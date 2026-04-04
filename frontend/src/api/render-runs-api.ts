import type { RenderRun } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

export async function getRuns(): Promise<RenderRun[]> {
  return http.get<RenderRun[]>(ENDPOINTS.renderRuns.list)
}

export async function saveRun(data: Omit<RenderRun, 'id' | 'createdAt'>): Promise<RenderRun> {
  return http.post<RenderRun>(ENDPOINTS.renderRuns.create, data)
}
