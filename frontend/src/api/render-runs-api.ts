import type { RenderRun } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'
import { createTemplate } from './templates-api'

interface BackendBenchmarkRunView {
  benchmarkRunId: string
  templateId: string
  engineType: string
  iterationsN: number
  startedAt: string
  finishedAt: string | null
  status: string
  avgMs: number | null
  minMs: number | null
  maxMs: number | null
  p95Ms: number | null
  outputBytes: number | null
}

interface StartBenchmarkRunResponse {
  benchmarkRunId: string
}

export interface SaveBenchmarkRunData {
  name: string
  engineId: string
  code: string
  context: Record<string, unknown>
  iterations: number
  samplesMs: number[]
  outputBytes: number
}

function percentile95(samples: number[]): number {
  const idx = Math.min(Math.floor((samples.length - 1) * 0.95), samples.length - 1)
  return samples[idx] ?? 0
}

function normalizeSamples(samples: number[], iterations: number): number[] {
  const fallback = samples[0] ?? 0
  const normalized = samples
    .slice(0, iterations)
    .map(sample => Math.max(0, Number(sample.toFixed(3))))

  while (normalized.length < iterations) {
    normalized.push(Math.max(0, Number(fallback.toFixed(3))))
  }

  return normalized
}

function summary(samples: number[]) {
  const sorted = [...samples].sort((a, b) => a - b)
  const sum = sorted.reduce((acc, value) => acc + value, 0)
  const avg = sorted.length ? sum / sorted.length : 0
  return {
    samplesMs: sorted,
    avgMs: avg,
    minMs: sorted[0] ?? 0,
    maxMs: sorted[sorted.length - 1] ?? 0,
    p95Ms: percentile95(sorted),
  }
}

function fromBackend(view: BackendBenchmarkRunView): RenderRun {
  return {
    id: view.benchmarkRunId,
    templateId: view.templateId,
    engineId: view.engineType,
    status: view.status,
    iterations: view.iterationsN,
    avgMs: view.avgMs,
    minMs: view.minMs,
    maxMs: view.maxMs,
    p95Ms: view.p95Ms,
    outputBytes: view.outputBytes,
    createdAt: view.finishedAt ?? view.startedAt,
  }
}

export async function getRuns(): Promise<RenderRun[]> {
  const res = await http.get<{ items: BackendBenchmarkRunView[] }>(ENDPOINTS.benchmarkRuns.list)
  return res.items.map(fromBackend)
}

export async function saveRun(data: SaveBenchmarkRunData): Promise<RenderRun> {
  const iterations = Math.max(1, Math.min(10000, data.iterations || 1))
  const samples = summary(normalizeSamples(data.samplesMs, iterations))
  const template = await createTemplate({
    name: data.name,
    engineId: data.engineId,
    code: data.code,
    isPublic: false,
  })
  const started = await http.post<StartBenchmarkRunResponse>(ENDPOINTS.benchmarkRuns.create, {
    templateId: template.id,
    context: data.context,
    iterationsN: iterations,
  })
  await http.post(ENDPOINTS.benchmarkRuns.success(started.benchmarkRunId), {
    samplesMs: samples.samplesMs,
    avgMs: samples.avgMs,
    minMs: samples.minMs,
    maxMs: samples.maxMs,
    p95Ms: samples.p95Ms,
    outputBytes: data.outputBytes,
  })

  return {
    id: started.benchmarkRunId,
    templateId: template.id,
    engineId: data.engineId,
    status: 'success',
    iterations,
    avgMs: samples.avgMs,
    minMs: samples.minMs,
    maxMs: samples.maxMs,
    p95Ms: samples.p95Ms,
    outputBytes: data.outputBytes,
    createdAt: new Date().toISOString(),
  }
}
