<template>
  <v-select
    :model-value="modelValue"
    :items="enginesStore.engines"
    item-title="name"
    item-value="id"
    density="compact"
    variant="outlined"
    hide-details
    class="engine-selector"
    @update:model-value="$emit('update:modelValue', $event)"
    @mousedown.stop
    @pointerdown.stop
  />
</template>

<script setup lang="ts">
import { useEnginesStore } from '@/stores/engines-store'

defineProps<{ modelValue: string }>()
defineEmits<{ 'update:modelValue': [value: string] }>()

const enginesStore = useEnginesStore()
</script>

<style scoped>
.engine-selector {
  width: 130px;
}

:deep(.v-field__input) {
  min-height: unset !important;
  padding-top: 4px;
  padding-bottom: 4px;
}
</style>
