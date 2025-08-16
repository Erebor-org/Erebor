<template>
  <div class="p-6 bg-theme-bg text-theme-text">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-center">🎨 Démonstration des Notifications</h1>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Notifications -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold mb-4">📱 Notifications de Base</h2>
          
          <div class="space-y-3">
            <button
              @click="showBasicNotification('success', 'Succès', 'Opération réussie !')"
              class="w-full px-4 py-3 bg-theme-success/10 hover:bg-theme-success/20 text-theme-success font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-success/20"
            >
              ✅ Notification de Succès
            </button>
            
            <button
              @click="showBasicNotification('error', 'Erreur', 'Une erreur est survenue')"
              class="w-full px-4 py-3 bg-theme-error/10 hover:bg-theme-error/20 text-theme-error font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-error/20"
            >
              ❌ Notification d'Erreur
            </button>
            
            <button
              @click="showBasicNotification('warning', 'Attention', 'Attention, action requise')"
              class="w-full px-4 py-3 bg-theme-warning/10 hover:bg-theme-warning/20 text-theme-warning font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-warning/20"
            >
              ⚠️ Notification d'Avertissement
            </button>
            
            <button
              @click="showBasicNotification('info', 'Information', 'Information importante')"
              class="w-full px-4 py-3 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-primary/20"
            >
              ℹ️ Notification d'Information
            </button>
          </div>
        </div>
        
        <!-- CRUD Notifications -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold mb-4">🔄 Notifications CRUD</h2>
          
          <div class="space-y-3">
            <button
              @click="showCrudNotification('create', 'Création', 'Nouvel élément créé avec succès')"
              class="w-full px-4 py-3 bg-theme-success/10 hover:bg-theme-success/20 text-theme-success font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-success/20"
            >
              ➕ Création
            </button>
            
            <button
              @click="showCrudNotification('update', 'Modification', 'Élément modifié avec succès')"
              class="w-full px-4 py-3 bg-theme-warning/10 hover:bg-theme-warning/20 text-theme-warning font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-warning/20"
            >
              ✏️ Modification
            </button>
            
            <button
              @click="showCrudNotification('delete', 'Suppression', 'Élément supprimé avec succès')"
              class="w-full px-4 py-3 bg-theme-error/10 hover:bg-theme-error/20 text-theme-error font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-error/20"
            >
              🗑️ Suppression
            </button>
            
            <button
              @click="showCrudNotification('read', 'Lecture', 'Informations récupérées')"
              class="w-full px-4 py-3 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-primary/20"
            >
              👁️ Lecture
            </button>
          </div>
        </div>
        
        <!-- Advanced Notifications -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold mb-4">🚀 Notifications Avancées</h2>
          
          <div class="space-y-3">
            <button
              @click="showAdvancedNotification('error', 'Erreur Réseau', 'Impossible de se connecter au serveur', true)"
              class="w-full px-4 py-3 bg-theme-error/10 hover:bg-theme-error/20 text-theme-error font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-error/20"
            >
              🔄 Erreur avec Bouton Réessayer
            </button>
            
            <button
              @click="showAdvancedNotification('warning', 'Mise à Jour', 'Une nouvelle version est disponible', true)"
              class="w-full px-4 py-3 bg-theme-warning/10 hover:bg-theme-warning/20 text-theme-warning font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-warning/20"
            >
              ⚠️ Avertissement avec Bouton Ignorer
            </button>
            
            <button
              @click="showAdvancedNotification('info', 'Nouveau Message', 'Vous avez reçu un nouveau message', true)"
              class="w-full px-4 py-3 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-primary/20"
            >
              📧 Message avec Bouton Voir
            </button>
          </div>
        </div>
        
        <!-- Custom Duration -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold mb-4">⏱️ Durées Personnalisées</h2>
          
          <div class="space-y-3">
            <button
              @click="showCustomDurationNotification('success', 'Rapide', 'Notification de 2 secondes', 2000)"
              class="w-full px-4 py-3 bg-theme-success/10 hover:bg-theme-success/20 text-theme-success font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-success/20"
            >
              ⚡ 2 secondes
            </button>
            
            <button
              @click="showCustomDurationNotification('info', 'Lente', 'Notification de 10 secondes', 10000)"
              class="w-full px-4 py-3 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-primary/20"
            >
              🐌 10 secondes
            </button>
            
            <button
              @click="showCustomDurationNotification('warning', 'Persistante', 'Notification qui ne disparaît pas', 0)"
              class="w-full px-4 py-3 bg-theme-warning/10 hover:bg-theme-warning/20 text-theme-warning font-medium rounded-lg transition-all duration-200 hover:scale-105 border border-theme-warning/20"
            >
              🔒 Persistante
            </button>
          </div>
        </div>
      </div>
      
      <!-- Theme Toggle -->
      <div class="mt-8 text-center">
        <button
          @click="toggleTheme"
          class="px-6 py-3 bg-theme-primary hover:bg-theme-primary-hover text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 shadow-lg"
        >
          {{ currentTheme === 'light' ? '🌙 Mode Sombre' : '☀️ Mode Clair' }}
        </button>
      </div>
    </div>
    
    <!-- Notifications -->
    <UnifiedNotification
      v-if="showBasic"
      :type="basicType"
      :title="basicTitle"
      :message="basicMessage"
      @close="showBasic = false"
    />
    
    <CrudNotification
      v-if="showCrud"
      :action="crudAction"
      :title="crudTitle"
      :message="crudMessage"
      @close="showCrud = false"
      @undo="handleUndo"
      @retry="handleRetry"
      @view="handleView"
    />
    
    <AdvancedNotification
      v-if="showAdvanced"
      :type="advancedType"
      :title="advancedTitle"
      :message="advancedMessage"
      :show-actions="true"
      @close="showAdvanced = false"
      @retry="handleRetry"
      @dismiss="handleDismiss"
    />
    
    <UnifiedNotification
      v-if="showCustom"
      :type="customType"
      :title="customTitle"
      :message="customMessage"
      :duration="customDuration"
      @close="showCustom = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import UnifiedNotification from './UnifiedNotification.vue'
