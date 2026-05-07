// Minimal browser shim for Node's `assert` module (used by pug-code-gen).

function assert(condition: unknown, message?: string | Error): asserts condition {
  if (!condition) {
    throw message instanceof Error ? message : new Error(message ?? 'Assertion failed')
  }
}

assert.ok = assert

assert.equal = (actual: unknown, expected: unknown, message?: string) => {
   
  if (actual != expected) throw new Error(message ?? `${actual} == ${expected} failed`)
}

assert.strictEqual = (actual: unknown, expected: unknown, message?: string) => {
  if (actual !== expected) throw new Error(message ?? `${actual} === ${expected} failed`)
}

assert.notStrictEqual = (actual: unknown, expected: unknown, message?: string) => {
  if (actual === expected) throw new Error(message ?? `${actual} !== ${expected} failed`)
}

assert.deepEqual = assert.equal
assert.deepStrictEqual = assert.strictEqual

assert.throws = (fn: () => void, _expected?: unknown, message?: string) => {
  try { fn() } catch { return }
  throw new Error(message ?? 'Expected function to throw')
}

assert.doesNotThrow = (fn: () => void, _expected?: unknown, message?: string) => {
  try { fn() } catch (e) { throw new Error(message ?? `Got unwanted exception: ${e}`) }
}

assert.fail = (message?: string) => { throw new Error(message ?? 'assert.fail()') }

export default assert
export { assert }
