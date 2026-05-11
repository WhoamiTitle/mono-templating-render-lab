<template>
  <div class="metrics-panel">
    <div v-if="!sandbox.metricsA && !sandbox.metricsB" class="metrics-empty">
      <div class="empty-title">Нет данных</div>
      <div class="empty-subtitle">Запуск benchmark заполнит показатели по слотам.</div>
    </div>

    <div v-else class="metrics-content">
      <div class="metrics-summary">
        <v-chip
          v-if="speedLabel"
          size="small"
          :color="speedLabel === 'Одинаковая скорость' ? 'success' : 'info'"
          variant="tonal"
        >
          {{ speedLabel }}
        </v-chip>

        <div class="summary-note">
          {{ completedSlots }} / 2 слота
        </div>
      </div>

      <div class="metrics-grid">
        <div class="slot-card" :class="{ empty: !sandbox.metricsA }">
          <div class="slot-head">
            <div>
              <div class="slot-title">Слот A</div>
              <div class="slot-engine">{{ sandbox.slotA.engineId }}</div>
            </div>
          </div>

          <div v-if="sandbox.metricsA" class="metric-list">
            <div v-for="item in metricsForA" :key="item.label" class="metric-item">
              <span>{{ item.label }}</span>
              <strong>{{ item.value }}</strong>
            </div>
          </div>
          <div v-else class="slot-empty">Нет результата</div>
        </div>

        <div class="slot-card" :class="{ empty: !sandbox.metricsB }">
          <div class="slot-head">
            <div>
              <div class="slot-title">Слот B</div>
              <div class="slot-engine">{{ sandbox.slotB.engineId }}</div>
            </div>
          </div>

          <div v-if="sandbox.metricsB" class="metric-list">
            <div v-for="item in metricsForB" :key="item.label" class="metric-item">
              <span>{{ item.label }}</span>
              <strong>{{ item.value }}</strong>
            </div>
          </div>
          <div v-else class="slot-empty">Нет результата</div>
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

const completedSlots = computed(() => Number(!!sandbox.metricsA) + Number(!!sandbox.metricsB))

const speedLabel = computed(() => {
  if (!sandbox.metricsA || !sandbox.metricsB) return null
  const a = sandbox.metricsA.avgMs
  const b = sandbox.metricsB.avgMs
  const maxAvg = Math.max(a, b)
  if (maxAvg === 0 || Math.abs(a - b) / maxAvg * 100 < 1) return 'Одинаковая скорость'
  const pct = Math.round(Math.abs(a - b) / maxAvg * 100)
  return `Слот ${a < b ? 'A' : 'B'} быстрее на ${pct}%`
})

const metricsForA = computed(() => sandbox.metricsA ? metrics(sandbox.metricsA) : [])
const metricsForB = computed(() => sandbox.metricsB ? metrics(sandbox.metricsB) : [])

function metrics(result: NonNullable<typeof sandbox.metricsA>) {
  return [
    { label: 'avg', value: formatMs(result.avgMs) },
    { label: 'min', value: formatMs(result.minMs) },
    { label: 'max', value: formatMs(result.maxMs) },
    { label: 'p95', value: formatMs(result.p95Ms) },
    { label: 'bytes', value: formatBytes(result.outputBytes) },
  ]
}
</script>

<style scoped>
.metrics-panel {
  width: 100%;
  height: 100%;
  min-height: 0;
  overflow: auto;
  padding: 10px;
}

.metrics-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 120px;
  height: 100%;
  text-align: center;
}

.empty-title {
  font-weight: 700;
}

.empty-subtitle,
.summary-note,
.slot-engine,
.slot-empty,
.metric-item span {
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}

.empty-subtitle,
.summary-note,
.slot-engine,
.slot-empty {
  margin-top: 3px;
  font-size: 0.78rem;
}

.metrics-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.metrics-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.slot-card {
  min-width: 0;
  padding: 10px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  background: rgba(var(--v-theme-on-surface), 0.025);
}

.slot-card.empty {
  opacity: 0.72;
}

.slot-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
}

.slot-title {
  font-weight: 700;
  line-height: 1.2;
}

.metric-list {
  display: grid;
  gap: 5px;
}

.metric-item {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  font-size: 0.82rem;
}

.metric-item strong {
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

@media (max-width: 760px) {
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}
</style>
