// fetch wrapper: session cookie auth, auto-logout on 401
import { useAuthStore } from '@/stores/auth-store'
import router from '@/router'

const LOCAL_HOSTS = new Set(['localhost', '127.0.0.1'])
const withoutTrailingSlash = (value: string) => value.replace(/\/$/, '')

function getBaseUrl() {
  const configured = import.meta.env.VITE_API_URL ?? ''
  if (!configured) return ''

  if (typeof window !== 'undefined' && window.location.hostname) {
    try {
      const apiUrl = new URL(configured, window.location.origin)
      if (LOCAL_HOSTS.has(apiUrl.hostname)) {
        apiUrl.hostname = window.location.hostname
        return withoutTrailingSlash(apiUrl.toString())
      }
    } catch {
    }
  }

  return withoutTrailingSlash(configured)
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(`${getBaseUrl()}${path}`, {
    ...init,
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...init?.headers },
  })

  if (res.status === 401) {
    useAuthStore().clearSession()
    router.push('/login')
    throw new Error('Unauthorized')
  }

  if (!res.ok) {
    const text = await res.text().catch(() => res.statusText)
    throw new Error(text || `HTTP ${res.status}`)
  }

  if (res.status === 204) return undefined as T
  return res.json() as Promise<T>
}

export const http = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, body: unknown) =>
    request<T>(path, { method: 'POST', body: JSON.stringify(body) }),
  put: <T>(path: string, body: unknown) =>
    request<T>(path, { method: 'PUT', body: JSON.stringify(body) }),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
}
