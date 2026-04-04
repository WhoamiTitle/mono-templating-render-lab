import type { SandboxState } from '@/types'
import { stateMock } from './state-mock'

export async function saveState(data: SandboxState): Promise<{ id: string }> {
  return stateMock.saveState(data)
}

export async function loadState(id: string): Promise<SandboxState> {
  return stateMock.loadState(id)
}
