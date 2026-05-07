/** Client-side template render dispatcher. Lazy-loads engine bundles and caches them. */

export type RenderResult = { output: string } | { error: string }

function toMessage(e: unknown): string {
  return e instanceof Error ? e.message : String(e)
}

/** Monaco language alias for each supported engine (matches engines-store syntaxAlias). */
export const ENGINE_MONACO_ALIAS: Record<string, string> = {
  handlebars: 'handlebars',
  pug: 'jade',
  ejs: 'html',
}

// Module-level cache stores in-flight Promises — concurrent first calls share the same import.
const engineCache = new Map<string, Promise<unknown>>()

function loadEngine(engineId: string): Promise<unknown> {
  if (engineCache.has(engineId)) return engineCache.get(engineId)!

  let p: Promise<unknown>
  switch (engineId) {
    case 'handlebars':
      p = import('handlebars').then(m => m.default)
      break
    case 'pug':
      p = import('pug')
      break
    case 'ejs':
      p = import('ejs')
      break
    default:
      return Promise.reject(new Error(`Unsupported engine: ${engineId}`))
  }

  engineCache.set(engineId, p)
  return p
}

/**
 * Render a template with the given engine and JSON context string.
 * Never throws — errors are returned as `{ error: string }`.
 */
export async function render(
  engineId: string,
  template: string,
  contextJson: string,
): Promise<RenderResult> {
  let context: Record<string, unknown>
  try {
    const parsed = JSON.parse(contextJson)
    if (typeof parsed !== 'object' || parsed === null || Array.isArray(parsed)) {
      return { error: 'Context must be a JSON object, not an array or primitive' }
    }
    context = parsed
  } catch (e) {
    return { error: `Invalid JSON context: ${toMessage(e)}` }
  }

  let engine: unknown
  try {
    engine = await loadEngine(engineId)
  } catch (e) {
    return { error: toMessage(e) }
  }

  try {
    let output: string

    if (engineId === 'handlebars') {
      const hbs = engine as typeof import('handlebars')
      output = hbs.compile(template)(context)
    } else if (engineId === 'pug') {
      const pugMod = engine as typeof import('pug')
      // `filename` suppresses the "filename required" error for inline templates
      output = pugMod.render(template, { ...context, filename: 'template' })
    } else if (engineId === 'ejs') {
      const ejsMod = engine as typeof import('ejs')
      output = ejsMod.render(template, context)
    } else {
      return { error: `Unsupported engine: ${engineId}` }
    }

    return { output }
  } catch (e) {
    return { error: toMessage(e) }
  }
}
