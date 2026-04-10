<script setup lang="ts">
import { onMounted } from 'vue'
import { useStatePersistence } from '@/composables/use-state-persistence'

const { runRestoreChain } = useStatePersistence()

onMounted(() => {
  runRestoreChain()
})
</script>

<template>
  <div class="sandbox-page">
    <!-- Top-left: Monaco Editor -->
    <div class="sandbox-cell cell-editor">
      <MonacoEditorWrapper
        v-model="sandbox.slotA.code"
        language="handlebars"
        class="fill-cell"
      />
    </div>

    <!-- Top-right: Preview (not yet implemented) -->
    <div class="sandbox-cell cell-preview" />

    <!-- Bottom-left: Compilation status (not yet implemented) -->
    <div class="sandbox-cell cell-status" />

    <!-- Bottom-right: Template comparison (not yet implemented) -->
    <div class="sandbox-cell cell-compare" />
  </div>
</template>

<script setup lang="ts">
import MonacoEditorWrapper from '@/components/sandbox/MonacoEditorWrapper.vue'
import { useSandboxStore } from '@/stores/sandbox-store'

const sandbox = useSandboxStore()
</script>

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

.fill-cell {
  width: 100%;
  height: 100%;
  min-height: unset;
}
</style>
