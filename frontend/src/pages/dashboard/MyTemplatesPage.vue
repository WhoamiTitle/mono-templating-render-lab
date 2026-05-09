<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTemplatesStore } from '@/stores/templates-store'
import { useSandboxStore } from '@/stores/sandbox-store'
import type { Template } from '@/types'
import TemplateList from '@/components/common/TemplateList.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const router = useRouter()
const store = useTemplatesStore()
const sandbox = useSandboxStore()

const deleteTarget = ref<Template | null>(null)
const actionError = ref<string | null>(null)

onMounted(() => store.fetchMyTemplates())

async function openInSandbox(tpl: Template) {
  sandbox.slotA.engineId = tpl.engineId
  sandbox.slotA.code = tpl.code
  sandbox.markDirty()
  await router.push('/sandbox')
}

async function cloneTemplate(tpl: Template) {
  actionError.value = null
  try {
    await store.cloneTemplate(tpl.id)
  } catch {
    actionError.value = 'Clone failed.'
  }
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  actionError.value = null
  try {
    await store.deleteTemplate(deleteTarget.value.id)
  } catch {
    actionError.value = 'Delete failed.'
  } finally {
    deleteTarget.value = null
  }
}
</script>

<template>
  <v-container>
    <div class="d-flex align-center mb-5">
      <div class="text-h5 font-weight-bold">My Templates</div>
    </div>

    <v-alert v-if="actionError" type="error" variant="tonal" class="mb-4" closable @click:close="actionError = null">
      {{ actionError }}
    </v-alert>

    <TemplateList
      :templates="store.templates"
      :loading="store.loading"
      show-open
      show-clone
      show-delete
      @open="openInSandbox"
      @clone="cloneTemplate"
      @delete="deleteTarget = $event"
    >
      <template #empty>
        <p class="mb-3">No templates yet.</p>
        <v-btn :to="{ name: 'sandbox' }" color="primary" variant="tonal">Open Sandbox</v-btn>
      </template>
    </TemplateList>

    <ConfirmDialog
      :open="!!deleteTarget"
      title="Delete template"
      :message="`Delete &quot;${deleteTarget?.name}&quot;? This cannot be undone.`"
      @confirm="confirmDelete"
      @cancel="deleteTarget = null"
    />
  </v-container>
</template>
