<template>
  <div class="min-h-screen bg-theme-bg text-theme-text p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-theme-primary mb-4">🎨 Theme Customizer Demo</h1>
        <p class="text-theme-text-muted text-lg">See the power of dynamic theme customization in action!</p>
      </div>

      <!-- Quick Access -->
      <div class="text-center mb-8">
        <RouterLink
          to="/theme-customizer"
          class="inline-flex items-center px-8 py-4 bg-theme-primary hover:bg-theme-primary-hover text-white font-bold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
        >
          🚀 Launch Theme Customizer
        </RouterLink>
      </div>

      <!-- Feature Showcase -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Real-time Preview -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">👁️ Real-time Preview</h3>
          <p class="text-theme-text-muted mb-4">
            See your color changes instantly as you customize. No need to save or refresh!
          </p>
          <div class="space-y-3">
            <div class="p-3 bg-theme-bg-muted rounded-lg border border-theme-border">
              <p class="text-theme-text">Sample content with your theme colors</p>
            </div>
            <button class="w-full bg-theme-primary hover:bg-theme-primary-hover text-white py-2 rounded-lg transition-colors">
              Sample Button
            </button>
          </div>
        </div>

        <!-- Dual Mode Support -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">🌓 Dual Mode Support</h3>
          <p class="text-theme-text-muted mb-4">
            Customize both light and dark modes independently. Perfect for different environments!
          </p>
          <div class="flex space-x-2">
            <div class="flex-1 p-3 bg-white text-black rounded-lg text-center text-sm">
              ☀️ Light
            </div>
            <div class="flex-1 p-3 bg-gray-900 text-white rounded-lg text-center text-sm">
              🌙 Dark
            </div>
          </div>
        </div>
      </div>

      <!-- Color Categories Demo -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg mb-8">
        <h3 class="text-xl font-semibold text-theme-primary mb-4">🎨 Color Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Background Colors -->
          <div>
            <h4 class="font-semibold text-theme-text mb-3">Background Colors</h4>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-bg rounded border border-theme-border"></div>
                <span class="text-sm text-theme-text-muted">Main Background</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-bg-muted rounded border border-theme-border"></div>
                <span class="text-sm text-theme-text-muted">Muted Background</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-card rounded border border-theme-border"></div>
                <span class="text-sm text-theme-text-muted">Card Background</span>
              </div>
            </div>
          </div>

          <!-- Text Colors -->
          <div>
            <h4 class="font-semibold text-theme-text mb-3">Text Colors</h4>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-text rounded"></div>
                <span class="text-sm text-theme-text-muted">Primary Text</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-text-muted rounded"></div>
                <span class="text-sm text-theme-text-muted">Muted Text</span>
              </div>
            </div>
          </div>

          <!-- Accent Colors -->
          <div>
            <h4 class="font-semibold text-theme-text mb-3">Accent Colors</h4>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-primary rounded"></div>
                <span class="text-sm text-theme-text-muted">Primary</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-success rounded"></div>
                <span class="text-sm text-theme-text-muted">Success</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-theme-error rounded"></div>
                <span class="text-sm text-theme-text-muted">Error</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Theme Management Features -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Export/Import -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">📤 Export & Import</h3>
          <p class="text-theme-text-muted mb-4">
            Share your themes with others or backup your creations. Full theme portability!
          </p>
          <div class="space-y-2 text-sm text-theme-text-muted">
            <p>• Export themes as JSON files</p>
            <p>• Import themes from other users</p>
            <p>• Backup your custom themes</p>
            <p>• Share themes across devices</p>
          </div>
        </div>

        <!-- Persistence -->
        <div class="bg-theme-card rounded-lg p-6 shadow-lg">
          <h3 class="text-xl font-semibold text-theme-primary mb-4">💾 Auto-Save</h3>
          <p class="text-theme-text-muted mb-4">
            Your themes are automatically saved and restored. Never lose your customizations!
          </p>
          <div class="space-y-2 text-sm text-theme-text-muted">
            <p>• Automatic localStorage saving</p>
            <p>• Persistent across sessions</p>
            <p>• Instant theme restoration</p>
            <p>• No manual save required</p>
          </div>
        </div>
      </div>

      <!-- Use Cases -->
      <div class="bg-theme-card rounded-lg p-6 shadow-lg mb-8">
        <h3 class="text-xl font-semibold text-theme-primary mb-4">🎯 Perfect For</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold text-theme-text mb-3">Personal Use</h4>
            <ul class="space-y-2 text-sm text-theme-text-muted">
              <li>• Match your personal brand colors</li>
              <li>• Create comfortable reading themes</li>
              <li>• Seasonal color schemes</li>
              <li>• Mood-based customization</li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold text-theme-text mb-3">Professional Use</h4>
            <ul class="space-y-2 text-sm text-theme-text-muted">
              <li>• Company brand consistency</li>
              <li>• Team theme sharing</li>
              <li>• Client presentation themes</li>
              <li>• Accessibility compliance</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="text-center bg-gradient-to-r from-theme-primary to-theme-primary-hover rounded-lg p-8 text-white">
        <h3 class="text-2xl font-bold mb-4">Ready to Create Your Perfect Theme?</h3>
        <p class="text-lg mb-6 opacity-90">
          Join thousands of users who have already personalized their experience
        </p>
        <RouterLink
          to="/theme-customizer"
          class="inline-flex items-center px-8 py-4 bg-white text-theme-primary font-bold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg"
        >
          🎨 Start Customizing Now
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
/* Custom styles for the demo page */
.gradient-bg {
  background: linear-gradient(135deg, var(--primary), var(--primary-hover));
}
</style>
