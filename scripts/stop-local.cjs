const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const root = path.resolve(__dirname, '..');
const file = path.join(root, '.runtime/services.json');
if (!fs.existsSync(file)) { console.log('No managed local session.'); process.exit(0); }
const state = JSON.parse(fs.readFileSync(file, 'utf8'));
if (state.root !== root) throw new Error('Unexpected workspace in process state.');
for (const service of state.services) {
  if (process.platform === 'win32') {
    // PIDs may be recycled: verify executable arguments belong to this checkout.
    const command = execFileSync('powershell.exe', ['-NoProfile', '-Command', `(Get-CimInstance Win32_Process -Filter 'ProcessId = ${Number(service.pid)}').CommandLine`], { encoding: 'utf8', windowsHide: true });
    if (!command.includes(root)) continue;
  }
  try { process.kill(service.pid); console.log(`Stopped ${service.name}.`); } catch {}
}
fs.unlinkSync(file);
