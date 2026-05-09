<script setup lang="ts">
import { onMounted } from 'vue'
import { useRenderRunsStore } from '@/stores/render-runs-store'

const store = useRenderRunsStore()

onMounted(() => store.fetchRuns())

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString()
}
</script>

<template>
  <v-container>
    <div class="text-h5 font-weight-bold mb-5">My Runs</div>

    <v-card v-if="store.loading" variant="outlined">
      <v-skeleton-loader type="table-row@5" />
    </v-card>

    <v-card v-else-if="store.runs.length === 0" variant="outlined">
      <v-card-text class="text-medium-emphasis text-center py-10">
        <p class="mb-3">No runs yet.</p>
        <v-btn :to="{ name: 'sandbox' }" color="primary" variant="tonal">Run a Benchmark</v-btn>
      </v-card-text>
    </v-card>

    <v-table v-else>
      <thead>
        <tr>
          <th>Engine</th>
          <th>Iterations</th>
          <th>Avg (ms)</th>
          <th>Min (ms)</th>
          <th>P95 (ms)</th>
          <th>Size (KB)</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="run in store.runs" :key="run.id">
          <td>
            <v-chip size="x-small" color="primary" variant="tonal" class="text-uppercase">
              {{ run.engineId }}
            </v-chip>
          </td>
          <td>{{ run.iterations }}</td>
          <td>{{ run.avgMs.toFixed(2) }}</td>
          <td>{{ run.minMs.toFixed(2) }}</td>
          <td>{{ run.p95Ms.toFixed(2) }}</td>
          <td>{{ (run.outputBytes / 1024).toFixed(1) }}</td>
          <td>{{ formatDate(run.createdAt) }}</td>
        </tr>
      </tbody>
    </v-table>
  </v-container>
</template>
