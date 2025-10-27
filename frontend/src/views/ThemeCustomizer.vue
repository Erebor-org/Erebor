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
        <h1 class="text-4xl font-bold text-theme-primary mb-4">🎨 Personnalisation</h1>
        <p class="text-theme-text-muted text-lg">Gérez vos préférences et personnalisez votre expérience</p>
      </div>

      <!-- Default Preferences Section -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg mb-12 border border-theme-border">
        <div class="mb-4">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">👥 Vue par défaut des membres</h3>
          <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="defaultMemberView" value="cards" v-model="defaultMemberView" @change="saveDefaultMemberView" />
              <span>Carte</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="defaultMemberView" value="list" v-model="defaultMemberView" @change="saveDefaultMemberView" />
              <span>Liste</span>
            </label>
          </div>
          <p class="text-theme-text-muted mt-2 text-sm">Choisissez la vue par défaut pour la gestion des membres.</p>
        </div>
        
        <!-- Character Selection -->
        <div class="mt-8 pt-8 border-t border-theme-border">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">🎭 Personnage associé</h3>
          <p class="text-theme-text-muted mb-4 text-sm">Associez un personnage à votre compte pour personnaliser votre icône de profil.</p>
          
          <!-- Search Input -->
          <div class="mb-4">
            <input
              type="text"
              v-model="characterSearchQuery"
              placeholder="Rechercher un personnage..."
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
            />
          </div>
          
          <!-- Character List -->
          <div class="max-h-64 overflow-y-auto bg-theme-bg-muted border-2 border-theme-border rounded-lg p-3">
            <div
              v-for="character in filteredCharacters"
              :key="character.id"
              @click="selectCharacter(character)"
              class="flex items-center space-x-3 p-3 cursor-pointer hover:bg-theme-card rounded-lg transition-colors duration-200 group mb-2"
              :class="{ 'bg-theme-primary/10 border border-theme-primary': selectedCharacter?.id === character.id }"
            >
              <img :src="getClassIcon(character.class)" :alt="`Classe ${character.class}`" class="w-10 h-10 rounded-lg border-2 border-theme-border group-hover:border-theme-primary transition-colors duration-200" />
              <div class="flex-1">
                <p class="text-base font-medium text-theme-text group-hover:text-theme-primary transition-colors duration-200">{{ character.pseudo }}</p>
                <p class="text-sm text-theme-text-muted">{{ character.ankamaPseudo }}</p>
              </div>
              <div v-if="selectedCharacter?.id === character.id" class="w-6 h-6 bg-theme-primary rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Clear Selection Button -->
          <div v-if="selectedCharacter" class="mt-4 flex justify-end">
            <button
              @click="clearCharacter"
              class="px-4 py-2 bg-theme-error/10 hover:bg-theme-error/20 text-theme-error font-medium rounded-lg transition-all duration-200"
            >
              Retirer l'association
            </button>
          </div>
        </div>
      </div>
      <!-- Theme Information -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg mb-10">
        <h3 class="text-2xl font-bold text-theme-primary mb-6 flex items-center gap-2">ℹ️ <span>Informations sur le Thème</span></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span class="text-lg">🎨</span>
              <span class="font-semibold text-theme-text text-lg">Mode :</span>
              <span :class="currentMode === 'light' ? 'bg-theme-primary/10 text-theme-primary' : 'bg-theme-warning/10 text-theme-warning'" class="px-3 py-1 rounded-full font-bold text-base ml-2">{{ currentMode === 'light' ? 'Clair' : 'Sombre' }}</span>
            </div>
            <div class="flex items-center gap-3 mb-2">
              <span class="text-lg">🛠️</span>
              <span class="font-semibold text-theme-text text-lg">Personnalisé :</span>
              <span :class="isCustomTheme ? 'bg-theme-success/10 text-theme-success' : 'bg-theme-error/10 text-theme-error'" class="px-3 py-1 rounded-full font-bold text-base ml-2">{{ isCustomTheme ? 'Oui' : 'Non' }}</span>
            </div>
            <p class="text-theme-text-muted text-sm ml-8">Indique si vous avez modifié les couleurs par défaut.</p>
          </div>
          <div>
          </div>
        </div>
      </div>

      <!-- Theme Customization Section -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg border border-theme-border">
        <h2 class="text-2xl font-semibold text-theme-primary mb-4">🎨 Personnalisation du thème</h2>
        <!-- Theme Mode Toggle (compact pill style) -->
        <div class="flex justify-center mb-8">
          <div class="inline-flex rounded-full bg-theme-bg-muted border border-theme-border p-1">
            <button
              @click="currentMode = 'light'"
              :class="[
                'px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200',
                currentMode === 'light'
                  ? 'bg-theme-primary text-white shadow'
                  : 'bg-transparent text-theme-text-muted hover:bg-theme-border'
              ]"
              title="Mode clair"
            >
              ☀️
            </button>
            <button
              @click="currentMode = 'dark'"
              :class="[
                'px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200',
                currentMode === 'dark'
                  ? 'bg-theme-primary text-white shadow'
                  : 'bg-transparent text-theme-text-muted hover:bg-theme-border'
              ]"
              title="Mode sombre"
            >
              🌙
            </button>
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
        </div>
        <!-- Action Buttons moved here -->
      <div class="flex justify-center mb-8">
        <div class="inline-flex gap-3">
          <button
            @click="applyCustomTheme"
            class="px-4 py-2 rounded-xl text-base font-medium transition-all duration-200 bg-theme-success text-white hover:bg-theme-success/80 focus:outline-none flex items-center gap-2"
            title="Sauvegarder le thème"
          >
            💾 <span>Sauver</span>
          </button>
          <button
            @click="previewCustomTheme"
            class="px-4 py-2 rounded-xl text-base font-medium transition-all duration-200 bg-theme-primary text-white hover:bg-theme-primary-hover focus:outline-none flex items-center gap-2"
            title="Prévisualiser le thème"
          >
            👁️ <span>Voir</span>
          </button>
          <button
            @click="resetToDefault"
            class="px-4 py-2 rounded-xl text-base font-medium transition-all duration-200 bg-theme-warning text-white hover:bg-theme-warning/80 focus:outline-none flex items-center gap-2"
            title="Réinitialiser aux couleurs initiales"
          >
            🔄 <span>Reset</span>
          </button>
          <button
            @click="exportTheme"
            class="px-4 py-2 rounded-xl text-base font-medium transition-all duration-200 bg-theme-primary text-white hover:bg-theme-primary-hover focus:outline-none flex items-center gap-2"
            title="Exporter le thème"
          >
            📤 <span>Exporter</span>
          </button>
          <button
            @click="importTheme"
            class="px-4 py-2 rounded-xl text-base font-medium transition-all duration-200 bg-theme-link text-white hover:bg-theme-primary-hover focus:outline-none flex items-center gap-2"
            title="Importer un thème"
          >
            📥 <span>Importer</span>
          </button>
        </div>
      </div>
      </div>

      <!-- Add preview cancel banner -->
      <div v-if="isPreviewing" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-50 bg-theme-warning text-theme-text px-6 py-3 rounded-xl shadow-lg flex items-center gap-4 border-2 border-theme-warning animate-fade-in">
        <span>Vous êtes en mode prévisualisation du thème.</span>
        <button @click="applyCustomTheme" class="bg-theme-success text-white px-4 py-2 rounded-lg font-medium hover:bg-theme-success/80 transition-all">Sauvegarder</button>
        <button @click="cancelPreview" class="bg-theme-error text-white px-4 py-2 rounded-lg font-medium hover:bg-theme-error/80 transition-all">Annuler la prévisualisation</button>
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
import { useAuthStore } from '@/stores/authStore'
import axios from 'axios'
import ColorPicker from '@/components/ColorPicker.vue'
import Notification from '@/components/Notification.vue'

