<template>
  <div class="min-h-screen bg-theme-bg text-theme-text p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-theme-primary mb-4">Démonstration du Système de Thèmes</h1>
        <p class="text-theme-text-muted text-lg">Testez le système de thèmes light/dark avec tous les composants</p>
      </div>

      <!-- Theme Toggle Section -->
      <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-theme-text mb-4">Contrôle du Thème</h2>
        <div class="flex items-center justify-center space-x-4">
          <ThemeToggle />
          <div class="text-theme-text-muted">
            Thème actuel: <span class="font-semibold text-theme-primary">{{ themeStore.currentTheme }}</span>
          </div>
        </div>
      </div>

      <!-- Color Palette Display -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Background Colors -->
        <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-theme-text mb-4">Couleurs de Fond</h3>
          <div class="space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-bg border border-theme-border rounded"></div>
              <span class="text-theme-text-muted">--bg</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-bg-muted border border-theme-border rounded"></div>
              <span class="text-theme-text-muted">--bg-muted</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-card border border-theme-border rounded"></div>
              <span class="text-theme-text-muted">--card</span>
            </div>
          </div>
        </div>

        <!-- Text Colors -->
        <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-theme-text mb-4">Couleurs de Texte</h3>
          <div class="space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-text rounded"></div>
              <span class="text-theme-text">--text</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-text-muted rounded"></div>
              <span class="text-theme-text-muted">--text-muted</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-primary rounded"></div>
              <span class="text-theme-primary">--primary</span>
            </div>
          </div>
        </div>

        <!-- Accent Colors -->
        <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-theme-text mb-4">Couleurs d'Accent</h3>
          <div class="space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-success rounded"></div>
              <span class="text-theme-success">--success</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-warning rounded"></div>
              <span class="text-theme-warning">--warning</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-error rounded"></div>
              <span class="text-theme-error">--error</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Component Examples -->
      <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-theme-text mb-6">Exemples de Composants</h2>
        
        <!-- Buttons -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-theme-text mb-4">Boutons</h3>
          <div class="flex flex-wrap gap-4">
            <button class="px-6 py-3 bg-theme-primary text-theme-bg font-semibold rounded-lg hover:bg-theme-primary-hover transition-colors">
              Bouton Principal
            </button>
            <button class="px-6 py-3 bg-theme-success text-theme-bg font-semibold rounded-lg hover:opacity-90 transition-colors">
              Succès
            </button>
            <button class="px-6 py-3 bg-theme-warning text-theme-bg font-semibold rounded-lg hover:opacity-90 transition-colors">
              Attention
            </button>
            <button class="px-6 py-3 bg-theme-error text-theme-bg font-semibold rounded-lg hover:opacity-90 transition-colors">
              Erreur
            </button>
            <button class="px-6 py-3 bg-theme-bg-muted text-theme-text font-semibold rounded-lg hover:bg-theme-border transition-colors">
              Secondaire
            </button>
          </div>
        </div>

        <!-- Form Elements -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-theme-text mb-4">Éléments de Formulaire</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-theme-text font-medium mb-2">Champ de texte</label>
              <input 
                type="text" 
                placeholder="Placeholder..." 
                class="w-full px-4 py-2 bg-theme-bg border border-theme-border rounded-lg focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-colors"
              />
            </div>
            <div>
              <label class="block text-theme-text font-medium mb-2">Sélection</label>
              <select class="w-full px-4 py-2 bg-theme-bg border border-theme-border rounded-lg focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-colors">
                <option>Option 1</option>
                <option>Option 2</option>
                <option>Option 3</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Cards -->
        <div>
          <h3 class="text-lg font-semibold text-theme-text mb-4">Cartes</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-theme-bg-muted border border-theme-border rounded-xl p-6">
              <h4 class="text-theme-primary font-semibold mb-2">Carte 1</h4>
              <p class="text-theme-text-muted">Contenu de la carte avec du texte d'exemple.</p>
            </div>
            <div class="bg-theme-bg-muted border border-theme-border rounded-xl p-6">
              <h4 class="text-theme-primary font-semibold mb-2">Carte 2</h4>
              <p class="text-theme-text-muted">Autre carte avec du contenu différent.</p>
            </div>
            <div class="bg-theme-bg-muted border border-theme-border rounded-xl p-6">
              <h4 class="text-theme-primary font-semibold mb-2">Carte 3</h4>
              <p class="text-theme-text-muted">Dernière carte pour compléter l'exemple.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Theme Info -->
      <div class="bg-theme-card border border-theme-border rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-theme-text mb-4">Informations sur le Thème</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold text-theme-text mb-2">Thème Actuel</h3>
            <p class="text-theme-text-muted">
              <strong>Nom:</strong> {{ themeStore.currentTheme === 'light' ? 'Blanc & Bleu' : 'Noir & Magenta' }}<br>
              <strong>Type:</strong> {{ themeStore.isSystemTheme ? 'Système' : 'Personnalisé' }}<br>
              <strong>Attribut HTML:</strong> data-theme="{{ themeStore.currentTheme }}"
            </p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-theme-text mb-2">Actions</h3>
            <div class="space-y-2">
              <button 
                @click="themeStore.setTheme('light')"
                class="w-full px-4 py-2 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border transition-colors"
              >
                Forcer Light
              </button>
              <button 
                @click="themeStore.setTheme('dark')"
                class="w-full px-4 py-2 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border transition-colors"
              >
                Forcer Dark
              </button>
              <button 
                @click="themeStore.resetToSystemTheme"
                class="w-full px-4 py-2 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border transition-colors"
              >
                Réinitialiser au Système
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useThemeStore } from '@/stores/themeStore'
import ThemeToggle from '@/components/ThemeToggle.vue'

const themeStore = useThemeStore()
</script>

<style scoped>
/* Styles spécifiques si nécessaire */
</style>
