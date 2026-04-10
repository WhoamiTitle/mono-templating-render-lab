import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { User } from '@/types'
import * as authApi from '@/api/auth-api'

export const useAuthStore = defineStore('auth', () => {
  const stored = localStorage.getItem('auth_user')
  const user = ref<User | null>(stored ? (JSON.parse(stored) as User) : null)
  const isAuthenticated = computed(() => !!user.value)

  async function login(email: string, password: string) {
    user.value = await authApi.login(email, password)
    localStorage.setItem('auth_user', JSON.stringify(user.value))
  }

  async function register(email: string, password: string, name?: string) {
    user.value = await authApi.register(email, password, name)
    localStorage.setItem('auth_user', JSON.stringify(user.value))
  }

  function logout() {
    user.value = null
    localStorage.removeItem('auth_user')
    authApi.logout().catch(() => {})
  }

  async function fetchCurrentUser() {
    user.value = await authApi.getMe()
    if (user.value) {
      localStorage.setItem('auth_user', JSON.stringify(user.value))
    } else {
      localStorage.removeItem('auth_user')
    }
  }

  return { user, isAuthenticated, login, register, logout, fetchCurrentUser }
})
