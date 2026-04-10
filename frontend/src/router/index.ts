import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth-store'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/sandbox',
    },
    {
      path: '/login',
      component: () => import('@/layouts/AuthLayout.vue'),
      children: [
        {
          path: '',
          name: 'login',
          component: () => import('@/pages/auth/LoginPage.vue'),
          meta: { guestOnly: true },
        },
      ],
    },
    {
      path: '/register',
      component: () => import('@/layouts/AuthLayout.vue'),
      children: [
        {
          path: '',
          name: 'register',
          component: () => import('@/pages/auth/RegisterPage.vue'),
          meta: { guestOnly: true },
        },
      ],
    },
    {
      path: '/forgot-password',
      component: () => import('@/layouts/AuthLayout.vue'),
      children: [
        {
          path: '',
          name: 'forgot-password',
          component: () => import('@/pages/auth/ForgotPasswordPage.vue'),
          meta: { guestOnly: true },
        },
      ],
    },
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      children: [
        {
          path: 'change-password',
          name: 'change-password',
          component: () => import('@/pages/auth/ChangePasswordPage.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'sandbox',
          name: 'sandbox',
          component: () => import('@/pages/sandbox/SandboxPage.vue'),
        },
        {
          path: 's/:id',
          name: 'shared-state',
          component: () => import('@/pages/share/SharedStatePage.vue'),
        },
        {
          path: 'templates',
          name: 'templates',
          component: () => import('@/pages/templates/TemplateLibraryPage.vue'),
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/pages/dashboard/DashboardPage.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'dashboard/templates',
          name: 'dashboard-templates',
          component: () => import('@/pages/dashboard/MyTemplatesPage.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'dashboard/runs',
          name: 'dashboard-runs',
          component: () => import('@/pages/dashboard/MyRunsPage.vue'),
          meta: { requiresAuth: true },
        },
      ],
    },
  ],
})

router.beforeEach(to => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated)
    return `/login?redirect=${to.path}`
  if (to.meta.guestOnly && auth.isAuthenticated)
    return '/sandbox'
})

export default router
