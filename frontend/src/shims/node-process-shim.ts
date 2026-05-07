// Minimal `process` polyfill for browser-bundled packages that reference Node.js globals.
// Must be imported before any lazily-loaded template engine (e.g. pug).
if (typeof (globalThis as Record<string, unknown>)['process'] === 'undefined') {
  (globalThis as Record<string, unknown>)['process'] = {
    env: {},
    version: 'v18.0.0',
    versions: { node: '18.0.0' },
    browser: true,
    platform: 'browser',
  }
}
