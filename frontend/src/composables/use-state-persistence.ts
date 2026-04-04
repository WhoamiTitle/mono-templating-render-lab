import { watch, ref, nextTick } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { loadState as apiLoadState } from '@/api/state-api'
import type { SandboxState } from '@/types'

const LS_KEY = 'sandbox_state'
const DEBOUNCE_MS = 1000

export function useStatePersistence() {
  const store = useSandboxStore()
  const isRestoring = ref(false)
  let debounceTimer: ReturnType<typeof setTimeout> | null = null

  watch(
    () => ({
      slotA: { ...store.slotA },
      slotB: { ...store.slotB },
      json: store.json,
    }),
    (state) => {
      if (isRestoring.value) return
      if (debounceTimer) clearTimeout(debounceTimer)
      debounceTimer = setTimeout(() => {
        try {
          localStorage.setItem(LS_KEY, JSON.stringify(state))
        } catch {
        }
      }, DEBOUNCE_MS)
    },
  )

  function isSlot(obj: unknown): boolean {
    if (!obj || typeof obj !== 'object') return false
    const s = obj as Record<string, unknown>
    return typeof s.engineId === 'string' && typeof s.code === 'string'
  }

  function isValidState(obj: unknown): obj is SandboxState {
    if (!obj || typeof obj !== 'object') return false
    const s = obj as Record<string, unknown>
    return isSlot(s.slotA) && isSlot(s.slotB) && typeof s.json === 'string'
  }

  function restoreFromLocalStorage(): boolean {
    try {
      const raw = localStorage.getItem(LS_KEY)
      if (!raw) return false
      const parsed: unknown = JSON.parse(raw)
      if (!isValidState(parsed)) return false
      store.loadState(parsed)
      return true
    } catch {
      return false
    }
  }

  async function restoreFromBackend(id: string): Promise<boolean> {
    try {
      const state = await apiLoadState(id)
      store.loadState(state)
      return true
    } catch {
      return false
    }
  }

  async function runRestoreChain(
    shareId?: string,
  ): Promise<'backend' | 'localstorage' | 'preset'> {
    isRestoring.value = true
    try {
      if (shareId) {
        const ok = await restoreFromBackend(shareId)
        if (ok) return 'backend'
      }
      if (restoreFromLocalStorage()) return 'localstorage'
      store.resetToPreset()
      return 'preset'
    } finally {
      await nextTick()
      isRestoring.value = false
    }
  }

  return { runRestoreChain, isRestoring }
}
