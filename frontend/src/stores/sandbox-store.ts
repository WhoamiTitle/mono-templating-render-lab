import { ref, reactive } from 'vue'
import { defineStore } from 'pinia'
import type { Slot, BenchmarkResult, SandboxState } from '@/types'

const DEFAULT_PRESET: SandboxState = {
  slotA: { engineId: 'handlebars', code: '<h1>Hello, {{name}}!</h1>' },
  slotB: { engineId: 'pug', code: 'h1 Hello, #{name}!' },
  json: '{\n  "name": "World"\n}',
}

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

  function clearMetrics() {
    metricsA.value = null
    metricsB.value = null
  }

  function markDirty() {
    isDirty.value = true
    savedStateId.value = null
    clearMetrics()
  }

  function markSaved(id: string) {
    isDirty.value = false
    savedStateId.value = id
  }

  function setIterations(value: number) {
    const next = Math.max(1, Math.min(10000, Number(value) || 1))
    if (iterations.value === next) return
    iterations.value = next
    clearMetrics()
  }

  function loadState(state: SandboxState) {
    slotA.engineId = state.slotA.engineId
    slotA.code = state.slotA.code
    slotB.engineId = state.slotB.engineId
    slotB.code = state.slotB.code
    json.value = state.json
    isDirty.value = false
    savedStateId.value = null
    metricsA.value = null
    metricsB.value = null
  }

  function resetToPreset() {
    loadState(DEFAULT_PRESET)
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
    clearMetrics,
    markDirty,
    markSaved,
    setIterations,
    loadState,
    resetToPreset,
  }
})
