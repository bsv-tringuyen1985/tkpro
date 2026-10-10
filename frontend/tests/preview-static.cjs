// Local QA only. Serves the actual out/ build with a separate mock API origin.
// No request is sent to production and no source/deployment config is changed.
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');
const out = path.resolve(__dirname, '../out');
const webPort = 14173;
const apiPort = 14174;
const webOrigin = `http://127.0.0.1:${webPort}`;
const requests = [];
const contacts = [];
let fail = false;
const category = { id: 1, name: 'Camera giám sát', slug: 'camera-giam-sat', description: 'Thiết bị kiểm thử', children: [] };
const products = Array.from({ length: 14 }, (_, i) => ({
  id: i + 1, name: `Camera kiểm thử ${i + 1}`, slug: `camera-${i + 1}`, category,
  sku: `QA-${i + 1}`, price: (i + 1) * 100000, thumbnail: '/storage/test.svg',
  short_description: 'Sản phẩm kiểm thử frontend tĩnh', description: '<p>Nội dung chi tiết camera.</p>',
  technical_specifications: [{ label: 'Độ phân giải', value: '2K QHD' }],
  featured: true, images: [{ url: '/storage/second.svg', alt: 'Ảnh thứ hai' }],
}));
const posts = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1, title: `Bài viết kiểm thử ${i + 1}`, slug: `bai-viet-${i + 1}`,
  excerpt: 'Tin tức kiểm thử', content: '<p>Nội dung bài viết từ CMS.</p>',
  category: { id: 1, name: 'Công nghệ', slug: 'cong-nghe' },
  featured_image: '/storage/test.svg', published_at: '2026-10-11T00:00:00Z',
}));
function json(res, data, status = 200, extra = {}) {
  res.writeHead(status, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': webOrigin, 'Cache-Control': 'no-store' });
  res.end(JSON.stringify({ success: status < 400, message: status < 400 ? 'OK' : 'Không tìm thấy dữ liệu', data, ...extra }));
}
function list(res, items, url) {
  const size = Math.max(1, Number(url.searchParams.get('per_page') || 12));
  const page = Math.max(1, Number(url.searchParams.get('page') || 1));
  json(res, items.slice((page - 1) * size, page * size), 200, { meta: { current_page: page, last_page: Math.ceil(items.length / size), total: items.length, per_page: size } });
}
const api = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  if (req.method === 'OPTIONS') {
    res.writeHead(204, { 'Access-Control-Allow-Origin': webOrigin, 'Access-Control-Allow-Methods': 'GET,POST,OPTIONS', 'Access-Control-Allow-Headers': 'Content-Type,Accept' }); return res.end();
  }
  if (url.pathname === '/__fixture/state') return json(res, { requests, contacts });
  if (url.pathname === '/__fixture/fail') { fail = url.searchParams.get('value') === '1'; return json(res, { fail }); }
  if (url.pathname === '/__fixture/add-product') {
    products.push({ ...products[0], id: 99, slug: 'san-pham-moi-sau-build', name: 'Sản phẩm mới sau build' });
    return json(res, { added: true });
  }
  requests.push({ method: req.method, path: req.url });
  if (fail) return json(res, null, 503);
  if (url.pathname.startsWith('/storage/')) {
    res.writeHead(200, { 'Content-Type': 'image/svg+xml', 'Access-Control-Allow-Origin': webOrigin });
    return res.end('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#e1f5e7"/><text x="50" y="150" fill="#087a3b">Camera QA</text></svg>');
  }
  const endpoint = url.pathname.replace('/api/v1', '');
  if (endpoint === '/settings') return json(res, { site_name: 'TKPRO QA', hotline: '0900000000', email: 'qa@example.test' });
  if (endpoint.startsWith('/menus/')) return json(res, { items: [] });
  if (endpoint === '/banners') return json(res, []);
  if (endpoint === '/categories') return json(res, [category]);
  if (endpoint === '/categories/camera-giam-sat') return json(res, { category, products });
  if (endpoint === '/post-categories') return json(res, [posts[0].category]);
  if (endpoint === '/products') {
    let items = products.filter(p => !url.searchParams.get('q') || p.name.includes(url.searchParams.get('q')));
    if (url.searchParams.get('sort') === 'price_desc') items = items.slice().sort((a, b) => b.price - a.price);
    return list(res, items, url);
  }
  if (endpoint.startsWith('/products/')) {
    const product = products.find(p => p.slug === decodeURIComponent(endpoint.split('/').at(-1)));
    return product ? json(res, { product, related: products.slice(1, 3) }) : json(res, null, 404);
  }
  if (endpoint === '/posts') return list(res, posts, url);
  if (endpoint.startsWith('/posts/')) {
    const post = posts.find(p => p.slug === endpoint.split('/').at(-1));
    return post ? json(res, { post, related: posts.slice(1, 3) }) : json(res, null, 404);
  }
  if (endpoint === '/search') return json(res, { products: products.slice(0, 2), posts: posts.slice(0, 1) });
  if (endpoint.startsWith('/pages/')) return json(res, { title: endpoint.endsWith('gioi-thieu') ? 'Giới thiệu TKPRO' : 'Dịch vụ TKPRO', content: '<p>Nội dung trang từ CMS.</p>' });
  if (endpoint === '/contact-messages' && req.method === 'POST') {
    let body = ''; for await (const chunk of req) body += chunk;
    const data = JSON.parse(body);
    if (!data.name || !data.phone) return json(res, null, 422, { message: 'Vui lòng nhập tên và số điện thoại.', errors: { name: ['Vui lòng nhập họ tên.'], phone: ['Vui lòng nhập số điện thoại.'] } });
    contacts.push(data); return json(res, { id: contacts.length }, 201);
  }
  return json(res, null, 404);
});
const mime = { '.html': 'text/html; charset=utf-8', '.js': 'application/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.txt': 'text/plain' };
const web = http.createServer((req, res) => {
  const url = new URL(req.url, webOrigin);
  if (url.pathname === '/config.js') {
    res.writeHead(200, { 'Content-Type': 'application/javascript', 'Cache-Control': 'no-store' });
    return res.end(`window.TKPRO_CONFIG=${JSON.stringify({ apiUrl: `http://127.0.0.1:${apiPort}/api/v1`, siteUrl: webOrigin })};`);
  }
  let file = path.resolve(out, '.' + decodeURIComponent(url.pathname));
  if (!file.startsWith(out + path.sep) && file !== out) { res.writeHead(403); return res.end(); }
  if (!fs.existsSync(file) || fs.statSync(file).isDirectory()) {
    if (path.extname(url.pathname)) { res.writeHead(404); return res.end('Not found'); }
    file = path.join(out, 'index.html');
  }
  res.writeHead(200, { 'Content-Type': mime[path.extname(file)] || 'application/octet-stream', 'Cache-Control': 'no-store' });
  fs.createReadStream(file).pipe(res);
});
api.listen(apiPort, '127.0.0.1', () => console.log(`Fixture API: http://127.0.0.1:${apiPort}`));
web.listen(webPort, '127.0.0.1', () => console.log(`Static QA: ${webOrigin}`));
