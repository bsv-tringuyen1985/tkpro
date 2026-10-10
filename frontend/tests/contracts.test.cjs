const fs = require('node:fs');
const path = require('node:path');
const Module = require('node:module');
const test = require('node:test');
const assert = require('node:assert/strict');
const ts = require('typescript');
const root = path.resolve(__dirname, '..');
const resolve = Module._resolveFilename;
Module._resolveFilename = function (name, ...args) {
  return resolve.call(this, name.startsWith('@/') ? path.join(root, name.slice(2)) : name, ...args);
};
require.extensions['.ts'] = (mod, filename) => {
  mod._compile(ts.transpileModule(fs.readFileSync(filename, 'utf8'), {
    compilerOptions: { module: ts.ModuleKind.CommonJS, target: ts.ScriptTarget.ES2022, jsx: ts.JsxEmit.ReactJSX },
  }).outputText, filename);
};
require.extensions['.tsx'] = require.extensions['.ts'];
process.env.NEXT_PUBLIC_API_URL = 'https://tkpro.example/api/v1';
delete process.env.API_INTERNAL_URL;
const product = { name: 'Camera', slug: 'camera', category: { slug: 'cameras' } };
const post = { title: 'News', slug: 'news', content: '<p>Content</p>' };
global.fetch = async (url) => ({
  ok: true, status: 200,
  json: async () => ({ success: true, data:
    url.includes('/products/') ? { product, related: [{ slug: 'related-camera' }] } :
    url.includes('/posts/') ? { post, related: [{ slug: 'related-news' }] } :
    url.includes('/categories/') ? { category: { name: 'Cameras', slug: 'cameras' }, products: [] } :
    { site_name: 'TKPRO' },
  }),
});

test('product detail exposes product fields and related products', async () => {
  const result = await require('../services/productService.ts').getProduct('camera');
  assert.equal(result.name, product.name);
  assert.equal(result.category.slug, 'cameras');
  assert.equal(result.related[0].slug, 'related-camera');
});
test('category detail exposes its slug', async () => {
  const result = await require('../services/productService.ts').getCategory('cameras');
  assert.equal(result.slug, 'cameras');
});
test('product specifications render API rows and legacy CMS dictionaries', () => {
  const React = require('react');
  const { renderToStaticMarkup } = require('react-dom/server');
  const Component = require('../components/ProductSpecifications.tsx').default;
  for (const specs of [[{ label: 'Resolution', value: '2K QHD' }], { Resolution: '2K QHD' }]) {
    const html = renderToStaticMarkup(React.createElement(Component, { specs }));
    assert.ok(html.includes('<th scope="row">Resolution</th>'));
    assert.ok(html.includes('<td>2K QHD</td>'));
  }
});
test('post detail exposes content and related posts', async () => {
  const result = await require('../services/postService.ts').getPost('news');
  assert.equal(result.content, post.content);
  assert.equal(result.related[0].slug, 'related-news');
});
test('page menu uses the resolved API URL', () => {
  const { resolveMenuItemUrl } = require('../services/menuService.ts');
  assert.equal(resolveMenuItemUrl({ type: 'page', url: '/gioi-thieu' }), '/gioi-thieu');
  assert.equal(resolveMenuItemUrl({ type: 'page', page_slug: 'lien-he' }), '/lien-he');
});
test('metadata includes nested SEO, canonical, robots and absolute Open Graph image', async () => {
  const metadata = await require('../lib/metadata.ts').buildMetadata({
    title: 'Content title', seo: {
      meta_title: 'SEO title', meta_description: 'SEO description', meta_keywords: 'camera, security',
      og_title: 'Social title', og_image: '/storage/og.jpg',
      canonical_url: 'https://tkpro.example/custom', robots: 'noindex,follow',
    },
  }, { canonical: '/fallback' });
  assert.equal(metadata.title, 'SEO title');
  assert.equal(metadata.description, 'SEO description');
  assert.deepEqual(metadata.keywords, ['camera', 'security']);
  assert.equal(metadata.openGraph.title, 'Social title');
  assert.equal(metadata.openGraph.images[0].url, 'https://tkpro.example/storage/og.jpg');
  assert.equal(metadata.alternates.canonical, 'https://tkpro.example/custom');
  assert.equal(metadata.robots, 'noindex,follow');
});
