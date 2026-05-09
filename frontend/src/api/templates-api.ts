import type { Template } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

// Backend field names differ from frontend: templateId→id, engineType→engineId, templateBody→code
interface BackendTemplate {
  templateId: string
  ownerId: string
  name: string
  engineType: string
  templateBody: string
  isActive: boolean
  createdAt: string
  updatedAt: string
}

function fromBackend(bt: BackendTemplate): Template {
  return {
    id: bt.templateId,
    ownerId: bt.ownerId,
    name: bt.name,
    engineId: bt.engineType,
    code: bt.templateBody,
    isPublic: false,
    createdAt: bt.createdAt,
    updatedAt: bt.updatedAt,
  }
}

export async function getMyTemplates(): Promise<Template[]> {
  const res = await http.get<{ items: BackendTemplate[] }>(ENDPOINTS.templates.list)
  return res.items.map(fromBackend)
}

export async function getPublicTemplates(): Promise<Template[]> {
  // Backend has no separate public endpoint — returns actor's templates
  const res = await http.get<{ items: BackendTemplate[] }>(ENDPOINTS.templates.list)
  return res.items.map(fromBackend)
}

export async function getTemplate(id: string): Promise<Template> {
  const bt = await http.get<BackendTemplate>(ENDPOINTS.templates.byId(id))
  return fromBackend(bt)
}

export async function createTemplate(
  data: Omit<Template, 'id' | 'ownerId' | 'createdAt' | 'updatedAt'>,
): Promise<Template> {
  const bt = await http.post<BackendTemplate>(ENDPOINTS.templates.list, {
    name: data.name,
    engineType: data.engineId,
    templateBody: data.code,
  })
  return fromBackend(bt)
}

export async function updateTemplate(id: string, data: Partial<Template>): Promise<Template> {
  // Backend only supports updating the template body
  await http.put<{ templateId: string; updatedAt: string }>(ENDPOINTS.templates.updateBody(id), {
    templateBody: data.code ?? '',
  })
  return getTemplate(id)
}

export async function deleteTemplate(id: string): Promise<void> {
  // Backend uses deactivation instead of hard delete
  await http.post<unknown>(ENDPOINTS.templates.deactivate(id), {})
}

export async function cloneTemplate(id: string): Promise<Template> {
  // No clone endpoint — fetch source then create copy with modified name
  const source = await getTemplate(id)
  return createTemplate({
    name: `${source.name} (copy)`,
    engineId: source.engineId,
    code: source.code,
    isPublic: false,
    description: source.description,
  })
}
