import { ref, reactive } from 'vue'
import { defineStore } from 'pinia'
import type { Slot, BenchmarkResult } from '@/types'

export const useSandboxStore = defineStore('sandbox', () => {
  const slotA = reactive<Slot>({ engineId: 'handlebars', code: '' })
  const slotB = reactive<Slot>({ engineId: 'handlebars', code: '' })
  const json = ref('{}')
  const activeTab = ref<'a' | 'b' | 'json'>('a')
  const mode = ref<'editor' | 'compare'>('editor')
  const metricsA = ref<BenchmarkResult | null>(null)
  const metricsB = ref<BenchmarkResult | null>(null)
  const iterations = ref(100)
  const isDirty = ref(false)
  const savedStateId = ref<string | null>(null)

  function markDirty() {
    isDirty.value = true
    savedStateId.value = null
  }

  function markSaved(id: string) {
    isDirty.value = false
    savedStateId.value = id
  }

  return {
    slotA,
    slotB,
    json,
    activeTab,
    mode,
    metricsA,
    metricsB,
    iterations,
    isDirty,
    savedStateId,
    markDirty,
    markSaved,
  }
})
