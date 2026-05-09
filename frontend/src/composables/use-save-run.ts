import { ref, onUnmounted } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { saveRun as saveRunApi } from '@/api/render-runs-api'
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

  async function saveRun(): Promise<void> {
    if (isSaving.value) return
    isSaving.value = true
    try {
      const calls: Promise<RenderRun>[] = []
      if (sandbox.metricsA) {
        calls.push(saveRunApi({
          engineId: sandbox.slotA.engineId,
          iterations: sandbox.iterations,
          avgMs: sandbox.metricsA.avgMs,
          minMs: sandbox.metricsA.minMs,
          maxMs: sandbox.metricsA.maxMs,
          p95Ms: sandbox.metricsA.p95Ms,
          outputBytes: sandbox.metricsA.outputBytes,
        }))
      }
      if (sandbox.metricsB) {
        calls.push(saveRunApi({
          engineId: sandbox.slotB.engineId,
          iterations: sandbox.iterations,
          avgMs: sandbox.metricsB.avgMs,
          minMs: sandbox.metricsB.minMs,
          maxMs: sandbox.metricsB.maxMs,
          p95Ms: sandbox.metricsB.p95Ms,
          outputBytes: sandbox.metricsB.outputBytes,
        }))
      }
      await Promise.all(calls)
      showFeedback(`${calls.length} run${calls.length !== 1 ? 's' : ''} saved`)
    } catch {
      showFeedback('Save failed')
    } finally {
      isSaving.value = false
    }
  }

  return { isSaving, feedbackMsg, saveRun }
}
