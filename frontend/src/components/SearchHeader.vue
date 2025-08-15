<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl p-8 mb-8">
    <!-- Tab Navigation -->
    <div class="flex justify-center mb-8">
      <div class="flex space-x-2 bg-black p-2 rounded-xl border border-gray-800">
        <button
          @click="$emit('update:activeTab', 'active')"
          :class="{
            'bg-gradient-to-r from-amber-500 to-yellow-600 text-black shadow-lg shadow-amber-500/30': activeTab === 'active',
            'text-gray-400 hover:text-amber-400': activeTab !== 'active',
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
            'bg-gradient-to-r from-amber-500 to-yellow-600 text-black shadow-lg shadow-amber-500/30': activeTab === 'archived',
            'text-gray-400 hover:text-amber-400': activeTab !== 'archived',
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
            <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            class="block w-full pl-12 pr-4 py-4 bg-black border-2 border-gray-800 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-white placeholder-gray-500 transition-all duration-300"
          />
        </div>
      </div>

      <!-- Add Character Button -->
      <button
        @click="$emit('show-modal-member')"
        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-amber-500 to-yellow-600 text-black font-bold rounded-xl hover:from-amber-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-300 shadow-lg hover:shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 transform hover:-translate-y-1"
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
