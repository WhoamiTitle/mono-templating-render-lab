<template>
  <div class="preview-panel">
    <v-alert
      v-if="error"
      type="error"
      variant="tonal"
      density="compact"
      class="error-alert"
    >
      <pre class="error-text">{{ error }}</pre>
    </v-alert>
    <iframe
      v-if="html !== null && html !== undefined"
      :srcdoc="html"
      sandbox="allow-scripts"
      class="preview-iframe"
      title="Template preview"
    />
    <div v-else-if="!error" class="preview-empty">
      <span class="text-medium-emphasis text-body-2">Waiting for render…</span>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  html?: string | null
  error?: string | null
}>()
</script>

<style scoped>
.preview-panel {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.error-alert {
  margin: 12px;
  flex-shrink: 0;
}

.error-text {
  white-space: pre-wrap;
  word-break: break-word;
  font-size: 0.8rem;
  margin: 0;
}

.preview-iframe {
  flex: 1;
  width: 100%;
  border: none;
  background: white;
}

.preview-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
