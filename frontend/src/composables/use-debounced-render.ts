import { ref, watch, onUnmounted } from 'vue'
import { render } from '@/services/render-service'
import { useSandboxStore } from '@/stores/sandbox-store'

export function useDebouncedRender() {
  const sandbox = useSandboxStore()
  const previewHtml = ref<string | null>(null)
  const previewError = ref<string | null>(null)

  let debounceTimer: ReturnType<typeof setTimeout> | null = null
  // Monotonic counter — stale async results are dropped when a newer request completes first.
  let requestId = 0

  async function triggerRender() {
    const tab = sandbox.activeTab
    if (tab === 'json') {
      // Cancel any in-flight result so it doesn't overwrite preview after tab switch
      requestId++
      return
    }

    const id = ++requestId
    const engineId = tab === 'a' ? sandbox.slotA.engineId : sandbox.slotB.engineId
    const code = tab === 'a' ? sandbox.slotA.code : sandbox.slotB.code

    const result = await render(engineId, code, sandbox.json)

    if (id !== requestId) return // superseded by a newer call

    if ('output' in result) {
      previewHtml.value = result.output
      previewError.value = null
    } else {
      previewError.value = result.error
      // intentionally retain last good html so preview is not blank on transient errors
    }
  }

  function scheduleRender() {
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(triggerRender, 150)
  }

  watch(
    () => [
      sandbox.activeTab,
      sandbox.slotA.engineId,
      sandbox.slotA.code,
      sandbox.slotB.engineId,
      sandbox.slotB.code,
      sandbox.json,
    ],
    scheduleRender,
    { immediate: true },
  )

  onUnmounted(() => {
    if (debounceTimer) clearTimeout(debounceTimer)
  })

  return { previewHtml, previewError }
}
