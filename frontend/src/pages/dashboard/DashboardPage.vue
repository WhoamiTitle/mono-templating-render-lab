<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useTemplatesStore } from '@/stores/templates-store'
import { useRenderRunsStore } from '@/stores/render-runs-store'

const templatesStore = useTemplatesStore()
const runsStore = useRenderRunsStore()

onMounted(async () => {
  await Promise.all([templatesStore.fetchMyTemplates(), runsStore.fetchRuns()])
})

const recentRuns = computed(() => runsStore.runs.slice(0, 5))

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString()
}
</script>

<template>
  <v-container>
    <div class="text-h5 font-weight-bold mb-6">Dashboard</div>

    <v-row class="mb-6">
      <v-col cols="12" sm="6">
        <v-card variant="tonal" color="primary">
          <v-card-text>
            <div class="text-h3 font-weight-bold">{{ templatesStore.templates.length }}</div>
            <div class="text-body-2 text-medium-emphasis mt-1">Templates</div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6">
        <v-card variant="tonal" color="secondary">
          <v-card-text>
            <div class="text-h3 font-weight-bold">{{ runsStore.runs.length }}</div>
            <div class="text-body-2 text-medium-emphasis mt-1">Runs</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <div class="text-h6 font-weight-medium mb-3">Recent Runs</div>

    <v-card v-if="runsStore.loading" variant="outlined">
      <v-skeleton-loader type="table-row@5" />
    </v-card>

    <v-card v-else-if="recentRuns.length === 0" variant="outlined">
      <v-card-text class="text-medium-emphasis text-center py-8">No runs yet.</v-card-text>
    </v-card>

    <v-table v-else density="compact">
      <thead>
        <tr>
          <th>Engine</th>
          <th>Avg (ms)</th>
          <th>Min (ms)</th>
          <th>P95 (ms)</th>
          <th>Size</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="run in recentRuns" :key="run.id">
          <td>
            <v-chip size="x-small" color="primary" variant="tonal" class="text-uppercase">
              {{ run.engineId }}
            </v-chip>
          </td>
          <td>{{ run.avgMs.toFixed(2) }}</td>
          <td>{{ run.minMs.toFixed(2) }}</td>
          <td>{{ run.p95Ms.toFixed(2) }}</td>
          <td>{{ (run.outputBytes / 1024).toFixed(1) }} KB</td>
          <td>{{ formatDate(run.createdAt) }}</td>
        </tr>
      </tbody>
    </v-table>
  </v-container>
</template>
