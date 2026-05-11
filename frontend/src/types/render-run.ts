export interface RenderRun {
  id: string
  templateId?: string
  engineId: string
  status: 'in_progress' | 'success' | 'failure' | string
  iterations: number
  avgMs: number | null
  minMs: number | null
  maxMs: number | null
  p95Ms: number | null
  outputBytes: number | null
  createdAt: string
}
