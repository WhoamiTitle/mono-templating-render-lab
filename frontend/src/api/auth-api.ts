import type { User } from '@/types'
import { http } from './http-client'
import { ENDPOINTS } from './endpoints'

export async function login(email: string, password: string): Promise<User> {
  const result = await http.post<{ userId: string; expiresAt: string }>(
    ENDPOINTS.auth.login,
    { email, password },
  )
  return { id: result.userId, email }
}

export async function register(email: string, password: string, _name?: string): Promise<User> {
  await http.post<{ userId: string; email: string; createdAt: string }>(
    ENDPOINTS.auth.register,
    { email, password },
  )

  return login(email, password)
}

export async function logout(): Promise<void> {
  return http.delete<void>(ENDPOINTS.auth.logout)
}

export async function forgotPassword(email: string): Promise<void> {
  return http.post<void>(ENDPOINTS.auth.forgotPassword, { email })
}

export async function changePassword(oldPassword: string, newPassword: string): Promise<void> {
  return http.post<void>(ENDPOINTS.auth.changePassword, { oldPassword, newPassword })
}

// No /me endpoint in backend — rely on stored auth state from login/register
export async function getMe(): Promise<User | null> {
  return null
}
