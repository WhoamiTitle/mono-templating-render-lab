// Все URL endpoint'ы для всех API-модулей.
export const ENDPOINTS = {
  auth: {
    login: '/auth/login',
    register: '/auth/register',
    logout: '/auth/logout',
    forgotPassword: '/auth/forgot-password',
    changePassword: '/auth/change-password',
    me: '/auth/me',
  },
  templates: {
    list: '/templates',
    public: '/templates/public',
    byId: (id: string) => `/templates/${id}`,
    clone: (id: string) => `/templates/${id}/clone`,
  },
  renderRuns: {
    list: '/render-runs',
    create: '/render-runs',
  },
  state: {
    save: '/state',
    byId: (id: string) => `/state/${id}`,
  },
}
