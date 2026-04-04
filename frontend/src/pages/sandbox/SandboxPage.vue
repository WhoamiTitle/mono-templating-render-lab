<script setup lang="ts">
import { onMounted } from 'vue'
import { useStatePersistence } from '@/composables/use-state-persistence'

const { runRestoreChain } = useStatePersistence()

onMounted(() => {
  runRestoreChain()
})
</script>

<template>
  <v-container>
    <div class="text-h5 font-weight-bold mb-4">Sandbox</div>
    <p class="text-medium-emphasis">Sandbox page — coming soon.</p>
  </v-container>
</template>
