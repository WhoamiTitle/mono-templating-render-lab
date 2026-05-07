// Stub for Node's `fs` module — pug imports it at module load time even for inline templates.
// None of these should be called at runtime when rendering inline (non-file) templates.

function notSupported(name: string): never {
  throw new Error(`fs.${name} is not available in browser`)
}

export const readFileSync = (_path: unknown) => notSupported('readFileSync')
export const existsSync = (_path: unknown): boolean => notSupported('existsSync')
export const writeFileSync = (_path: unknown, _data: unknown) => notSupported('writeFileSync')
export const readFile = (_path: unknown, _cb: unknown) => notSupported('readFile')
export const writeFile = (_path: unknown, _data: unknown, _cb: unknown) => notSupported('writeFile')
export const stat = (_path: unknown, _cb: unknown) => notSupported('stat')
export const statSync = (_path: unknown) => notSupported('statSync')

export default {
  readFileSync,
  existsSync,
  writeFileSync,
  readFile,
  writeFile,
  stat,
  statSync,
}
