import type { SandboxState } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

export async function saveState(data: SandboxState): Promise<{ id: string }> {
  return http.post<{ id: string }>(ENDPOINTS.state.save, data)
}

export async function loadState(id: string): Promise<SandboxState> {
  return http.get<SandboxState>(ENDPOINTS.state.byId(id))
}
