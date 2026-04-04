export interface RenderRun {
  id: string
  templateId?: string
  engineId: string
  iterations: number
  avgMs: number
  minMs: number
  maxMs: number
  p95Ms: number
  outputBytes: number
  createdAt: string
}
