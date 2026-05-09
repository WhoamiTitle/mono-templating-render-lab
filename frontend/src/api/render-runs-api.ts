import type { RenderRun } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

// Backend RenderRunView maps to frontend RenderRun (best-effort — models differ)
interface BackendRenderRunView {
  runId: string
  templateId: string
  engineType: string
  startedAt: string
  finishedAt: string | null
  status: string
  durationMs: number | null
  outputText: string | null
}

function fromBackend(view: BackendRenderRunView): RenderRun {
  const duration = view.durationMs ?? 0
  return {
    id: view.runId,
    templateId: view.templateId,
    engineId: view.engineType,
    iterations: 1,
    avgMs: duration,
    minMs: duration,
    maxMs: duration,
    p95Ms: duration,
    outputBytes: view.outputText
      ? new TextEncoder().encode(view.outputText).length
      : 0,
    createdAt: view.finishedAt ?? view.startedAt,
  }
}

export async function getRuns(): Promise<RenderRun[]> {
  const res = await http.get<{ items: BackendRenderRunView[] }>(ENDPOINTS.renderRuns.list)
  return res.items.map(fromBackend)
}

export async function saveRun(_data: Omit<RenderRun, 'id' | 'createdAt'>): Promise<RenderRun> {
  // Backend requires templateId + lifecycle (start→complete); not available in sandbox flow
  throw new Error('saveRun requires a templateId — save the template first')
}
