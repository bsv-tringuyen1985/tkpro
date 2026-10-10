const fs = require('node:fs');
const path = require('node:path');
const Module = require('node:module');
const test = require('node:test');
const assert = require('node:assert/strict');
const ts = require('typescript');
const root = path.resolve(__dirname, '..');
const originalResolve = Module._resolveFilename;
Module._resolveFilename = function (name, ...args) {
  return originalResolve.call(this, name.startsWith('@/') ? path.join(root, name.slice(2)) : name, ...args);
};
require.extensions['.ts'] = (mod, filename) => mod._compile(ts.transpileModule(fs.readFileSync(filename, 'utf8'), {
  compilerOptions: { module: ts.ModuleKind.CommonJS, target: ts.ScriptTarget.ES2022 },
}).outputText, filename);

const { matchRoute, readSearchParams } = require('../static/routes.ts');
const { getPublicApiUrl, getPublicSiteUrl } = require('../lib/runtime-config.ts');
const { absoluteImageUrl } = require('../utils/format.ts');

test('static routes accept new CMS slugs without a build-time manifest', () => {
  assert.deepEqual(matchRoute('/san-pham/danh-muc-moi/san-pham-moi/'), {
    name: 'product', params: { category: 'danh-muc-moi', slug: 'san-pham-moi' },
  });
  assert.equal(matchRoute('/tin-tuc/bai-moi').name, 'post');
  assert.equal(matchRoute('/san-pham/camera').name, 'category');
  for (const invalid of ['/unknown', '/tin-tuc/a/b', '/san-pham/a/b/c', '/san-pham/%FF', '/san-pham/a%2Fb', '/constructor']) {
    assert.equal(matchRoute(invalid).name, 'missing', invalid);
  }
});

test('filter, pagination and contact queries preserve unicode and repeated values', () => {
  const query = readSearchParams('?q=camera+wifi&page=2&san-pham=Camera%20ngo%C3%A0i%20tr%E1%BB%9Di&category=a&category=b');
  assert.equal(query.q, 'camera wifi');
  assert.equal(query.page, '2');
  assert.equal(query['san-pham'], 'Camera ngoài trời');
  assert.deepEqual(query.category, ['a', 'b']);
});

test('runtime config overrides build-time and private API URLs for GET, POST and media', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'https://old.example/api/v1';
  process.env.API_INTERNAL_URL = 'http://private-api:8000/api/v1';
  global.window = { location: { origin: 'https://shop.example' }, TKPRO_CONFIG: {
    apiUrl: 'https://api.example/api/v1/', siteUrl: 'https://shop.example',
  }, dispatchEvent() {} };
  const requests = [];
  const oldFetch = global.fetch;
  global.fetch = async (url, options) => {
    requests.push({ url, options });
    return { ok: true, status: 200, json: async () => ({ success: true, data: [], meta: { current_page: 2 } }) };
  };
  try {
    const { apiFetch, apiFetchWithMeta, apiPost } = require('../lib/api.ts');
    await apiFetch('/products', { q: 'camera wifi' });
    await apiFetchWithMeta('/products', { page: 2 });
    await apiPost('/contact-messages', { name: 'Test' });
    assert.ok(requests.every(r => r.url.startsWith('https://api.example/api/v1/')));
    assert.equal(requests[0].options.cache, 'no-store');
    assert.equal(requests[2].options.method, 'POST');
    assert.equal(absoluteImageUrl('/storage/photo.jpg'), 'https://api.example/storage/photo.jpg');
    window.TKPRO_CONFIG.apiUrl = '/api/v1';
    assert.equal(getPublicApiUrl(), 'https://shop.example/api/v1');
    assert.equal(getPublicSiteUrl(), 'https://shop.example');
    window.TKPRO_CONFIG.apiUrl = 'javascript:alert(1)';
    assert.throws(getPublicApiUrl, /HTTP/);
  } finally { delete global.window; global.fetch = oldFetch; }
});

test('invalid API envelopes are rejected, including responses from a wrong document root', async () => {
  const oldFetch = global.fetch;
  global.fetch = async () => ({ ok: true, status: 200, json: async () => ({ html: 'wrong site' }) });
  try {
    const { apiFetchWithMeta, ApiError } = require('../lib/api.ts');
    await assert.rejects(apiFetchWithMeta('/products'), ApiError);
  } finally { global.fetch = oldFetch; }
});
