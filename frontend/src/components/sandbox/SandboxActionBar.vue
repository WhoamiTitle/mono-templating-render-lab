<template>
  <div class="action-bar">
    <div class="bar-row">
      <div class="action-left">
        <v-btn
          size="small"
          variant="tonal"
          :loading="isRunning"
          :disabled="isRunning"
          @click="runBenchmark"
        >
          Run Benchmark
        </v-btn>

        <v-btn
          v-if="isRunning"
          size="small"
          variant="text"
          color="error"
          @click="cancel"
        >
          Cancel
        </v-btn>

        <v-btn
          size="small"
          :variant="sandbox.mode === 'compare' ? 'tonal' : 'text'"
          :disabled="isRunning"
          @click="sandbox.mode = sandbox.mode === 'compare' ? 'editor' : 'compare'"
        >
          Compare
        </v-btn>

        <div class="iter-group">
          <v-btn
            v-for="n in ITER_PRESETS"
            :key="n"
            size="x-small"
            :variant="sandbox.iterations === n ? 'tonal' : 'text'"
            :disabled="isRunning"
            @click="sandbox.iterations = n"
          >
            {{ n }}
          </v-btn>
          <v-text-field
            v-model.number="sandbox.iterations"
            type="number"
            label="N"
            density="compact"
            variant="outlined"
            hide-details
            min="1"
            max="10000"
            :disabled="isRunning"
            class="iterations-input"
          />
        </div>
      </div>

      <div class="action-right">
        <v-fade-transition>
          <span v-if="benchmarkError" key="error" class="feedback-msg text-body-2 text-error">
            {{ benchmarkError }}
          </span>
          <span v-else-if="shareMsg || saveRunMsg" key="feedback" class="feedback-msg text-body-2 text-medium-emphasis">
            {{ shareMsg || saveRunMsg }}
          </span>
        </v-fade-transition>

        <v-btn
          v-if="auth.isAuthenticated && hasMetrics"
          size="small"
          variant="tonal"
          :loading="isSavingRun"
          @click="saveRun"
        >
          Save Run
        </v-btn>

        <v-btn
          v-if="auth.isAuthenticated"
          size="small"
          :variant="sandbox.isDirty ? 'tonal' : 'text'"
          :loading="isSavingState"
          @click="saveAndShare"
        >
          {{ sandbox.isDirty ? 'Save' : 'Saved ✓' }}
        </v-btn>

        <v-btn
          size="small"
          variant="text"
          :disabled="isSavingState"
          @click="newSandbox"
        >
          New
        </v-btn>
      </div>
    </div>

    <v-progress-linear
      v-if="isRunning"
      :model-value="progress * 100"
      height="2"
      class="bar-progress"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { useAuthStore } from '@/stores/auth-store'
import { useBenchmark } from '@/composables/use-benchmark'
import { useSandboxShare } from '@/composables/use-sandbox-share'
import { useSaveRun } from '@/composables/use-save-run'
import { useStatePersistence } from '@/composables/use-state-persistence'

const ITER_PRESETS = [100, 500, 1000, 5000] as const

const sandbox = useSandboxStore()
const auth = useAuthStore()
const { isRunning, benchmarkError, progress, runBenchmark, cancel } = useBenchmark()
const { isSaving: isSavingState, feedbackMsg: shareMsg, saveAndShare } = useSandboxShare()
const { isSaving: isSavingRun, feedbackMsg: saveRunMsg, saveRun } = useSaveRun()
const { clearAndReset } = useStatePersistence()

const hasMetrics = computed(() => !!(sandbox.metricsA || sandbox.metricsB))

function newSandbox() {
  clearAndReset()
}
</script>

<style scoped>
.action-bar {
  display: flex;
  flex-direction: column;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  flex-shrink: 0;
}

.bar-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 12px;
  gap: 8px;
}

.action-left,
.action-right {
  display: flex;
  align-items: center;
  gap: 8px;
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

.feedback-msg {
  font-size: 0.8rem;
}

.bar-progress {
  border-radius: 0;
}
</style>
