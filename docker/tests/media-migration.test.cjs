const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const script = path.resolve(__dirname, '../merge-media.php');

function fixture(t) {
  const root = fs.mkdtempSync(path.join(os.tmpdir(), 'tkpro-media-test-'));
  const source = path.join(root, 'legacy');
  const destination = path.join(root, 'shared');
  fs.mkdirSync(source);
  fs.mkdirSync(destination);
  t.after(() => {
    if (path.dirname(root) !== path.resolve(os.tmpdir()) || !path.basename(root).startsWith('tkpro-media-test-')) {
      throw new Error('Unexpected test cleanup path');
    }
    fs.rmSync(root, { recursive: true, force: true });
  });
  return { source, destination, run: () => spawnSync(process.env.PHP_BINARY || 'php', [script, source, destination], { encoding: 'utf8' }) };
}

test('imports nested legacy files once, preserving originals and later deletions', t => {
  const f = fixture(t);
  fs.mkdirSync(path.join(f.source, 'products'));
  fs.writeFileSync(path.join(f.source, 'products/photo.jpg'), 'legacy photo');
  assert.equal(f.run().status, 0);
  assert.equal(fs.readFileSync(path.join(f.destination, 'products/photo.jpg'), 'utf8'), 'legacy photo');
  assert.equal(fs.readFileSync(path.join(f.source, 'products/photo.jpg'), 'utf8'), 'legacy photo');
  fs.unlinkSync(path.join(f.destination, 'products/photo.jpg'));
  assert.equal(f.run().status, 0);
  assert.equal(fs.existsSync(path.join(f.destination, 'products/photo.jpg')), false);
});

test('different files with the same path stop the migration without overwriting', t => {
  const f = fixture(t);
  fs.writeFileSync(path.join(f.source, 'photo.jpg'), 'legacy photo');
  fs.writeFileSync(path.join(f.destination, 'photo.jpg'), 'current photo');
  assert.notEqual(f.run().status, 0);
  assert.equal(fs.readFileSync(path.join(f.destination, 'photo.jpg'), 'utf8'), 'current photo');
  assert.equal(fs.existsSync(path.join(f.destination, '.legacy-cms-media-imported')), false);
});

test('identical files do not prevent completing the migration', t => {
  const f = fixture(t);
  for (const dir of [f.source, f.destination]) fs.writeFileSync(path.join(dir, 'photo.jpg'), 'same photo');
  assert.equal(f.run().status, 0);
  assert.equal(fs.existsSync(path.join(f.destination, '.legacy-cms-media-imported')), true);
});
