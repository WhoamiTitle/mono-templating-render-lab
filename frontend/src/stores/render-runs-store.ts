import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { RenderRun } from '@/types'
import * as renderRunsApi from '@/api/render-runs-api'

export const useRenderRunsStore = defineStore('render-runs', () => {
  const runs = ref<RenderRun[]>([])
  const loading = ref(false)

  async function fetchRuns() {
    loading.value = true
    try {
      runs.value = await renderRunsApi.getRuns()
    } finally {
      loading.value = false
    }
  }

  function addRun(run: RenderRun) {
    runs.value.unshift(run)
  }

  return { runs, loading, fetchRuns, addRun }
})
