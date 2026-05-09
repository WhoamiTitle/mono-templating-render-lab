<template>
  <div class="editor-tabs-container">
    <v-tabs v-model="sandbox.activeTab" density="compact" bg-color="surface">
      <v-tab value="a" class="tab-with-selector">
        <span>Template A</span>
        <EngineSelector
          :model-value="sandbox.slotA.engineId"
          class="ml-2"
          @update:model-value="onEngineChange('a', $event)"
        />
      </v-tab>
      <v-tab value="b" class="tab-with-selector">
        <span>Template B</span>
        <EngineSelector
          :model-value="sandbox.slotB.engineId"
          class="ml-2"
          @update:model-value="onEngineChange('b', $event)"
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

// Default starter templates per engine — used to detect "still on default" when switching engines.
const ENGINE_DEFAULT_CODE: Record<string, string> = {
  handlebars: '<h1>Hello, {{name}}!</h1>',
  pug: 'h1 Hello, #{name}!',
  ejs: '<h1>Hello, <%= name %>!</h1>',
}

function onEngineChange(slot: 'a' | 'b', newEngineId: string) {
  const s = slot === 'a' ? sandbox.slotA : sandbox.slotB
  // Auto-swap code only when the user hasn't written custom content yet.
  if (!s.code || s.code === ENGINE_DEFAULT_CODE[s.engineId]) {
    s.code = ENGINE_DEFAULT_CODE[newEngineId] ?? ''
  }
  s.engineId = newEngineId
  sandbox.markDirty()
}
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
