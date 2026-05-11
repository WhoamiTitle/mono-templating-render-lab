// Browser shim for Node's `assert` module — used by pug-code-gen.
// CJS format so pug's require('assert') gets a callable function, not a namespace object.

function assert(v, msg) {
  if (!v) throw msg instanceof Error ? msg : new Error(msg != null ? msg : 'Assertion failed')
}

assert.ok = assert

assert.equal = function equal(a, b, msg) {
  if (a != b) throw new Error(msg != null ? msg : a + ' == ' + b + ' failed')
}

assert.strictEqual = function strictEqual(a, b, msg) {
  if (a !== b) throw new Error(msg != null ? msg : a + ' === ' + b + ' failed')
}

assert.notStrictEqual = function notStrictEqual(a, b, msg) {
  if (a === b) throw new Error(msg != null ? msg : a + ' !== ' + b + ' failed')
}

assert.deepEqual = assert.equal
assert.deepStrictEqual = assert.strictEqual

assert.throws = function throws(fn, _expected, msg) {
  try { fn() } catch (e) {
    return e;
  }
  throw new Error(msg != null ? msg : 'Expected function to throw')
}

assert.doesNotThrow = function doesNotThrow(fn, _expected, msg) {
  try { fn() } catch (e) { throw new Error(msg != null ? msg : 'Got unwanted exception: ' + e) }
}

assert.fail = function fail(msg) {
  throw new Error(msg != null ? msg : 'assert.fail()')
}

module.exports = assert
