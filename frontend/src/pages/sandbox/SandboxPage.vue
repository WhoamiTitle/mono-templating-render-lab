<script setup lang="ts">
import { onMounted } from 'vue'
import EditorTabs from '@/components/sandbox/EditorTabs.vue'
import PreviewPanel from '@/components/sandbox/PreviewPanel.vue'
import { useStatePersistence } from '@/composables/use-state-persistence'
import { useDebouncedRender } from '@/composables/use-debounced-render'

const { runRestoreChain } = useStatePersistence()
const { previewHtml, previewError } = useDebouncedRender()

onMounted(() => {
  runRestoreChain()
})
</script>

<template>
  <div class="sandbox-page">
    <div class="sandbox-cell cell-editor">
      <EditorTabs />
    </div>

    <div class="sandbox-cell cell-preview">
      <PreviewPanel :html="previewHtml" :error="previewError" />
    </div>

    <!-- Bottom-left: Compilation status (not yet implemented) -->
    <div class="sandbox-cell cell-status" />

    <!-- Bottom-right: Template comparison (not yet implemented) -->
    <div class="sandbox-cell cell-compare" />
  </div>
</template>

<style scoped>
.sandbox-page {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 3fr 1fr;
  width: 100%;
  /* Fill viewport below navbar (~64px), never shrink below 768px total */
  height: max(calc(100vh - 64px), 768px);
}

.sandbox-cell {
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

/* Remove double borders between adjacent cells */
.sandbox-cell + .sandbox-cell {
  border-left: none;
}

.cell-status,
.cell-compare {
  border-top: none;
}

</style>
