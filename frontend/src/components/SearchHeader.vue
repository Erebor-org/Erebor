<template>
  <div class="glass-card rounded-2xl p-6 mb-6">
    <!-- Tab Navigation -->
    <div class="flex justify-center mb-6">
      <div class="flex space-x-1 bg-theme-bg-muted/60 p-1 rounded-xl border border-theme-border">
        <button
          @click="$emit('update:activeTab', 'active')"
          :class="{
            'bg-theme-primary text-white shadow': activeTab === 'active',
            'text-theme-text-muted hover:text-theme-primary': activeTab !== 'active',
          }"
          class="px-6 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Membres Actifs</span>
        </button>
        <button
          @click="$emit('update:activeTab', 'archived')"
          :class="{
            'bg-theme-primary text-white shadow': activeTab === 'archived',
            'text-theme-text-muted hover:text-theme-primary': activeTab !== 'archived',
          }"
          class="px-6 py-2.5 rounded-lg font-semibold text-sm transition-all duration-300 flex items-center space-x-2"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
          </svg>
          <span>Membres Archivés</span>
        </button>
      </div>
    </div>

    <!-- Search and Actions Row -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <!-- Search Input -->
      <div class="flex-1 max-w-2xl">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            :value="currentSearchQuery"
            @input="$emit('update:currentSearchQuery', $event.target.value)"
            :placeholder="
              activeTab === 'active'
                ? 'Rechercher par nom, rang, recruteur...'
                : 'Rechercher des membres archivés...'
            "
            class="block w-full pl-11 pr-4 py-3 bg-theme-bg border border-theme-border rounded-xl focus:ring-2 focus:ring-theme-primary/40 focus:border-theme-primary text-theme-text placeholder-theme-text-muted transition-all duration-300"
          />
        </div>
      </div>

      <!-- Add Character Button -->
      <button
        @click="$emit('show-modal-member')"
        class="inline-flex items-center justify-center px-6 py-3 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg transition-all duration-300 shadow-lg hover:shadow-theme-primary/30 whitespace-nowrap"
      >
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Ajouter un personnage
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchHeader',
  props: {
    activeTab: {
      type: String,
      required: true,
      validator: (value) => ['active', 'archived'].includes(value),
    },
    currentSearchQuery: {
      type: String,
      required: true,
    },
  },
  emits: ['update:activeTab', 'update:currentSearchQuery', 'show-modal-member'],
};
</script>
