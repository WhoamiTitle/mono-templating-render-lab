// Mock implementation — replaces render-runs-api.ts in dev via Vite alias
import type { RenderRun } from '@/types'
import { renderRunsMock } from './render-runs-mock'

export async function getRuns(): Promise<RenderRun[]> {
  return renderRunsMock.getRuns()
}

export async function saveRun(data: Omit<RenderRun, 'id' | 'createdAt'>): Promise<RenderRun> {
  return renderRunsMock.saveRun(data)
}
