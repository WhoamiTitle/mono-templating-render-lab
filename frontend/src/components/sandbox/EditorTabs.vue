<template>
  <div class="editor-tabs-container">
    <div class="editor-toolbar">
      <v-tabs v-model="sandbox.activeTab" density="compact" class="editor-tabs">
        <v-tab value="a" class="editor-tab">
          <span class="tab-label">A</span>
          <v-chip size="x-small" :color="engineColor(sandbox.slotA.engineId)" variant="tonal" class="text-uppercase">
            {{ sandbox.slotA.engineId }}
          </v-chip>
        </v-tab>
        <v-tab value="b" class="editor-tab">
          <span class="tab-label">B</span>
          <v-chip size="x-small" :color="engineColor(sandbox.slotB.engineId)" variant="tonal" class="text-uppercase">
            {{ sandbox.slotB.engineId }}
          </v-chip>
        </v-tab>
        <v-tab value="json" class="editor-tab">
          <span class="tab-label">JSON</span>
        </v-tab>
      </v-tabs>

      <div v-if="sandbox.activeTab !== 'json'" class="engine-control">
        <span class="control-label">Движок</span>
        <EngineSelector
          :model-value="activeEngineId"
          @update:model-value="onEngineChange(sandbox.activeTemplateSlot(), $event)"
        />
      </div>
    </div>

    <div class="slot-strip">
      <button
        type="button"
        class="slot-pill"
        :class="{ active: sandbox.activeTab === 'a' }"
        @click="sandbox.activeTab = 'a'"
      >
        <span>A</span>
        <strong>{{ engineNameA }}</strong>
      </button>
      <button
        type="button"
        class="slot-pill"
        :class="{ active: sandbox.activeTab === 'b' }"
        @click="sandbox.activeTab = 'b'"
      >
        <span>B</span>
        <strong>{{ engineNameB }}</strong>
      </button>
      <button
        type="button"
        class="slot-pill"
        :class="{ active: sandbox.activeTab === 'json' }"
        @click="sandbox.activeTab = 'json'"
      >
        <span>JSON</span>
        <strong>Context</strong>
      </button>
    </div>

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
const engineNameA = computed(() => engines.getById(sandbox.slotA.engineId)?.name ?? sandbox.slotA.engineId)
const engineNameB = computed(() => engines.getById(sandbox.slotB.engineId)?.name ?? sandbox.slotB.engineId)
const activeEngineId = computed(() => sandbox.activeTemplateSlot() === 'b' ? sandbox.slotB.engineId : sandbox.slotA.engineId)

const ENGINE_DEFAULT_CODE: Record<string, string> = {
  handlebars: '<h1>Hello, {{name}}!</h1>',
  pug: 'h1 Hello, #{name}!',
  ejs: '<h1>Hello, <%= name %>!</h1>',
}

function engineColor(id: string) {
  const map: Record<string, string> = { handlebars: 'orange', pug: 'teal', ejs: 'deep-purple' }
  return map[id] ?? 'primary'
}

function onEngineChange(slot: 'a' | 'b', newEngineId: string) {
  const s = slot === 'a' ? sandbox.slotA : sandbox.slotB
  if (!s.code || s.code === ENGINE_DEFAULT_CODE[s.engineId]) {
    s.code = ENGINE_DEFAULT_CODE[newEngineId] ?? ''
  }
  s.engineId = newEngineId
  sandbox.activeTab = slot
  sandbox.markDirty()
}
</script>

<style scoped>
.editor-tabs-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 0;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.editor-toolbar {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: center;
  gap: 12px;
  min-height: 48px;
  padding: 6px 10px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.editor-tabs {
  min-width: 0;
}

.editor-tab {
  gap: 8px;
  min-width: 88px;
  letter-spacing: 0;
}

.tab-label {
  font-weight: 700;
}

.engine-control {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.control-label {
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}

.slot-strip {
  display: none;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 6px;
  padding: 8px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.slot-pill {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  min-width: 0;
  padding: 7px 9px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  background: transparent;
  font: inherit;
}

.slot-pill.active {
  color: rgb(var(--v-theme-primary));
  border-color: rgba(var(--v-theme-primary), 0.65);
  background: rgba(var(--v-theme-primary), 0.08);
}

.slot-pill strong {
  min-width: 0;
  overflow: hidden;
  font-size: 0.75rem;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tab-window {
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

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

@media (max-width: 760px) {
  .editor-toolbar {
    grid-template-columns: 1fr;
  }

  .editor-tabs {
    display: none;
  }

  .slot-strip {
    display: grid;
  }

  .engine-control {
    justify-content: space-between;
  }
}
</style>