const API_URL = import.meta.env.VITE_API_URL
const themeStore = useThemeStore()
const authStore = useAuthStore()
const currentMode = ref('light')
const fileInput = ref(null)

// Character selector
const characters = ref([])
const characterSearchQuery = ref('')
const selectedCharacter = ref(null)
const isLoadingCharacters = ref(false)

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

// Default member view setting
const defaultMemberView = ref(localStorage.getItem('erebor-default-member-view') || 'cards')
const saveDefaultMemberView = () => {
  localStorage.setItem('erebor-default-member-view', defaultMemberView.value)
}

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

const isPreviewing = ref(false)
let previousColors = null

const previewCustomTheme = () => {
  // Sauvegarde l'état du localStorage AVANT preview
  previousColors = localStorage.getItem('erebor-custom-colors')
    ? JSON.parse(localStorage.getItem('erebor-custom-colors'))
    : null
  // Applique le thème custom en CSS variables (sans sauvegarder)
  const root = document.documentElement
  const colors = customColors.value[currentMode.value]
  Object.entries(colors).forEach(([key, value]) => {
    root.style.setProperty(`--${key}`, value)
    if (value.startsWith('#')) {
      const rgb = hexToRgb(value)
      if (rgb) {
        root.style.setProperty(`--${key}-rgb`, `${rgb.r}, ${rgb.g}, ${rgb.b}`)
      }
    }
  })
  isPreviewing.value = true
  showNotificationMessage('info', 'Prévisualisation', 'Votre thème est en mode prévisualisation. Cliquez sur "Sauvegarder" pour l\'appliquer ou "Annuler" pour revenir en arrière.')
}

