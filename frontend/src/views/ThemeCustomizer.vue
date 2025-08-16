<template>
  <div class="min-h-screen bg-theme-bg text-theme-text p-6">
    <!-- Notifications -->
    <Notification
      v-if="showNotification"
      :type="notificationType"
      :title="notificationTitle"
      :message="notificationMessage"
      @close="showNotification = false"
    />
    
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-theme-primary mb-4">🎨 Personnalisateur de Thème</h1>
        <p class="text-theme-text-muted text-lg">Personnalisez vos couleurs de thème pour les modes clair et sombre</p>
      </div>

      <!-- Theme Mode Toggle -->
      <div class="flex justify-center mb-8">
        <div class="bg-theme-card rounded-lg p-4 shadow-lg">
          <div class="flex items-center space-x-4">
            <button
              @click="currentMode = 'light'"
              :class="[
                'px-6 py-3 rounded-lg font-medium transition-all duration-200',
                currentMode === 'light'
                  ? 'bg-theme-primary text-white shadow-lg'
                  : 'bg-theme-bg-muted text-theme-text-muted hover:bg-theme-border'
              ]"
            >
              ☀️ Light Mode
            </button>
            <button
              @click="currentMode = 'dark'"
              :class="[
                'px-6 py-3 rounded-lg font-medium transition-all duration-200',
                currentMode === 'dark'
                  ? 'bg-theme-primary text-white shadow-lg'
                  : 'bg-theme-bg-muted text-theme-text-muted hover:bg-theme-border'
              ]"
            >
              🌙 Dark Mode
            </button>
          </div>
        </div>
      </div>

      <!-- Color Customization Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Background Colors -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
                      <h3 class="text-xl font-semibold text-theme-primary mb-4">🎨 Couleurs d'Arrière-plan</h3>
            <div class="space-y-4">
              <ColorPicker
                label="Arrière-plan Principal"
                v-model="customColors[currentMode].bg"
                description="Arrière-plan principal de la page"
              />
              <ColorPicker
                label="Arrière-plan Atténué"
                v-model="customColors[currentMode]['bg-muted']"
                description="Arrière-plans secondaires, cartes, etc."
              />
              <ColorPicker
                label="Arrière-plan des Cartes"
                v-model="customColors[currentMode].card"
                description="Arrière-plans des composants et cartes"
              />
            </div>
        </div>

        <!-- Text Colors -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">📝 Couleurs de Texte</h3>
          <div class="space-y-4">
            <ColorPicker
              label="Texte Principal"
              v-model="customColors[currentMode].text"
              description="Couleur principale du texte"
            />
            <ColorPicker
              label="Texte Atténué"
              v-model="customColors[currentMode]['text-muted']"
              description="Texte secondaire, étiquettes, etc."
            />
          </div>
        </div>

        <!-- Accent Colors -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">✨ Couleurs d'Accent</h3>
          <div class="space-y-4">
            <ColorPicker
              label="Couleur Principale"
              v-model="customColors[currentMode].primary"
              description="Accent principal, boutons, liens"
            />
            <ColorPicker
              label="Survol Principal"
              v-model="customColors[currentMode]['primary-hover']"
              description="États de survol pour les éléments principaux"
            />
            <ColorPicker
              label="Couleur des Liens"
              v-model="customColors[currentMode].link"
              description="Couleur du texte des liens"
            />
            <ColorPicker
              label="Couleur des Anneaux"
              v-model="customColors[currentMode].ring"
              description="Anneaux de focus et contours"
            />
          </div>
        </div>

        <!-- Status Colors -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">🚦 Couleurs de Statut</h3>
          <div class="space-y-4">
            <ColorPicker
              label="Succès"
              v-model="customColors[currentMode].success"
              description="Messages de succès, confirmations"
            />
            <ColorPicker
              label="Avertissement"
              v-model="customColors[currentMode].warning"
              description="Messages d'avertissement, alertes"
            />
            <ColorPicker
              label="Erreur"
              v-model="customColors[currentMode].error"
              description="Messages d'erreur, échecs"
            />
          </div>
        </div>

        <!-- Border Colors -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">🔲 Couleurs de Bordure</h3>
          <div class="space-y-4">
            <ColorPicker
              label="Couleur de Bordure"
              v-model="customColors[currentMode].border"
              description="Bordures générales et séparateurs"
            />
          </div>
        </div>

        <!-- Preview -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">👁️ Aperçu en Temps Réel</h3>
          <div class="space-y-4">
            <div class="p-4 rounded-lg border-2 border-theme-border">
              <h4 class="font-semibold mb-2">Exemple de Carte</h4>
              <p class="text-theme-text-muted mb-3">Ceci montre comment vos couleurs apparaîtront</p>
              <button class="bg-theme-primary hover:bg-theme-primary-hover text-white px-4 py-2 rounded-lg transition-colors">
                Bouton Exemple
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-center space-x-4 mb-8">
        <button
          @click="applyCustomTheme"
          class="bg-theme-success hover:bg-theme-success/80 text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          ✅ Appliquer le Thème Personnalisé
        </button>
        
        <button
          @click="previewCustomTheme"
          class="bg-theme-primary hover:bg-theme-primary-hover text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          👁️ Prévisualiser
        </button>
        <button
          @click="resetToDefault"
          class="bg-theme-warning hover:bg-theme-warning/80 text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          🔄 Réinitialiser aux Couleurs Initiales
        </button>
        <button
          @click="reapplyDefaultThemes"
          class="bg-theme-link hover:bg-theme-primary-hover text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          🎨 Réappliquer les Thèmes
        </button>
        <button
          @click="exportTheme"
          class="bg-theme-primary hover:bg-theme-primary-hover text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          📤 Exporter le Thème
        </button>
        <button
          @click="importTheme"
          class="bg-theme-link hover:bg-theme-primary-hover text-white px-8 py-3 rounded-lg font-medium transition-colors shadow-lg"
        >
          📥 Importer un Thème
        </button>
      </div>

      <!-- Theme Information -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg">
        <h3 class="text-xl font-semibold text-theme-primary mb-4">ℹ️ Informations sur le Thème</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold text-theme-text mb-2">Thème Actuel</h4>
            <p class="text-theme-text-muted">Mode: <span class="font-mono">{{ currentMode === 'light' ? 'Clair' : 'Sombre' }}</span></p>
            <p class="text-theme-text-muted">Personnalisé: <span class="font-mono">{{ isCustomTheme ? 'Oui' : 'Non' }}</span></p>
          </div>
          <div>
            <h4 class="font-semibold text-theme-text mb-2">Stockage</h4>
            <p class="text-theme-text-muted">Thème: <span class="font-mono">{{ storedTheme === 'light' ? 'Clair' : storedTheme === 'dark' ? 'Sombre' : 'Système' }}</span></p>
            <p class="text-theme-text-muted">Couleurs personnalisées sauvegardées dans localStorage</p>
          </div>
        </div>
      </div>

      <!-- Hidden file input for import -->
      <input
        ref="fileInput"
        type="file"
        accept=".json"
        @change="handleFileImport"
        class="hidden"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useThemeStore } from '@/stores/themeStore'
