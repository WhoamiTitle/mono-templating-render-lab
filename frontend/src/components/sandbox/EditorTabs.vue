<template>
  <div class="editor-tabs-container">
    <v-tabs v-model="sandbox.activeTab" density="compact" bg-color="surface">
      <v-tab value="a" class="tab-with-selector">
        <span>Template A</span>
        <EngineSelector
          v-model="sandbox.slotA.engineId"
          class="ml-2"
          @update:model-value="sandbox.markDirty()"
        />
      </v-tab>
      <v-tab value="b" class="tab-with-selector">
        <span>Template B</span>
        <EngineSelector
          v-model="sandbox.slotB.engineId"
          class="ml-2"
          @update:model-value="sandbox.markDirty()"
        />
      </v-tab>
      <v-tab value="json">JSON</v-tab>
    </v-tabs>

    <v-window v-model="sandbox.activeTab" class="tab-window">
      <v-window-item value="a" class="tab-item">
        <MonacoEditorWrapper
          v-model="sandbox.slotA.code"
          :language="langA"
          class="editor-fill"
          @update:model-value="sandbox.markDirty()"
        />
      </v-window-item>
      <v-window-item value="b" class="tab-item">
        <MonacoEditorWrapper
          v-model="sandbox.slotB.code"
          :language="langB"
          class="editor-fill"
          @update:model-value="sandbox.markDirty()"
        />
      </v-window-item>
      <v-window-item value="json" class="tab-item">
        <MonacoEditorWrapper
          v-model="sandbox.json"
          language="json"
          class="editor-fill"
          @update:model-value="sandbox.markDirty()"
        />
      </v-window-item>
    </v-window>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import MonacoEditorWrapper from './MonacoEditorWrapper.vue'
import EngineSelector from './EngineSelector.vue'
import { useSandboxStore } from '@/stores/sandbox-store'
import { useEnginesStore } from '@/stores/engines-store'

const sandbox = useSandboxStore()
const engines = useEnginesStore()

const langA = computed(() => engines.getById(sandbox.slotA.engineId)?.syntaxAlias ?? 'plaintext')
const langB = computed(() => engines.getById(sandbox.slotB.engineId)?.syntaxAlias ?? 'plaintext')
</script>

<style scoped>
.editor-tabs-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.tab-with-selector {
  display: flex;
  align-items: center;
}

.tab-window {
  flex: 1;
  min-height: 0;
  overflow: hidden;
  padding-top: 10px;
}

/* Force Vuetify window internals to fill available height */
.tab-window :deep(.v-window__container),
.tab-window :deep(.v-window-item) {
  height: 100%;
}

.tab-item {
  height: 100%;
  overflow: hidden;
}

.editor-fill {
  height: 100%;
  min-height: unset;
}
</style>
