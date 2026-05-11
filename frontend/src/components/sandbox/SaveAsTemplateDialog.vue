<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useTemplatesStore } from '@/stores/templates-store'
import { useSandboxStore } from '@/stores/sandbox-store'

const props = defineProps<{ open: boolean }>()
const emit = defineEmits<{ 'update:open': [val: boolean]; saved: [] }>()

const store = useTemplatesStore()
const sandbox = useSandboxStore()

const name = ref('')
const description = ref('')
const isPublic = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const selectedSlot = ref<'a' | 'b'>('a')

const activeSlot = computed(() => selectedSlot.value === 'b' ? sandbox.slotB : sandbox.slotA)
const engineId = computed(() => activeSlot.value.engineId)
const code = computed(() => activeSlot.value.code)

const nameRules = [(v: string) => !!v.trim() || 'Введите название']

watch(
  () => props.open,
  open => {
    if (open) selectedSlot.value = sandbox.activeTemplateSlot()
  },
)

async function save() {
  if (!name.value.trim()) return
  saving.value = true
  error.value = null
  try {
    await store.createTemplate({
      name: name.value.trim(),
      description: description.value.trim() || undefined,
      engineId: engineId.value,
      code: code.value,
      isPublic: isPublic.value,
    })
    reset()
    emit('update:open', false)
    emit('saved')
  } catch {
    error.value = 'Ошибка сохранения шаблона. Попробуйте ещё раз.'
  } finally {
    saving.value = false
  }
}

function cancel() {
  reset()
  emit('update:open', false)
}

function reset() {
  name.value = ''
  description.value = ''
  isPublic.value = false
  selectedSlot.value = sandbox.activeTemplateSlot()
  error.value = null
}
</script>

<template>
  <v-dialog :model-value="props.open" max-width="440" @update:model-value="val => !val && cancel()">
    <v-card>
      <v-card-title class="pt-4">Сохранить как шаблон</v-card-title>
      <v-card-text>
        <v-alert v-if="error" type="error" variant="tonal" density="compact" class="mb-4">
          {{ error }}
        </v-alert>

        <v-chip size="small" :color="engineId" variant="tonal" class="mb-4 text-uppercase">
          {{ engineId }}
        </v-chip>

        <v-btn-toggle
          v-model="selectedSlot"
          mandatory
          density="compact"
          variant="outlined"
          class="mb-4"
        >
          <v-btn value="a">Слот A</v-btn>
          <v-btn value="b">Слот B</v-btn>
        </v-btn-toggle>

        <v-text-field
          v-model="name"
          label="Название"
          :rules="nameRules"
          density="compact"
          variant="outlined"
          autofocus
          class="mb-3"
          hide-details="auto"
        />
        <v-textarea
          v-model="description"
          label="Описание (необязательно)"
          density="compact"
          variant="outlined"
          rows="2"
          hide-details
          class="mb-3"
        />
        <v-switch
          v-model="isPublic"
          label="Сделать публичным"
          density="compact"
          color="primary"
          hide-details
        />
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" :disabled="saving" @click="cancel">Отмена</v-btn>
        <v-btn
          variant="tonal"
          color="primary"
          :loading="saving"
          :disabled="!name.trim()"
          @click="save"
        >
          Сохранить
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
