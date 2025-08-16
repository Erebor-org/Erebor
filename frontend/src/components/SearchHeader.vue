<template>
  <div class="bg-theme-card border border-theme-border rounded-2xl shadow-2xl p-8 mb-8">
    <!-- Tab Navigation -->
    <div class="flex justify-center mb-8">
      <div class="flex space-x-2 bg-theme-bg p-2 rounded-xl border border-theme-border">
        <button
          @click="$emit('update:activeTab', 'active')"
          :class="{
            'bg-gradient-to-r from-theme-primary to-theme-primary-hover text-theme-bg shadow-lg shadow-theme-primary/30': activeTab === 'active',
            'text-theme-text-muted hover:text-theme-primary': activeTab !== 'active',
          }"
          class="px-8 py-4 rounded-xl font-bold transition-all duration-300 flex items-center space-x-3"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Membres Actifs</span>
        </button>
        <button
          @click="$emit('update:activeTab', 'archived')"
          :class="{
            'bg-gradient-to-r from-theme-primary to-theme-primary-hover text-theme-bg shadow-lg shadow-theme-primary/30': activeTab === 'archived',
            'text-theme-text-muted hover:text-theme-primary': activeTab !== 'archived',
          }"
          class="px-8 py-4 rounded-xl font-bold transition-all duration-300 flex items-center space-x-3"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
          </svg>
          <span>Membres Archivés</span>
        </button>
      </div>
    </div>

    <!-- Search and Actions Row -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between space-y-6 lg:space-y-0 lg:space-x-8">
      <!-- Search Input -->
      <div class="flex-1 max-w-2xl">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-6 w-6 text-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            class="block w-full pl-12 pr-4 py-4 bg-theme-bg border-2 border-theme-border rounded-xl focus:ring-2 focus:ring-theme-primary focus:border-theme-primary text-theme-text placeholder-gray-500 transition-all duration-300"
          />
        </div>
      </div>

      <!-- Add Character Button -->
      <button
        @click="$emit('show-modal-member')"
        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-theme-primary to-theme-primary-hover text-theme-bg font-bold rounded-xl hover:from-theme-primary hover:to-theme-primary-hover focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg transition-all duration-300 shadow-lg hover:shadow-xl shadow-theme-primary/30 hover:shadow-theme-primary/50 transform hover:-translate-y-1"
      >
        <svg class="w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
