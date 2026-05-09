<script setup lang="ts">
defineProps<{
  title: string
  message: string
  open: boolean
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <v-dialog :model-value="open" max-width="400" @update:model-value="emit('cancel')">
    <v-card>
      <v-card-title class="text-h6">{{ title }}</v-card-title>
      <v-card-text>{{ message }}</v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="emit('cancel')">Cancel</v-btn>
        <v-btn color="error" variant="tonal" @click="emit('confirm')">Confirm</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
