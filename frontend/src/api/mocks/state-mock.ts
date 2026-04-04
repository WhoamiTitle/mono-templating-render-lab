import type { SandboxState } from '@/types'

const store = new Map<string, SandboxState>()
let nextId = 1

function delay() {
  return new Promise(r => setTimeout(r, 100 + Math.random() * 100))
}

export const stateMock = {
  async saveState(data: SandboxState): Promise<{ id: string }> {
    await delay()
    const id = `state-${nextId++}`
    store.set(id, data)
    return { id }
  },

  async loadState(id: string): Promise<SandboxState> {
    await delay()
    const state = store.get(id)
    if (!state) throw new Error(`State ${id} not found`)
    return state
  },
}
