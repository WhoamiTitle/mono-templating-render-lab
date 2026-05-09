// fetch wrapper: session cookie + x-actor-id header auth, auto-logout on 401
import { useAuthStore } from '@/stores/auth-store'
import router from '@/router'

const getBaseUrl = () => import.meta.env.VITE_API_URL ?? ''

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const auth = useAuthStore()
  const authHeaders: Record<string, string> = {}
  if (auth.user?.id) {
    authHeaders['x-actor-id'] = auth.user.id
  }

  const res = await fetch(`${getBaseUrl()}${path}`, {
    ...init,
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...authHeaders, ...init?.headers },
  })

  if (res.status === 401) {
    useAuthStore().logout()
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
