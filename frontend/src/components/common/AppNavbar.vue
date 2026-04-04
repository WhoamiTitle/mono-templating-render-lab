<script setup lang="ts">
import { useAuthStore } from '@/stores/auth-store'
import { useThemeToggle } from '@/composables/use-theme-toggle'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const { isDark, toggle } = useThemeToggle()

async function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <v-app-bar elevation="1">
    <v-app-bar-title>
      <router-link to="/sandbox" class="text-decoration-none font-weight-bold">
        RenderLab
      </router-link>
    </v-app-bar-title>

    <v-btn :to="{ name: 'sandbox' }" variant="text">Sandbox</v-btn>
    <v-btn :to="{ name: 'templates' }" variant="text">Templates</v-btn>

    <v-btn
      :icon="isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'"
      variant="text"
      @click="toggle"
    />

    <template v-if="auth.isAuthenticated">
      <v-menu>
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="text" append-icon="mdi-chevron-down">
            {{ auth.user?.name ?? auth.user?.email }}
          </v-btn>
        </template>
        <v-list>
          <v-list-item :to="{ name: 'dashboard' }" title="Dashboard" />
          <v-list-item :to="{ name: 'dashboard-templates' }" title="My Templates" />
          <v-list-item :to="{ name: 'dashboard-runs' }" title="My Runs" />
          <v-divider />
          <v-list-item title="Logout" @click="handleLogout" />
        </v-list>
      </v-menu>
    </template>

    <template v-else>
      <v-btn :to="{ name: 'login' }" variant="text">Login</v-btn>
      <v-btn :to="{ name: 'register' }" variant="outlined" class="mr-2">Register</v-btn>
    </template>
  </v-app-bar>
</template>
