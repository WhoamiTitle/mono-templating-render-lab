<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStatePersistence } from '@/composables/use-state-persistence'

const route = useRoute()
const router = useRouter()
const { runRestoreChain } = useStatePersistence()
const error = ref(false)

onMounted(async () => {
  const id = route.params.id as string
  const source = await runRestoreChain(id)
  if (source === 'backend') {
    router.replace('/sandbox')
  } else {
    error.value = true
  }
})
</script>

<template>
  <v-container>
    <div v-if="!error" class="d-flex justify-center align-center" style="height: 200px">
      <v-progress-circular indeterminate color="primary" />
    </div>
    <div v-else class="text-center mt-8">
      <p class="text-medium-emphasis mb-4">State not found.</p>
      <v-btn color="primary" to="/sandbox">Go to Sandbox</v-btn>
    </div>
  </v-container>
</template>