const cancelPreview = () => {
  // Restaure le localStorage et recharge l'UI
  if (previousColors) {
    localStorage.setItem('erebor-custom-colors', JSON.stringify(previousColors))
  } else {
    localStorage.removeItem('erebor-custom-colors')
  }
  themeStore.applyCustomColors()
  loadCustomColors()
  isPreviewing.value = false
  previousColors = null
  showNotification.value = false
}

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
  isPreviewing.value = false
  previousColors = null
  // Update the snapshot to the new saved state
  // originalCustomColors = JSON.parse(JSON.stringify(customColors.value)) // This line is removed
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

// Import class icons
const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
const classes = {
  sram: images['/src/assets/icon_classe/sram.avif'].default,
  forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
  cra: images['/src/assets/icon_classe/cra.avif'].default,
  ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
  eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
  enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
  feca: images['/src/assets/icon_classe/feca.avif'].default,
  eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
  iop: images['/src/assets/icon_classe/iop.avif'].default,
  osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
  pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
  roublard: images['/src/assets/icon_classe/roublard.avif'].default,
  sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
  sadida: images['/src/assets/icon_classe/sadida.avif'].default,
  steamer: images['/src/assets/icon_classe/steamer.avif'].default,
  xelor: images['/src/assets/icon_classe/xelor.avif'].default,
  zobal: images['/src/assets/icon_classe/zobal.avif'].default,
  huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
  ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
};

// Get class icon
const getClassIcon = (className) => {
  return classes[className] || classes.cra;
}

// Filter characters based on search query
const filteredCharacters = computed(() => {
  if (!characterSearchQuery.value) {
    return characters.value;
  }
  const query = characterSearchQuery.value.toLowerCase();
  return characters.value.filter(char => 
    char.pseudo.toLowerCase().includes(query) || 
    char.ankamaPseudo.toLowerCase().includes(query)
  );
});

// Fetch characters
const fetchCharacters = async () => {
  isLoadingCharacters.value = true;
  try {
    const response = await axios.get(`${API_URL}/characters/`);
    characters.value = response.data;
  } catch (error) {
    console.error('Error fetching characters:', error);
    showNotificationMessage('error', 'Erreur', 'Impossible de charger les personnages');
  } finally {
    isLoadingCharacters.value = false;
  }
};

// Select character
const selectCharacter = async (character) => {
  selectedCharacter.value = character;
  try {
    await authStore.updateCharacter(character.id);
    showNotificationMessage('success', 'Personnage associé', `Le personnage ${character.pseudo} a été associé à votre compte.`);
  } catch (error) {
    console.error('Error updating character:', error);
    showNotificationMessage('error', 'Erreur', 'Impossible d\'associer le personnage');
  }
};

// Clear character
const clearCharacter = async () => {
  selectedCharacter.value = null;
  try {
    await authStore.updateCharacter(null);
    showNotificationMessage('success', 'Association retirée', 'L\'association du personnage a été retirée.');
  } catch (error) {
    console.error('Error clearing character:', error);
    showNotificationMessage('error', 'Erreur', 'Impossible de retirer l\'association');
  }
};

// Initialize
onMounted(() => {
  loadCustomColors()
  // Don't apply theme automatically on mount
  // Only load the custom colors for display purposes
  
  // Load characters and set selected character
  fetchCharacters().then(() => {
    if (authStore.user?.character) {
      selectedCharacter.value = authStore.user.character;
    }
  });
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
