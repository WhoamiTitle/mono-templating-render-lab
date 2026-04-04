import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Template } from '@/types'
import * as templatesApi from '@/api/templates-api'

export const useTemplatesStore = defineStore('templates', () => {
  const templates = ref<Template[]>([])
  const loading = ref(false)

  async function fetchMyTemplates() {
    loading.value = true
    try {
      templates.value = await templatesApi.getMyTemplates()
    } finally {
      loading.value = false
    }
  }

  async function fetchPublicTemplates() {
    loading.value = true
    try {
      templates.value = await templatesApi.getPublicTemplates()
    } finally {
      loading.value = false
    }
  }

  async function createTemplate(data: Omit<Template, 'id' | 'ownerId' | 'createdAt' | 'updatedAt'>) {
    const tpl = await templatesApi.createTemplate(data)
    templates.value.push(tpl)
    return tpl
  }

  async function updateTemplate(id: string, data: Partial<Template>) {
    const updated = await templatesApi.updateTemplate(id, data)
    const idx = templates.value.findIndex(t => t.id === id)
    if (idx !== -1) templates.value[idx] = updated
    return updated
  }

  async function deleteTemplate(id: string) {
    await templatesApi.deleteTemplate(id)
    templates.value = templates.value.filter(t => t.id !== id)
  }

  async function cloneTemplate(id: string) {
    const clone = await templatesApi.cloneTemplate(id)
    templates.value.push(clone)
    return clone
  }

  return {
    templates,
    loading,
    fetchMyTemplates,
    fetchPublicTemplates,
    createTemplate,
    updateTemplate,
    deleteTemplate,
    cloneTemplate,
  }
})
