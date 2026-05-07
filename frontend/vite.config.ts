import { fileURLToPath, URL } from 'node:url'

import { defineConfig, type Plugin } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

// CJS-compatible assert shim for dep pre-bundling.
// pug-code-gen calls `assert(cond, msg)` directly — the ESM default-export shim
// produces a namespace object after esbuild's __toESM interop, so it is not
// callable. This plugin returns a proper CommonJS module instead.
const ASSERT_CJS_IMPL = `
var assert = function(v, m) {
  if (!v) throw (m instanceof Error ? m : new Error(m != null ? String(m) : 'Assertion failed'));
};
assert.ok = assert;
assert.equal = function(a, b, m) {
  if (a != b) throw new Error(m != null ? String(m) : a + ' == ' + b + ' failed');
};
assert.strictEqual = function(a, b, m) {
  if (a !== b) throw new Error(m != null ? String(m) : a + ' === ' + b + ' failed');
};
assert.notStrictEqual = function(a, b, m) {
  if (a === b) throw new Error(m != null ? String(m) : a + ' !== ' + b + ' failed');
};
assert.deepEqual = assert.equal;
assert.deepStrictEqual = assert.strictEqual;
assert.throws = function(fn, _, m) {
  try { fn(); } catch (e) { return; }
  throw new Error(m != null ? String(m) : 'Expected function to throw');
};
assert.doesNotThrow = function(fn, _, m) {
  try { fn(); } catch (e) { throw new Error(m != null ? String(m) : 'Got unwanted exception: ' + e); }
};
assert.fail = function(m) { throw new Error(m != null ? String(m) : 'assert.fail()'); };
module.exports = assert;
`

const assertCjsShim = {
  name: 'assert-cjs-browser-shim',
  setup(build: { onResolve: Function; onLoad: Function }) {
    build.onResolve({ filter: /^assert$/ }, () => ({
      path: 'assert',
      namespace: 'assert-cjs-shim',
    }))
    build.onLoad({ filter: /.*/, namespace: 'assert-cjs-shim' }, () => ({
      loader: 'js',
      contents: ASSERT_CJS_IMPL,
    }))
  },
}

function suppressMonacoChunkWarning(): Plugin {
  let onlyMonacoIsLarge = false
  const LIMIT_BYTES = 500 * 1024
  const MONACO_RE = /monaco|editor\.(api|main|worker)/i

  return {
    name: 'suppress-monaco-chunk-size-warning',
    apply: 'build',

    configResolved(config) {
      const original = config.logger.warn.bind(config.logger)
      config.logger.warn = (msg, options) => {
        if (onlyMonacoIsLarge && typeof msg === 'string' && msg.includes('chunks are larger')) return
        original(msg, options)
      }
    },

    generateBundle(_, bundle) {
      const largeNonMonaco = Object.values(bundle).filter((entry) => {
        if (entry.type !== 'chunk') return false
        return Buffer.byteLength(entry.code, 'utf8') > LIMIT_BYTES && !MONACO_RE.test(entry.fileName)
      })
      onlyMonacoIsLarge = largeNonMonaco.length === 0
    },
  }
}

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    vuetify({ autoImport: true }),
    suppressMonacoChunkWarning(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      // Pug imports these Node built-ins at module load time; redirect to browser-safe stubs.
      'path': fileURLToPath(new URL('./src/shims/node-path-shim.ts', import.meta.url)),
      'assert': fileURLToPath(new URL('./src/shims/node-assert-shim.ts', import.meta.url)),
      'fs': fileURLToPath(new URL('./src/shims/node-fs-shim.ts', import.meta.url)),
    },
  },
  worker: {
    format: 'es',
  },
  optimizeDeps: {
    include: ['monaco-editor/esm/vs/editor/editor.worker', 'ejs', 'pug'],
    esbuildOptions: {
      plugins: [assertCjsShim],
    },
  },
})
