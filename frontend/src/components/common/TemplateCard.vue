<script setup lang="ts">
import type { Template } from '@/types'

defineProps<{
  template: Template
  showOpen?: boolean
  showClone?: boolean
  showDelete?: boolean
}>()

const emit = defineEmits<{
  open: [template: Template]
  clone: [template: Template]
  delete: [template: Template]
}>()

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString()
}
</script>

<template>
  <v-card variant="outlined">
    <v-card-title class="d-flex align-center gap-2 pt-3 pb-1">
      <span class="text-truncate">{{ template.name }}</span>
      <v-chip size="x-small" color="primary" variant="tonal" class="ml-1 text-uppercase flex-shrink-0">
        {{ template.engineId }}
      </v-chip>
    </v-card-title>
    <v-card-subtitle class="pb-2">{{ formatDate(template.createdAt) }}</v-card-subtitle>
    <v-card-text v-if="template.description" class="pt-0 pb-2 text-body-2 text-medium-emphasis">
      {{ template.description }}
    </v-card-text>
    <v-card-actions>
      <v-btn
        v-if="showOpen"
        size="small"
        variant="tonal"
        color="primary"
        @click="emit('open', template)"
      >
        Open in Sandbox
      </v-btn>
      <v-btn
        v-if="showClone"
        size="small"
        variant="text"
        @click="emit('clone', template)"
      >
        Clone
      </v-btn>
      <v-spacer />
      <v-btn
        v-if="showDelete"
        size="small"
        variant="text"
        color="error"
        @click="emit('delete', template)"
      >
        Delete
      </v-btn>
    </v-card-actions>
  </v-card>
</template>
