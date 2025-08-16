import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const currentTheme = ref('light')
  const isSystemTheme = ref(false)

  // Initialiser le thème
  const initializeTheme = () => {
    // Vérifier localStorage d'abord
    const savedTheme = localStorage.getItem('theme')
    
    if (savedTheme) {
      currentTheme.value = savedTheme
      isSystemTheme.value = false
    } else {
      // Fallback sur prefers-color-scheme
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      currentTheme.value = prefersDark ? 'dark' : 'light'
      isSystemTheme.value = true
    }
    
    applyTheme()
  }

  // Appliquer le thème au document
  const applyTheme = () => {
    document.documentElement.setAttribute('data-theme', currentTheme.value)
  }

  // Basculer le thème
  const toggleTheme = () => {
    currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light'
    isSystemTheme.value = false
    localStorage.setItem('theme', currentTheme.value)
    applyTheme()
  }

  // Définir un thème spécifique
  const setTheme = (theme) => {
    if (['light', 'dark'].includes(theme)) {
      currentTheme.value = theme
      isSystemTheme.value = false
      localStorage.setItem('theme', theme)
      applyTheme()
    }
  }

  // Réinitialiser au thème système
  const resetToSystemTheme = () => {
    localStorage.removeItem('theme')
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    currentTheme.value = prefersDark ? 'dark' : 'light'
    isSystemTheme.value = true
    applyTheme()
  }

  // Écouter les changements de prefers-color-scheme
  const setupSystemThemeListener = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    
    const handleChange = (e) => {
      if (isSystemTheme.value) {
        currentTheme.value = e.matches ? 'dark' : 'light'
        applyTheme()
      }
    }
    
    mediaQuery.addEventListener('change', handleChange)
    
    // Retourner la fonction de nettoyage
    return () => mediaQuery.removeEventListener('change', handleChange)
  }

  // Surveiller les changements de thème
  watch(currentTheme, () => {
    applyTheme()
  })

  return {
    currentTheme,
    isSystemTheme,
    initializeTheme,
    toggleTheme,
    setTheme,
    resetToSystemTheme,
    setupSystemThemeListener
  }
})
