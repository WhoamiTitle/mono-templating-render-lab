import { ref, onUnmounted } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { useRenderRunsStore } from '@/stores/render-runs-store'
import { saveRun as saveRunApi, type SaveBenchmarkRunData } from '@/api/render-runs-api'
import type { RenderRun } from '@/types'

export function useSaveRun() {
  const isSaving = ref(false)
  const feedbackMsg = ref<string | null>(null)
  const sandbox = useSandboxStore()

  let timer: ReturnType<typeof setTimeout> | null = null

  function showFeedback(msg: string) {
    if (timer) clearTimeout(timer)
    feedbackMsg.value = msg
    timer = setTimeout(() => {
      feedbackMsg.value = null
    }, 2500)
  }

  onUnmounted(() => {
    if (timer) clearTimeout(timer)
  })

  function parseContext(): Record<string, unknown> {
    const parsed = JSON.parse(sandbox.json)
    if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
      throw new Error('Context must be an object')
    }

    return parsed as Record<string, unknown>
  }

  function runName(slot: 'A' | 'B'): string {
    return `Benchmark ${slot} ${new Date().toISOString()}`
  }

  async function saveRun(): Promise<void> {
    if (isSaving.value) return
    isSaving.value = true
    try {
      const context = parseContext()
      const calls: Promise<RenderRun>[] = []
      if (sandbox.metricsA) {
        const run: SaveBenchmarkRunData = {
          name: runName('A'),
          engineId: sandbox.slotA.engineId,
          code: sandbox.slotA.code,
          context,
          iterations: sandbox.iterations,
          outputBytes: sandbox.metricsA.outputBytes,
          samplesMs: sandbox.metricsA.samplesMs,
        }
        calls.push(saveRunApi(run))
      }
      if (sandbox.metricsB) {
        const run: SaveBenchmarkRunData = {
          name: runName('B'),
          engineId: sandbox.slotB.engineId,
          code: sandbox.slotB.code,
          context,
          iterations: sandbox.iterations,
          outputBytes: sandbox.metricsB.outputBytes,
          samplesMs: sandbox.metricsB.samplesMs,
        }
        calls.push(saveRunApi(run))
      }
      const saved = await Promise.all(calls)
      saved.forEach(run => useRenderRunsStore().addRun(run))
      showFeedback(`${calls.length} run${calls.length !== 1 ? 's' : ''} saved`)
    } catch {
      showFeedback('Save failed')
    } finally {
      isSaving.value = false
    }
  }

  return { isSaving, feedbackMsg, saveRun }
}
