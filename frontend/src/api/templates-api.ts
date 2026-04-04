import type { Template } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

export async function getMyTemplates(): Promise<Template[]> {
  return http.get<Template[]>(ENDPOINTS.templates.list)
}

export async function getPublicTemplates(): Promise<Template[]> {
  return http.get<Template[]>(ENDPOINTS.templates.public)
}

export async function getTemplate(id: string): Promise<Template> {
  return http.get<Template>(ENDPOINTS.templates.byId(id))
}

export async function createTemplate(
  data: Omit<Template, 'id' | 'ownerId' | 'createdAt' | 'updatedAt'>,
): Promise<Template> {
  return http.post<Template>(ENDPOINTS.templates.list, data)
}

export async function updateTemplate(id: string, data: Partial<Template>): Promise<Template> {
  return http.put<Template>(ENDPOINTS.templates.byId(id), data)
}

export async function deleteTemplate(id: string): Promise<void> {
  return http.delete<void>(ENDPOINTS.templates.byId(id))
}

export async function cloneTemplate(id: string): Promise<Template> {
  return http.post<Template>(ENDPOINTS.templates.clone(id), {})
}