import ColorPicker from '@/components/ColorPicker.vue'
import Notification from '@/components/Notification.vue'

const themeStore = useThemeStore()
const currentMode = ref('light')
const fileInput = ref(null)

// Notification state
const showNotification = ref(false)
const notificationType = ref('success')
const notificationTitle = ref('')
const notificationMessage = ref('')

// Default theme colors
const defaultColors = {
  light: {
    bg: '#ffffff',
    'bg-muted': '#f7f8fa',
    card: '#ffffff',
    border: '#e6e8ec',
    text: '#0b1220',
    'text-muted': '#5b6472',
    primary: '#2563eb',
    'primary-hover': '#1d4ed8',
    link: '#2563eb',
    ring: '#93c5fd',
    success: '#16a34a',
    warning: '#f59e0b',
    error: '#dc2626'
  },
  dark: {
    bg: '#0b0b0f',
    'bg-muted': '#121218',
    card: '#14141b',
    border: '#262633',
    text: '#f4f6fb',
    'text-muted': '#a8adbb',
    primary: '#d946ef',
    'primary-hover': '#c026d3',
    link: '#e879f9',
    ring: '#f0abfc',
    success: '#22c55e',
    warning: '#facc15',
    error: '#ef4444'
  }
}

// Custom colors (start with defaults)
const customColors = ref({
  light: { ...defaultColors.light },
  dark: { ...defaultColors.dark }
})

// Computed properties
const isCustomTheme = computed(() => {
  const stored = localStorage.getItem('erebor-custom-colors')
  return stored !== null
})

const storedTheme = computed(() => {
  return themeStore.currentTheme
})

// Load custom colors from localStorage
const loadCustomColors = () => {
  const stored = localStorage.getItem('erebor-custom-colors')
  if (stored) {
    try {
      const parsed = JSON.parse(stored)
      customColors.value = { ...defaultColors, ...parsed }
    } catch (error) {
      console.error('Failed to parse custom colors:', error)
    }
  }
}

// Save custom colors to localStorage
const saveCustomColors = () => {
  localStorage.setItem('erebor-custom-colors', JSON.stringify(customColors.value))
}

// Preview custom theme without applying it permanently
const previewCustomTheme = () => {
  const root = document.documentElement
  const colors = customColors.value[currentMode.value]
  
  // Apply all colors as CSS variables temporarily
  Object.entries(colors).forEach(([key, value]) => {
    root.style.setProperty(`--${key}`, value)
    
    // Also set RGB versions for opacity usage
    if (value.startsWith('#')) {
      const rgb = hexToRgb(value)
      if (rgb) {
        root.style.setProperty(`--${key}-rgb`, `${rgb.r}, ${rgb.g}, ${rgb.b}`)
      }
    }
  })
  
  // Show preview notification
  showNotificationMessage('info', 'Prévisualisation', 'Votre thème est en mode prévisualisation. Cliquez sur "Appliquer" pour le sauvegarder définitivement.')
}

