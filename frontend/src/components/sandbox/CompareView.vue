<template>
  <div class="compare-view">
    <div v-if="!sandbox.metricsA && !sandbox.metricsB" class="compare-empty">
      <div>
        <div class="empty-title">Нет сравнения</div>
        <div class="empty-subtitle">Benchmark заполнит вывод слотов A/B.</div>
      </div>
    </div>
    <template v-else>
      <div class="diff-bar">
        <v-chip size="x-small" :color="isIdentical ? 'success' : 'warning'">
          {{ isIdentical ? 'Вывод: идентичный' : `Различий: ${diffCount} строк` }}
        </v-chip>
      </div>
      <div class="compare-panels">
        <div class="compare-panel">
          <div class="panel-header text-caption text-medium-emphasis">
            Шаблон A · {{ engineNameA }}
          </div>
          <iframe
            v-if="sandbox.metricsA"
            :srcdoc="sandbox.metricsA.output"
            sandbox=""
            class="compare-iframe"
            title="Предпросмотр шаблона A"
          />
          <div v-else class="panel-empty">
            <span class="text-medium-emphasis text-body-2">Нет данных для слота A</span>
          </div>
        </div>
        <div class="compare-panel">
          <div class="panel-header text-caption text-medium-emphasis">
            Шаблон B · {{ engineNameB }}
          </div>
          <iframe
            v-if="sandbox.metricsB"
            :srcdoc="sandbox.metricsB.output"
            sandbox=""
            class="compare-iframe"
            title="Предпросмотр шаблона B"
          />
          <div v-else class="panel-empty">
            <span class="text-medium-emphasis text-body-2">Нет данных для слота B</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { useEnginesStore } from '@/stores/engines-store'

const sandbox = useSandboxStore()
const engines = useEnginesStore()

const engineNameA = computed(() => engines.getById(sandbox.slotA.engineId)?.name ?? sandbox.slotA.engineId)
const engineNameB = computed(() => engines.getById(sandbox.slotB.engineId)?.name ?? sandbox.slotB.engineId)

const diffCount = computed(() => {
  if (!sandbox.metricsA || !sandbox.metricsB) return 0
  const linesA = sandbox.metricsA.output.split('\n')
  const linesB = sandbox.metricsB.output.split('\n')
  const len = Math.max(linesA.length, linesB.length)
  let count = 0
  for (let i = 0; i < len; i++) {
    if (linesA[i] !== linesB[i]) count++
  }
  return count
})

const isIdentical = computed(() => !!(sandbox.metricsA && sandbox.metricsB) && diffCount.value === 0)
</script>

<style scoped>
.compare-view {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  min-height: 0;
  overflow: hidden;
}

.compare-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
}

.diff-bar {
  display: flex;
  align-items: center;
  min-height: 36px;
  padding: 6px 12px;
  flex-shrink: 0;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.compare-panels {
  display: flex;
  flex: 1;
  min-height: 0;
}

.compare-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.compare-panel:first-child {
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.panel-header {
  padding: 7px 12px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  flex-shrink: 0;
}

.compare-iframe {
  flex: 1;
  width: 100%;
  border: none;
  background: white;
}

.panel-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-title {
  font-weight: 700;
}

.empty-subtitle {
  margin-top: 4px;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  font-size: 0.82rem;
}
</style>
