import type { Template } from '@/types'

const store: Template[] = [
  {
    id: 'tpl-1',
    name: 'Hello World (Handlebars)',
    description: 'Simple greeting template',
    engineId: 'handlebars',
    code: 'Hello, {{name}}!',
    isPublic: true,
    ownerId: 'mock-1',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: 'tpl-2',
    name: 'Welcome (Pug)',
    description: 'Welcome page template',
    engineId: 'pug',
    code: 'p Hello #{name}',
    isPublic: false,
    ownerId: 'mock-1',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
]

let nextId = 3

function delay() {
  return new Promise(r => setTimeout(r, 100 + Math.random() * 100))
}

export const templatesMock = {
  async getMyTemplates(): Promise<Template[]> {
    await delay()
    return store.filter(t => t.ownerId === 'mock-1')
  },

  async getPublicTemplates(): Promise<Template[]> {
    await delay()
    return store.filter(t => t.isPublic)
  },

  async getTemplate(id: string): Promise<Template> {
    await delay()
    const tpl = store.find(t => t.id === id)
    if (!tpl) throw new Error(`Template ${id} not found`)
    return tpl
  },

  async createTemplate(data: Omit<Template, 'id' | 'ownerId' | 'createdAt' | 'updatedAt'>): Promise<Template> {
    await delay()
    const now = new Date().toISOString()
    const tpl: Template = {
      ...data,
      id: `tpl-${nextId++}`,
      ownerId: 'mock-1',
      createdAt: now,
      updatedAt: now,
    }
    store.push(tpl)
    return tpl
  },

  async updateTemplate(id: string, data: Partial<Template>): Promise<Template> {
    await delay()
    const idx = store.findIndex(t => t.id === id)
    if (idx === -1) throw new Error(`Template ${id} not found`)
    const updated = { ...store[idx]!, ...data, updatedAt: new Date().toISOString() } as Template
    store[idx] = updated
    return updated
  },

  async deleteTemplate(id: string): Promise<void> {
    await delay()
    const idx = store.findIndex(t => t.id === id)
    if (idx !== -1) store.splice(idx, 1)
  },

  async cloneTemplate(id: string): Promise<Template> {
    await delay()
    const source = store.find(t => t.id === id)
    if (!source) throw new Error(`Template ${id} not found`)
    const now = new Date().toISOString()
    const clone: Template = {
      ...source,
      id: `tpl-${nextId++}`,
      name: `${source.name} (copy)`,
      isPublic: false,
      createdAt: now,
      updatedAt: now,
    }
    store.push(clone)
    return clone
  },
}
