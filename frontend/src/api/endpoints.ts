// Все URL endpoint'ы для всех API-модулей.
export const ENDPOINTS = {
  auth: {
    login: '/sessions',
    register: '/users',
    logout: '/sessions/current',
    forgotPassword: '/auth/forgot-password', // not in backend yet
    changePassword: '/auth/change-password', // not in backend yet
  },
  templates: {
    list: '/templates',
    byId: (id: string) => `/templates/${id}`,
    updateBody: (id: string) => `/templates/${id}/body`,
    deactivate: (id: string) => `/templates/${id}/deactivation`,
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