import CrudNotification from './CrudNotification.vue'
import AdvancedNotification from './AdvancedNotification.vue'

// Basic notification state
const showBasic = ref(false)
const basicType = ref('success')
const basicTitle = ref('')
const basicMessage = ref('')

// CRUD notification state
const showCrud = ref(false)
const crudAction = ref('success')
const crudTitle = ref('')
const crudMessage = ref('')

// Advanced notification state
const showAdvanced = ref(false)
const advancedType = ref('info')
const advancedTitle = ref('')
const advancedMessage = ref('')

// Custom duration notification state
const showCustom = ref(false)
const customType = ref('info')
const customTitle = ref('')
const customMessage = ref('')
const customDuration = ref(5000)

// Theme state
const currentTheme = ref('light')

// Methods
const showBasicNotification = (type, title, message) => {
  basicType.value = type
  basicTitle.value = title
  basicMessage.value = message
  showBasic.value = true
}

const showCrudNotification = (action, title, message) => {
  crudAction.value = action
  crudTitle.value = title
  crudMessage.value = message
  showCrud.value = true
}

const showAdvancedNotification = (type, title, message, showActions = false) => {
  advancedType.value = type
  advancedTitle.value = title
  advancedMessage.value = message
  showAdvanced.value = true
}

const showCustomDurationNotification = (type, title, message, duration) => {
  customType.value = type
  customTitle.value = title
  customMessage.value = message
  customDuration.value = duration
  showCustom.value = true
}

const toggleTheme = () => {
  currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light'
  document.documentElement.setAttribute('data-theme', currentTheme.value)
}

const handleUndo = () => {
  console.log('Action annulée')
}

const handleRetry = () => {
  console.log('Nouvelle tentative')
}

const handleView = () => {
  console.log('Voir les détails')
}

const handleDismiss = () => {
  console.log('Avertissement ignoré')
}

// Initialize theme
onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  currentTheme.value = savedTheme
  document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>
