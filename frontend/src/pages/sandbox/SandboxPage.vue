<script setup lang="ts">
import { computed, onMounted } from 'vue'
import EditorTabs from '@/components/sandbox/EditorTabs.vue'
import PreviewPanel from '@/components/sandbox/PreviewPanel.vue'
import CompareView from '@/components/sandbox/CompareView.vue'
import MetricsPanel from '@/components/sandbox/MetricsPanel.vue'
import SandboxActionBar from '@/components/sandbox/SandboxActionBar.vue'
import { useStatePersistence } from '@/composables/use-state-persistence'
import { useDebouncedRender } from '@/composables/use-debounced-render'
import { useSandboxStore } from '@/stores/sandbox-store'

const sandbox = useSandboxStore()
const { runRestoreChain } = useStatePersistence()
const { previewHtml, previewError } = useDebouncedRender()
const activeLabel = computed(() => {
  if (sandbox.activeTab === 'json') return 'JSON'

  return `Слот ${sandbox.activeTab.toUpperCase()}`
})
const previewLabel = computed(() => sandbox.mode === 'compare' ? 'Сравнение' : 'Preview')
const statusLabel = computed(() => sandbox.isDirty ? 'Есть изменения' : 'Состояние сохранено')

onMounted(() => {
  runRestoreChain()
})
</script>

<template>
  <div class="sandbox-page">
    <header class="sandbox-header">
      <div class="header-title">
        <div class="text-subtitle-1 font-weight-medium">Sandbox</div>
        <div class="text-caption text-medium-emphasis">
          {{ activeLabel }} · {{ sandbox.iterations }} итераций
        </div>
      </div>

      <div class="header-status">
        <v-chip size="x-small" :color="sandbox.isDirty ? 'warning' : 'success'" variant="tonal">
          {{ statusLabel }}
        </v-chip>
        <v-chip size="x-small" :color="sandbox.mode === 'compare' ? 'info' : 'primary'" variant="tonal">
          {{ previewLabel }}
        </v-chip>
      </div>
    </header>

    <main class="main-area">
      <section class="pane pane-editor">
        <div class="pane-header">
          <div>
            <div class="pane-title">Редактор</div>
            <div class="pane-subtitle">{{ activeLabel }}</div>
          </div>
        </div>
        <EditorTabs />
      </section>

      <div class="right-column">
        <section class="pane pane-preview">
          <div class="pane-header">
            <div>
              <div class="pane-title">{{ previewLabel }}</div>
              <div class="pane-subtitle">
                {{ sandbox.mode === 'compare' ? 'Вывод слотов A/B' : 'Результат активного шаблона' }}
              </div>
            </div>
          </div>
          <CompareView v-if="sandbox.mode === 'compare'" />
          <PreviewPanel v-else :html="previewHtml" :error="previewError" />
        </section>

        <section class="pane pane-metrics">
          <div class="pane-header metrics-header">
            <div>
              <div class="pane-title">Метрики</div>
              <div class="pane-subtitle">Avg · Min · Max · P95 · Bytes</div>
            </div>
          </div>
          <MetricsPanel />
        </section>
      </div>
    </main>

    <footer class="sandbox-footer">
      <SandboxActionBar />
    </footer>
  </div>
</template>

<style scoped>
.sandbox-page {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: calc(100dvh - 64px);
  min-height: 0;
  overflow: hidden;
  background: rgb(var(--v-theme-background));
}

.sandbox-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 52px;
  padding: 8px 14px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgb(var(--v-theme-surface));
  flex-shrink: 0;
}

.sandbox-footer {
  flex-shrink: 0;
}

.header-title {
  min-width: 0;
}

.header-status {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.main-area {
  flex: 1;
  display: grid;
  grid-template-columns: minmax(420px, 1.15fr) minmax(360px, 0.85fr);
  gap: 10px;
  min-height: 0;
  padding: 10px;
}

.pane {
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
}

.pane-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 42px;
  padding: 8px 12px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  flex-shrink: 0;
}

.pane-title {
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1.2;
}

.pane-subtitle {
  margin-top: 2px;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  font-size: 0.75rem;
  line-height: 1.2;
}

.right-column {
  display: grid;
  grid-template-rows: minmax(0, 1fr) minmax(156px, 0.36fr);
  gap: 10px;
  min-height: 0;
}

.pane-editor :deep(.editor-tabs-container),
.pane-preview :deep(.preview-panel),
.pane-preview :deep(.compare-view),
.pane-metrics :deep(.metrics-panel) {
  min-height: 0;
}

@media (max-width: 1100px) {
  .sandbox-page {
    height: auto;
    min-height: calc(100dvh - 64px);
    overflow: visible;
  }

  .main-area {
    grid-template-columns: 1fr;
    grid-auto-rows: minmax(420px, auto);
  }

  .right-column {
    grid-template-rows: minmax(420px, auto) minmax(180px, auto);
  }
}

@media (max-width: 760px) {
  .sandbox-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .header-status {
    justify-content: flex-start;
  }

  .main-area {
    padding: 8px;
  }
}
</style>
