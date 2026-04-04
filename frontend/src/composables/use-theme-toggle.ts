import { computed } from 'vue'
import { useTheme } from 'vuetify'

const STORAGE_KEY = 'theme'

// Persists theme preference in localStorage
export function useThemeToggle() {
  const theme = useTheme()

  const isDark = computed(() => theme.global.name.value === 'dark')

  function toggle() {
    const next = isDark.value ? 'light' : 'dark'
    theme.global.name.value = next
    localStorage.setItem(STORAGE_KEY, next)
  }

  return { isDark, toggle }
}

// Call once at app startup to restore saved theme
export function restoreTheme() {
  const saved = localStorage.getItem(STORAGE_KEY)
  return saved === 'dark' || saved === 'light' ? saved : 'light'
}
