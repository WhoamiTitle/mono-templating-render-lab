// Minimal browser-safe shim for Node's `path` module (used by pug internally).
// Only implements the subset that pug-code-gen requires.

export const sep = '/'

export function dirname(p: string): string {
  const parts = p.replace(/\\/g, '/').split('/')
  parts.pop()
  return parts.join('/') || '/'
}

export function basename(p: string, ext?: string): string {
  const base = p.replace(/\\/g, '/').split('/').pop() ?? ''
  return ext && base.endsWith(ext) ? base.slice(0, -ext.length) : base
}

export function extname(p: string): string {
  const i = p.lastIndexOf('.')
  return i > 0 ? p.slice(i) : ''
}

export function join(...parts: string[]): string {
  return parts.join('/').replace(/\/+/g, '/')
}

export function resolve(...parts: string[]): string {
  return join(...parts)
}

export function isAbsolute(p: string): boolean {
  return p.startsWith('/')
}

export default { sep, dirname, basename, extname, join, resolve, isAbsolute }
