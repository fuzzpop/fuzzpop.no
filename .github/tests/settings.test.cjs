const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'index.js'), 'utf8');
const resetSource = source.match(/function resetSettings\(\) \{[\s\S]*?\n\}/)[0];

test('Reset restores the Releases view and artist filter without changing selected releases or unrelated queries', () => {
  for (const [current, expected] of [
    ['/releases?view=details&artist=Silentlab', '/releases'],
    ['/releases?view=table&artist=MC+Peko&source=test#top', '/releases?source=test#top'],
    ['/disco?a=sundays&view=details', '/disco?a=sundays&view=details'],
    ['/artists?source=test', '/artists?source=test']
  ]) {
    let cleared = false;
    let destination;
    vm.runInNewContext(`${resetSource}\nresetSettings();`, {
      URL,
      localStorage: { clear() { cleared = true; } },
      window: { location: {
        href: `http://localhost:8004${current}`,
        replace(url) { destination = url; }
      } }
    });
    assert.equal(cleared, true);
    assert.equal(destination, `http://localhost:8004${expected}`);
  }
});
