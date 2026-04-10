<template>
  <div ref="containerRef" class="monaco-editor-container" />
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useTheme } from 'vuetify'
import type * as Monaco from 'monaco-editor'

let monaco: typeof Monaco | null = null

const props = withDefaults(
  defineProps<{
    modelValue: string
    language: string
    readonly?: boolean
  }>(),
  { readonly: false },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const containerRef = ref<HTMLDivElement | null>(null)
const theme = useTheme()

let editor: Monaco.editor.IStandaloneCodeEditor | null = null
let resizeObserver: ResizeObserver | null = null

let isUpdatingFromProp = false
let destroyed = false

onMounted(async () => {
  const editorWorker = (await import('monaco-editor/esm/vs/editor/editor.worker?worker')).default
  self.MonacoEnvironment = {
    getWorker(_workerId: string, _label: string) {
      return new editorWorker()
    },
  }

  monaco = await import('monaco-editor')

  if (destroyed || !containerRef.value) return

  editor = monaco.editor.create(containerRef.value, {
    value: props.modelValue,
    language: props.language,
    theme: theme.global.name.value === 'dark' ? 'vs-dark' : 'vs',
    minimap: { enabled: false },
    wordWrap: 'on',
    fontSize: 14,
    readOnly: props.readonly,
    automaticLayout: false,
    scrollBeyondLastLine: false,
  })

  editor.onDidChangeModelContent(() => {
    if (isUpdatingFromProp) return
    emit('update:modelValue', editor!.getValue())
  })

  resizeObserver = new ResizeObserver(() => {
    editor?.layout()
  })
  resizeObserver.observe(containerRef.value)
})

watch(
  () => props.language,
  (lang) => {
    if (!editor || !monaco) return
    const model = editor.getModel()
    if (model) {
      monaco.editor.setModelLanguage(model, lang)
    }
  },
)

watch(
  () => props.modelValue,
  (value) => {
    if (!editor) return
    if (editor.getValue() === value) return
    isUpdatingFromProp = true
    editor.setValue(value)
    isUpdatingFromProp = false
  },
)

watch(
  () => props.readonly,
  (val) => {
    editor?.updateOptions({ readOnly: val })
  },
)

watch(
  () => theme.global.name.value,
  (themeName) => {
    if (!monaco) return
    monaco.editor.setTheme(themeName === 'dark' ? 'vs-dark' : 'vs')
  },
)

onBeforeUnmount(() => {
  destroyed = true
  resizeObserver?.disconnect()
  editor?.dispose()
  editor = null
})

defineExpose({
  focus: () => editor?.focus(),
})
</script>

<style scoped>
.monaco-editor-container {
  width: 100%;
  height: 100%;
  min-height: 200px;
}
</style>
