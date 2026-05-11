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
      <div>
        <div class="empty-title">Preview пустой</div>
        <div class="empty-subtitle">Измените шаблон или JSON, чтобы увидеть результат.</div>
      </div>
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
  min-height: 0;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
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
  text-align: center;
  background:
    linear-gradient(rgba(var(--v-theme-on-surface), 0.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(var(--v-theme-on-surface), 0.035) 1px, transparent 1px);
  background-size: 24px 24px;
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
