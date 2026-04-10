import { fileURLToPath, URL } from 'node:url'
import { defineConfig, mergeConfig } from 'vite'
import baseConfig from './vite.config'

export default mergeConfig(baseConfig, defineConfig({
  resolve: {
    alias: [
      {
        find: '@/api/auth-api',
        replacement: fileURLToPath(new URL('./src/api/mocks/auth-api.ts', import.meta.url)),
      },
      {
        find: '@/api/templates-api',
        replacement: fileURLToPath(new URL('./src/api/mocks/templates-api.ts', import.meta.url)),
      },
      {
        find: '@/api/render-runs-api',
        replacement: fileURLToPath(new URL('./src/api/mocks/render-runs-api.ts', import.meta.url)),
      },
      {
        find: '@/api/state-api',
        replacement: fileURLToPath(new URL('./src/api/mocks/state-api.ts', import.meta.url)),
      },
    ],
  },
}))