// Apply custom theme to CSS variables
const applyCustomTheme = () => {
  const root = document.documentElement
  const colors = customColors.value[currentMode.value]
  
  // Apply all colors as CSS variables
  Object.entries(colors).forEach(([key, value]) => {
    root.style.setProperty(`--${key}`, value)
    
    // Also set RGB versions for opacity usage
    if (value.startsWith('#')) {
      const rgb = hexToRgb(value)
      if (rgb) {
        root.style.setProperty(`--${key}-rgb`, `${rgb.r}, ${rgb.g}, ${rgb.b}`)
      }
    }
  })
  
  saveCustomColors()
  
  // Show success notification
  showNotificationMessage('success', 'Thème Appliqué', 'Votre thème personnalisé a été appliqué avec succès !')
}

// Reapply default themes with their initial colors
const reapplyDefaultThemes = () => {
  // Remove custom colors from localStorage
  localStorage.removeItem('erebor-custom-colors')
  
  // Reset custom colors to defaults
  customColors.value = {
    light: { ...defaultColors.light },
    dark: { ...defaultColors.dark }
  }
  
  // Reset CSS variables to default theme values
  const root = document.documentElement
  Object.keys(defaultColors.light).forEach(key => {
    root.style.removeProperty(`--${key}`)
    root.style.removeProperty(`--${key}-rgb`)
  })
  
  // Reapply the current theme from the store
  themeStore.applyCustomColors()
  
  showNotificationMessage('success', 'Thèmes Réappliqués', 'Les thèmes par défaut ont été restaurés avec leurs couleurs initiales !')
}

// Show notification message
const showNotificationMessage = (type, title, message) => {
  notificationType.value = type
  notificationTitle.value = title
  notificationMessage.value = message
  showNotification.value = true
}

// Reset to default theme
const resetToDefault = () => {
  if (confirm('Êtes-vous sûr de vouloir réinitialiser aux couleurs par défaut ?')) {
    customColors.value = {
      light: { ...defaultColors.light },
      dark: { ...defaultColors.dark }
    }
    
    // Remove custom colors from localStorage
    localStorage.removeItem('erebor-custom-colors')
    
    // Reset CSS variables to default
    const root = document.documentElement
    Object.keys(defaultColors.light).forEach(key => {
      root.style.removeProperty(`--${key}`)
      root.style.removeProperty(`--${key}-rgb`)
    })
    
    // Restore the current theme from the store
    themeStore.applyCustomColors()
    
    showNotificationMessage('success', 'Thème Réinitialisé', 'Le thème a été réinitialisé aux couleurs par défaut !')
  }
}

// Export theme
const exportTheme = () => {
  const dataStr = JSON.stringify(customColors.value, null, 2)
  const dataBlob = new Blob([dataStr], { type: 'application/json' })
  
  const link = document.createElement('a')
  link.href = URL.createObjectURL(dataBlob)
  link.download = `erebor-theme-${new Date().toISOString().split('T')[0]}.json`
  link.click()
}

// Import theme
const importTheme = () => {
  fileInput.value.click()
}

// Handle file import
const handleFileImport = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const imported = JSON.parse(e.target.result)
      
      // Validate imported data
      if (imported.light && imported.dark) {
        customColors.value = { ...defaultColors, ...imported }
        saveCustomColors()
        
        // Apply the imported theme immediately
        applyCustomTheme()
        
        showNotificationMessage('success', 'Thème Importé', 'Le thème a été importé et appliqué avec succès !')
      } else {
        showNotificationMessage('error', 'Format Invalide', 'Le format du fichier de thème est invalide')
      }
    } catch (error) {
      console.error('Import error:', error)
      showNotificationMessage('error', 'Erreur d\'Import', 'Impossible de traiter le fichier de thème')
    }
  }
  reader.readAsText(file)
  
  // Reset file input
  event.target.value = ''
}

// Convert hex to RGB
const hexToRgb = (hex) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result ? {
    r: parseInt(result[1], 16),
    g: parseInt(result[2], 16),
    b: parseInt(result[3], 16)
  } : null
}

// Watch for mode changes - only update the display, don't apply theme
watch(currentMode, () => {
  // Don't apply theme automatically, just update the display
  // The theme will only be applied when the user clicks "Appliquer le Thème Personnalisé"
})

// Initialize
onMounted(() => {
  loadCustomColors()
  // Don't apply theme automatically on mount
  // Only load the custom colors for display purposes
})
</script>

<style scoped>
/* Custom styles for the theme customizer */
.color-picker {
  @apply flex items-center space-x-3;
}

.color-picker input[type="color"] {
  @apply w-12 h-12 rounded-lg border-2 border-theme-border cursor-pointer;
}

.color-picker input[type="text"] {
  @apply flex-1 px-3 py-2 bg-theme-bg border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-ring focus:border-theme-primary;
}
</style>
