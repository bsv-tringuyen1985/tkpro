// Run against an isolated seeded API and a running production frontend.
// API_BASE=http://127.0.0.1:18000/api/v1 WEB_BASE=http://127.0.0.1:13000 node scripts/smoke-storefront.cjs
const assert = require('node:assert/strict');
const api = process.env.API_BASE || 'http://127.0.0.1:18000/api/v1';
const web = process.env.WEB_BASE || 'http://127.0.0.1:13000';
async function data(path) {
  const response = await fetch(api + path);
  assert.equal(response.status, 200, path);
  return (await response.json()).data;
}
async function main() {
  const [products, posts] = await Promise.all([data('/products'), data('/posts')]);
  const product = products[0];
  const post = posts[0];
  assert.ok(product?.category?.slug && post?.slug, 'Seeded product and post are required');
  const checks = [
    ['/', 'TKPRO'], ['/gioi-thieu', 'TKPRO'], ['/dich-vu', 'TKPRO'],
    ['/lien-he?san-pham=Camera', 'Camera'], ['/san-pham', product.name],
    [`/san-pham/${product.category.slug}`, product.category.name],
    [`/san-pham/${product.category.slug}/${product.slug}`, product.name],
    ['/tin-tuc', post.title], [`/tin-tuc/${post.slug}`, post.title],
    ['/tim-kiem?q=camera', 'TKPRO'], ['/sitemap.xml', product.slug], ['/robots.txt', 'Sitemap:'],
  ];
  for (const [path, content] of checks) {
    const response = await fetch(web + path, { headers: { 'User-Agent': 'Googlebot' } });
    assert.equal(response.status, 200, path);
    const body = await response.text();
    assert.ok(body.includes(content), `Expected page content at ${path}: ${content}`);
    assert.ok(!body.includes('NEXT_HTTP_ERROR_FALLBACK;500'), `Server rendering error at ${path}`);
    assert.ok(!/\\?"digest\\?":\\?"\d+/.test(body), `Streamed server error at ${path}`);
    console.log(`PASS ${path}`);
  }
  const detail = await data('/products/' + product.slug);
  const spec = detail.product.technical_specifications?.[0];
  if (spec?.value) {
    const html = await (await fetch(web + `/san-pham/${product.category.slug}/${product.slug}`)).text();
    assert.ok(html.includes(spec.value), 'Product specifications are rendered');
  }
  const missing = await fetch(web + '/san-pham/camera-giam-sat/does-not-exist', { headers: { 'User-Agent': 'Googlebot' } });
  // Next streams a 200 shell when loading.tsx has already flushed; its notFound()
  // response must still display the missing page and tell crawlers not to index it.
  assert.ok([200, 404].includes(missing.status));
  const missingBody = await missing.text();
  assert.ok(missingBody.includes('Không tìm thấy trang'));
  assert.match(missingBody, /name="robots" content="noindex"/);
  console.log(`PASS missing product: not-found UI and noindex\n${checks.length + 1} runtime checks passed.`);
}
main().catch(error => { console.error(error); process.exitCode = 1; });
