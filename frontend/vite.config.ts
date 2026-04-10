import { fileURLToPath, URL } from 'node:url'

import { defineConfig, type Plugin } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import vuetify from 'vite-plugin-vuetify'

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
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  worker: {
    format: 'es',
  },
  optimizeDeps: {
    include: ['monaco-editor/esm/vs/editor/editor.worker'],
  },
})
