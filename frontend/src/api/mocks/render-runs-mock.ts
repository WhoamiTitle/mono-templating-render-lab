import type { RenderRun } from '@/types'

const store: RenderRun[] = []
let nextId = 1

function delay() {
  return new Promise(r => setTimeout(r, 100 + Math.random() * 100))
}

export const renderRunsMock = {
  async getRuns(): Promise<RenderRun[]> {
    await delay()
    return [...store].reverse()
  },

  async saveRun(data: Omit<RenderRun, 'id' | 'createdAt'>): Promise<RenderRun> {
    await delay()
    const run: RenderRun = {
      ...data,
      id: `run-${nextId++}`,
      createdAt: new Date().toISOString(),
    }
    store.push(run)
    return run
  },
}
