import type { User } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

export async function login(email: string, password: string): Promise<User> {
  return http.post<User>(ENDPOINTS.auth.login, { email, password })
}

export async function register(email: string, password: string, name?: string): Promise<User> {
  return http.post<User>(ENDPOINTS.auth.register, { email, password, name })
}

export async function logout(): Promise<void> {
  return http.post<void>(ENDPOINTS.auth.logout, {})
}

export async function forgotPassword(email: string): Promise<void> {
  return http.post<void>(ENDPOINTS.auth.forgotPassword, { email })
}

export async function changePassword(oldPassword: string, newPassword: string): Promise<void> {
  return http.post<void>(ENDPOINTS.auth.changePassword, { oldPassword, newPassword })
}

export async function getMe(): Promise<User | null> {
  return http.get<User>(ENDPOINTS.auth.me)
}
