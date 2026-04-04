import type { User } from '@/types'

// In-memory auth state — persists for session duration
let currentUser: User | null = null

function delay() {
  return new Promise(r => setTimeout(r, 100 + Math.random() * 100))
}

export const authMock = {
  async login(email: string, _password: string): Promise<User> {
    await delay()
    currentUser = { id: 'mock-1', email, name: 'Dev User' }
    return currentUser
  },

  async register(email: string, _password: string, name?: string): Promise<User> {
    await delay()
    currentUser = { id: 'mock-2', email, name: name ?? 'New User' }
    return currentUser
  },

  async logout(): Promise<void> {
    await delay()
    currentUser = null
  },

  async forgotPassword(_email: string): Promise<void> {
    await delay()
  },

  async changePassword(_oldPassword: string, _newPassword: string): Promise<void> {
    await delay()
  },

  async getMe(): Promise<User | null> {
    await delay()
    return currentUser
  },
}
