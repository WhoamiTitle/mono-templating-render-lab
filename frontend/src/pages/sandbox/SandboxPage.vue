<script setup lang="ts">
import { onMounted } from 'vue'
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

onMounted(() => {
  runRestoreChain()
})
</script>

<template>
  <div class="sandbox-page">
    <div class="main-area">
      <div class="cell cell-editor">
        <EditorTabs />
      </div>

      <div class="right-column">
        <div class="cell cell-preview">
          <CompareView v-if="sandbox.mode === 'compare'" />
          <PreviewPanel v-else :html="previewHtml" :error="previewError" />
        </div>
        <div class="cell cell-metrics">
          <MetricsPanel />
        </div>
      </div>
    </div>

    <SandboxActionBar />
  </div>
</template>

<style scoped>
.sandbox-page {
  display: flex;
  flex-direction: column;
  width: 100%;
  /* Fill viewport below navbar (~64px), never shrink below 768px total */
  height: max(calc(100vh - 64px), 768px);
}

.main-area {
  flex: 1;
  display: flex;
  min-height: 0;
}

.cell {
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.cell-editor {
  flex: 1;
}

.right-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  border-left: none;
}

.cell-preview {
  flex: 2;
  min-height: 0;
  border-left: none;
}

.cell-metrics {
  flex: 1;
  min-height: 0;
  border-top: none;
  border-left: none;
}
</style>
