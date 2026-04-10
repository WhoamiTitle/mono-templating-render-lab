import type { Template } from '@/types'
import { templatesMock } from './templates-mock'

export async function getMyTemplates(): Promise<Template[]> {
  return templatesMock.getMyTemplates()
}

export async function getPublicTemplates(): Promise<Template[]> {
  return templatesMock.getPublicTemplates()
}

export async function getTemplate(id: string): Promise<Template> {
  return templatesMock.getTemplate(id)
}

export async function createTemplate(
  data: Omit<Template, 'id' | 'ownerId' | 'createdAt' | 'updatedAt'>,
): Promise<Template> {
  return templatesMock.createTemplate(data)
}

export async function updateTemplate(id: string, data: Partial<Template>): Promise<Template> {
  return templatesMock.updateTemplate(id, data)
}

export async function deleteTemplate(id: string): Promise<void> {
  return templatesMock.deleteTemplate(id)
}

export async function cloneTemplate(id: string): Promise<Template> {
  return templatesMock.cloneTemplate(id)
}
