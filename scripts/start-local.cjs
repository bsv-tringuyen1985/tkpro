const fs = require('node:fs');
const path = require('node:path');
const net = require('node:net');
const crypto = require('node:crypto');
const { spawn } = require('node:child_process');

const root = path.resolve(__dirname, '..');
const runtime = path.join(root, '.runtime');
const php = process.env.PHP_BINARY || 'php';
const urls = {
  website: 'http://127.0.0.1:13000',
  api: 'http://127.0.0.1:18000/api/v1',
  cms: 'http://127.0.0.1:18001/admin',
};
const started = [];

async function freePort(port) {
  await new Promise((resolve, reject) => {
    const server = net.createServer();
    server.once('error', () => reject(new Error(`Port ${port} is in use.`)));
    server.listen(port, '127.0.0.1', () => server.close(resolve));
  });
}

function run(command, args, cwd, env, logName) {
  const log = fs.openSync(path.join(runtime, logName), 'a');
  const child = spawn(command, args, { cwd, env, windowsHide: true, stdio: ['ignore', log, log] });
  fs.closeSync(log);
  return new Promise((resolve, reject) => {
    child.once('error', reject);
    child.once('exit', code => code === 0 ? resolve() : reject(new Error(`${logName}: command failed (${code}). See .runtime/${logName}.`)));
  });
}

async function serve(name, command, args, cwd, env) {
  const log = fs.openSync(path.join(runtime, `${name}.log`), 'a');
  const child = spawn(command, args, { cwd, env, windowsHide: true, detached: true, stdio: ['ignore', log, log] });
  fs.closeSync(log);
  await new Promise((resolve, reject) => { child.once('spawn', resolve); child.once('error', reject); });
  started.push({ name, pid: child.pid });
  child.unref();
}

async function ready(url) {
  for (let i = 0; i < 60; i++) {
    try {
      const response = await fetch(url, { signal: AbortSignal.timeout(2000) });
      if (response.ok) return;
    } catch {}
    await new Promise(resolve => setTimeout(resolve, 500));
  }
  throw new Error(`Service is not ready: ${url}. See .runtime logs.`);
}

async function main() {
  const stateFile = path.join(runtime, 'services.json');
  if (fs.existsSync(stateFile)) {
    const state = JSON.parse(fs.readFileSync(stateFile, 'utf8'));
    const alive = state.services.every(service => {
      try { process.kill(service.pid, 0); return true; } catch { return false; }
    });
    if (alive) {
      await ready(urls.website);
      console.log('TKPRO is already running.');
      console.log(JSON.stringify(urls, null, 2));
      return;
    }
    throw new Error('A previous local session is incomplete. Run node scripts/stop-local.cjs before restarting.');
  }
  for (const port of [13000, 18000, 18001]) await freePort(port);
  fs.mkdirSync(runtime, { recursive: true });
  let key = '';
  const envPath = path.join(root, '.env');
  if (fs.existsSync(envPath)) {
    const match = fs.readFileSync(envPath, 'utf8').match(/^APP_KEY=(.*)$/m);
    key = match?.[1].trim().replace(/^["']|["']$/g, '') || '';
  }
  if (!key) {
    const keyFile = path.join(runtime, 'app-key');
    key = fs.existsSync(keyFile) ? fs.readFileSync(keyFile, 'utf8').trim() : `base64:${crypto.randomBytes(32).toString('base64')}`;
    fs.writeFileSync(keyFile, key, { mode: 0o600 });
  }
  const database = path.join(runtime, 'local.sqlite');
  if (!fs.existsSync(database)) fs.closeSync(fs.openSync(database, 'wx'));
  const storage = path.join(root, 'api/storage/app/public');
  fs.mkdirSync(storage, { recursive: true });
  for (const app of ['api', 'cms']) {
    const link = path.join(root, app, 'public/storage');
    try { fs.lstatSync(link); } catch (error) {
      if (error.code !== 'ENOENT') throw error;
      fs.symlinkSync(storage, link, process.platform === 'win32' ? 'junction' : 'dir');
    }
  }
  const env = {
    ...process.env,
    APP_ENV: 'local', APP_DEBUG: 'true', APP_KEY: key,
    DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '',
    CACHE_STORE: 'file', SESSION_DRIVER: 'file', QUEUE_CONNECTION: 'sync',
    SESSION_DOMAIN: '', SESSION_SECURE_COOKIE: 'false',
    FRONTEND_URL: urls.website, PUBLIC_STORAGE_PATH: storage,
    PUBLIC_STORAGE_URL: 'http://127.0.0.1:18000/storage',
    SANCTUM_STATEFUL_DOMAINS: '127.0.0.1:13000',
  };
  console.log('Preparing local database...');
  await run(php, ['-d', 'extension=pdo_sqlite', path.join(__dirname, 'prepare-local.php')], path.join(root, 'api'), { ...env, APP_URL: 'http://127.0.0.1:18000' }, 'setup.log');
  await run(php, ['-d', 'extension=pdo_sqlite', 'artisan', 'config:clear'], path.join(root, 'cms'), env, 'setup.log');
  for (const [name, port] of [['api', 18000], ['cms', 18001]]) {
    const router = path.join(root, name, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');
    await serve(name, php, ['-d', 'extension=pdo_sqlite', '-S', `127.0.0.1:${port}`, router], path.join(root, name, 'public'), {
      ...env, APP_NAME: `TKPRO ${name.toUpperCase()}`, APP_URL: `http://127.0.0.1:${port}`,
      SESSION_COOKIE: 'tkpro_local_cms_session',
    });
    await ready(`http://127.0.0.1:${port}/up`);
  }
  const frontendEnv = {
    ...process.env, NODE_ENV: 'production', API_INTERNAL_URL: urls.api,
    NEXT_PUBLIC_API_URL: urls.api, NEXT_PUBLIC_SITE_URL: urls.website,
  };
  const frontend = path.join(root, 'frontend');
  const next = path.join(frontend, 'node_modules/next/dist/bin/next');
  console.log('Building frontend for the local URLs...');
  await run(process.execPath, [next, 'build', '--webpack'], frontend, frontendEnv, 'build.log');
  await serve('frontend', process.execPath, [next, 'start', '-H', '127.0.0.1', '-p', '13000'], frontend, frontendEnv);
  await ready(urls.website);
  fs.writeFileSync(stateFile, JSON.stringify({ root, urls, services: started }, null, 2));
  console.log('TKPRO started.');
  console.log(JSON.stringify(urls, null, 2));
}

main().catch(error => {
  for (const service of started) { try { process.kill(service.pid); } catch {} }
  console.error(error.message);
  process.exitCode = 1;
});
