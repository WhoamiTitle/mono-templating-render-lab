<template>
  <div class="metrics-panel">
    <div v-if="!sandbox.metricsA && !sandbox.metricsB" class="metrics-empty">
      <span class="text-medium-emphasis text-body-2">No benchmark data — click Run Benchmark.</span>
    </div>
    <div v-else class="metrics-content">
      <v-chip
        v-if="speedLabel"
        size="x-small"
        :color="speedLabel === 'Identical speed' ? 'success' : 'info'"
        class="speed-chip"
      >
        {{ speedLabel }}
      </v-chip>

      <div class="metrics-slots">
        <div v-if="sandbox.metricsA" class="metrics-slot">
          <div class="metrics-slot-label text-caption font-weight-medium text-medium-emphasis">Slot A</div>
          <table class="metrics-table text-body-2">
            <tr>
              <td class="metric-key text-medium-emphasis">avg</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsA.avgMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">min</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsA.minMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">max</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsA.maxMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">p95</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsA.p95Ms) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">size</td>
              <td class="metric-val">{{ formatBytes(sandbox.metricsA.outputBytes) }}</td>
            </tr>
          </table>
        </div>

        <v-divider v-if="sandbox.metricsA && sandbox.metricsB" vertical class="mx-3" />

        <div v-if="sandbox.metricsB" class="metrics-slot">
          <div class="metrics-slot-label text-caption font-weight-medium text-medium-emphasis">Slot B</div>
          <table class="metrics-table text-body-2">
            <tr>
              <td class="metric-key text-medium-emphasis">avg</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsB.avgMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">min</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsB.minMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">max</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsB.maxMs) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">p95</td>
              <td class="metric-val">{{ formatMs(sandbox.metricsB.p95Ms) }}</td>
            </tr>
            <tr>
              <td class="metric-key text-medium-emphasis">size</td>
              <td class="metric-val">{{ formatBytes(sandbox.metricsB.outputBytes) }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { formatMs, formatBytes } from '@/utils/format-metrics'

const sandbox = useSandboxStore()

const speedLabel = computed(() => {
  if (!sandbox.metricsA || !sandbox.metricsB) return null
  const a = sandbox.metricsA.avgMs
  const b = sandbox.metricsB.avgMs
  const maxAvg = Math.max(a, b)
  if (maxAvg === 0 || Math.abs(a - b) / maxAvg * 100 < 1) return 'Identical speed'
  const pct = Math.round(Math.abs(a - b) / maxAvg * 100)
  return `Template ${a < b ? 'A' : 'B'} faster by ${pct}%`
})
</script>

<style scoped>
.metrics-panel {
  display: flex;
  align-items: center;
  width: 100%;
  height: 100%;
  padding: 8px 16px;
  overflow: auto;
}

.metrics-empty {
  width: 100%;
  text-align: center;
}

.metrics-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-start;
}

.speed-chip {
  align-self: flex-start;
}

.metrics-slots {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.metrics-slot {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.metrics-slot-label {
  margin-bottom: 2px;
}

.metrics-table {
  border-collapse: collapse;
}

.metric-key {
  padding-right: 12px;
  font-size: 0.75rem;
  white-space: nowrap;
}

.metric-val {
  font-size: 0.8rem;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
</style>
