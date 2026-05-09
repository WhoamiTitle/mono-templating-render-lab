import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth-store'
import { useSandboxStore } from '@/stores/sandbox-store'
import { saveState } from '@/api/state-api'

export function useSandboxShare() {
  const isSaving = ref(false)
  const feedbackMsg = ref<string | null>(null)
  const auth = useAuthStore()
  const sandbox = useSandboxStore()
  const router = useRouter()

  let feedbackTimer: ReturnType<typeof setTimeout> | null = null

  function showFeedback(msg: string) {
    if (feedbackTimer) clearTimeout(feedbackTimer)
    feedbackMsg.value = msg
    feedbackTimer = setTimeout(() => {
      feedbackMsg.value = null
    }, 2500)
  }

  onUnmounted(() => {
    if (feedbackTimer) clearTimeout(feedbackTimer)
  })

  async function saveAndShare(): Promise<void> {
    if (isSaving.value || !auth.isAuthenticated) return
    isSaving.value = true
    try {
      // Reuse existing saved ID if state hasn't changed since last save
      let id = !sandbox.isDirty && sandbox.savedStateId ? sandbox.savedStateId : null
      if (!id) {
        try {
          const res = await saveState({
            slotA: { engineId: sandbox.slotA.engineId, code: sandbox.slotA.code },
            slotB: { engineId: sandbox.slotB.engineId, code: sandbox.slotB.code },
            json: sandbox.json,
          })
          id = res.id
          sandbox.markSaved(id)
        } catch {
          showFeedback('Save failed')
          return
        }
      }
      await router.replace(`/s/${id}`)
      const url = `${window.location.origin}/s/${id}`
      const ok = await navigator.clipboard.writeText(url).then(() => true).catch(() => false)
      showFeedback(ok ? 'Saved! Link copied' : 'Saved!')
    } finally {
      isSaving.value = false
    }
  }

  return { isSaving, feedbackMsg, saveAndShare }
}
