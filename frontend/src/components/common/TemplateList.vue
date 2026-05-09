<script setup lang="ts">
import type { Template } from '@/types'
import TemplateCard from './TemplateCard.vue'

defineProps<{
  templates: Template[]
  loading?: boolean
  showOpen?: boolean
  showClone?: boolean
  showDelete?: boolean
}>()

const emit = defineEmits<{
  open: [template: Template]
  clone: [template: Template]
  delete: [template: Template]
}>()
</script>

<template>
  <div v-if="loading" class="d-flex flex-column gap-3">
    <v-skeleton-loader v-for="n in 3" :key="n" type="card" />
  </div>

  <div v-else-if="templates.length === 0" class="text-center py-12 text-medium-emphasis">
    <slot name="empty">
      <p>No templates yet.</p>
    </slot>
  </div>

  <div v-else class="d-flex flex-column gap-3">
    <TemplateCard
      v-for="tpl in templates"
      :key="tpl.id"
      :template="tpl"
      :show-open="showOpen"
      :show-clone="showClone"
      :show-delete="showDelete"
      @open="emit('open', $event)"
      @clone="emit('clone', $event)"
      @delete="emit('delete', $event)"
    />
  </div>
</template>
