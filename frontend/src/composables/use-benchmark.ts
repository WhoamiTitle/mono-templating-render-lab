import { ref } from 'vue'
import { render } from '@/services/render-service'
import type { BenchmarkResult } from '@/types'
import { useSandboxStore } from '@/stores/sandbox-store'

const WARMUP = 5
const YIELD_EVERY = 50

export function useBenchmark() {
  const isRunning = ref(false)
  const benchmarkError = ref<string | null>(null)
  const progress = ref(0)
  const sandbox = useSandboxStore()

  let abort: AbortController | null = null

  function cancel() {
    abort?.abort()
  }

  async function runSlot(
    slot: 'a' | 'b',
    signal: AbortSignal,
    progStart: number,
    progEnd: number,
  ): Promise<boolean> {
    const s = slot === 'a' ? sandbox.slotA : sandbox.slotB
    const n = Math.max(1, Math.min(10000, sandbox.iterations || 1))
    const total = WARMUP + n
    const times: number[] = []
    let lastOutput = ''

    for (let i = 0; i < total; i++) {
      if (signal.aborted) return false
      if (i > 0 && i % YIELD_EVERY === 0) {
        await new Promise<void>(r => setTimeout(r, 0))
        if (signal.aborted) return false
      }

      const t0 = performance.now()
      const result = await render(s.engineId, s.code, sandbox.json)
      const elapsed = performance.now() - t0

      if ('error' in result) {
        benchmarkError.value = `Slot ${slot.toUpperCase()} render error: ${result.error}`
        return false
      }

      if (i >= WARMUP) {
        times.push(elapsed)
        lastOutput = result.output
        const fraction = (i - WARMUP + 1) / n
        progress.value = progStart + fraction * (progEnd - progStart)
      }
    }

    times.sort((a, b) => a - b)
    const avg = times.reduce((a, b) => a + b, 0) / n
    const p95Idx = Math.min(Math.floor((n - 1) * 0.95), times.length - 1)

    if (signal.aborted) return false

    const metrics: BenchmarkResult = {
      avgMs: +avg.toFixed(3),
      minMs: +times[0]!.toFixed(3),
      maxMs: +times[times.length - 1]!.toFixed(3),
      p95Ms: +times[p95Idx]!.toFixed(3),
      outputBytes: new TextEncoder().encode(lastOutput).length,
      output: lastOutput,
    }

    if (slot === 'a') sandbox.metricsA = metrics
    else sandbox.metricsB = metrics
    return true
  }

  async function runBenchmark(): Promise<void> {
    if (isRunning.value) return
    isRunning.value = true
    benchmarkError.value = null
    progress.value = 0
    abort = new AbortController()
    const signal = abort.signal

    try {
      const ok = await runSlot('a', signal, 0, 0.5)
      if (ok && !signal.aborted) await runSlot('b', signal, 0.5, 1)
      if (!signal.aborted) progress.value = 1
    } finally {
      isRunning.value = false
      abort = null
    }
  }

  return { isRunning, benchmarkError, progress, runBenchmark, cancel }
}
