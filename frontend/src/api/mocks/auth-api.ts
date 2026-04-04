// Mock implementation — replaces auth-api.ts in dev via Vite alias
import type { User } from '@/types'
import { authMock } from './auth-mock'

export async function login(email: string, password: string): Promise<User> {
  return authMock.login(email, password)
}

export async function register(email: string, password: string, name?: string): Promise<User> {
  return authMock.register(email, password, name)
}

export async function logout(): Promise<void> {
  return authMock.logout()
}

export async function forgotPassword(email: string): Promise<void> {
  return authMock.forgotPassword(email)
}

export async function changePassword(oldPassword: string, newPassword: string): Promise<void> {
  return authMock.changePassword(oldPassword, newPassword)
}

export async function getMe(): Promise<User | null> {
  return authMock.getMe()
}
