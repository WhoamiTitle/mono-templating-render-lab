<template>
  <div class="action-bar">
    <div class="bar-row">
      <div class="action-group action-left">
        <v-btn
          size="small"
          variant="tonal"
          :loading="isRunning"
          :disabled="isRunning"
          @click="runBenchmark"
        >
          Запустить бенчмарк
        </v-btn>

        <v-btn
          v-if="isRunning"
          size="small"
          variant="text"
          color="error"
          @click="cancel"
        >
          Отмена
        </v-btn>

        <v-btn
          size="small"
          :variant="sandbox.mode === 'compare' ? 'tonal' : 'text'"
          :disabled="isRunning"
          @click="sandbox.mode = sandbox.mode === 'compare' ? 'editor' : 'compare'"
        >
          Сравнить
        </v-btn>
      </div>

      <div class="action-group action-center">
        <div class="iter-group">
          <v-btn
            v-for="n in ITER_PRESETS"
            :key="n"
            size="x-small"
            :variant="sandbox.iterations === n ? 'tonal' : 'text'"
            :disabled="isRunning"
            @click="sandbox.setIterations(n)"
          >
            {{ n }}
          </v-btn>
          <v-text-field
            :model-value="sandbox.iterations"
            type="number"
            label="N"
            density="compact"
            variant="outlined"
            hide-details
            min="1"
            max="10000"
            :disabled="isRunning"
            class="iterations-input"
            @update:model-value="sandbox.setIterations(Number($event))"
          />
        </div>
      </div>

      <div class="action-group action-right">
        <v-btn
          v-if="auth.isAuthenticated && hasMetrics"
          size="small"
          variant="tonal"
          :loading="isSavingRun"
          @click="saveRun"
        >
          Сохранить запуск
        </v-btn>

        <v-btn
          v-if="auth.isAuthenticated"
          size="small"
          :variant="sandbox.isDirty ? 'tonal' : 'text'"
          :loading="isSavingState"
          @click="saveAndShare"
        >
          {{ sandbox.isDirty ? 'Сохранить' : 'Сохранено ✓' }}
        </v-btn>

        <v-btn
          v-if="auth.isAuthenticated"
          size="small"
          variant="text"
          @click="saveTemplateOpen = true"
        >
          Сохранить как шаблон
        </v-btn>

        <v-btn
          size="small"
          variant="text"
          :disabled="isSavingState"
          @click="newSandbox"
        >
          Новый
        </v-btn>
      </div>
    </div>

    <v-fade-transition>
      <div v-if="feedbackMessage" class="bar-feedback" :class="{ 'text-error': benchmarkError }">
        {{ feedbackMessage }}
      </div>
    </v-fade-transition>

    <v-progress-linear
      v-if="isRunning"
      :model-value="progress * 100"
      height="2"
      class="bar-progress"
    />
  </div>

  <SaveAsTemplateDialog
    v-model:open="saveTemplateOpen"
    @saved="onTemplateSaved"
  />

  <v-snackbar v-model="templateSavedSnackbar" :timeout="3000" location="bottom right">
    Шаблон сохранён!
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { useAuthStore } from '@/stores/auth-store'
import { useBenchmark } from '@/composables/use-benchmark'
import { useSandboxShare } from '@/composables/use-sandbox-share'
import { useSaveRun } from '@/composables/use-save-run'
import { useStatePersistence } from '@/composables/use-state-persistence'
import SaveAsTemplateDialog from './SaveAsTemplateDialog.vue'

const ITER_PRESETS = [100, 500, 1000, 5000] as const

const sandbox = useSandboxStore()
const auth = useAuthStore()
const { isRunning, benchmarkError, progress, runBenchmark, cancel } = useBenchmark()
const { isSaving: isSavingState, feedbackMsg: shareMsg, saveAndShare } = useSandboxShare()
const { isSaving: isSavingRun, feedbackMsg: saveRunMsg, saveRun } = useSaveRun()
const { clearAndReset } = useStatePersistence()

const hasMetrics = computed(() => !!(sandbox.metricsA || sandbox.metricsB))
const feedbackMessage = computed(() => benchmarkError.value || shareMsg.value || saveRunMsg.value)
const saveTemplateOpen = ref(false)
const templateSavedSnackbar = ref(false)

function newSandbox() {
  clearAndReset()
}

function onTemplateSaved() {
  templateSavedSnackbar.value = true
}
</script>

<style scoped>
.action-bar {
  display: flex;
  flex-direction: column;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgb(var(--v-theme-surface));
  flex-shrink: 0;
}

.bar-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
  align-items: center;
  padding: 6px 12px;
  gap: 12px;
}

.action-group {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.action-left {
  justify-content: flex-start;
}

.action-center {
  justify-content: center;
}

.action-right {
  justify-content: flex-end;
  flex-wrap: wrap;
}

.iter-group {
  display: flex;
  align-items: center;
  gap: 4px;
}

.iterations-input {
  width: 80px;
  flex-shrink: 0;
}

.bar-feedback {
  min-height: 20px;
  padding: 0 12px 6px;
  text-align: center;
  font-size: 0.8rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}

.bar-progress {
  border-radius: 0;
}

@media (max-width: 960px) {
  .bar-row {
    grid-template-columns: 1fr;
  }

  .action-left,
  .action-center,
  .action-right {
    justify-content: center;
  }
}
</style>
