export interface Slot {
  engineId: string
  code: string
}

export interface SandboxState {
  slotA: Slot
  slotB: Slot
  json: string
}

export interface BenchmarkResult {
  avgMs: number
  minMs: number
  maxMs: number
  p95Ms: number
  outputBytes: number
  output: string
}
