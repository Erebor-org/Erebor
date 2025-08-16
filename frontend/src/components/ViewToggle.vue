<template>
  <div class="flex items-center space-x-2 bg-gray-800 rounded-lg p-1 border border-gray-700">
    <button
      @click="$emit('update:view-mode', 'cards')"
      :class="[
        'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
        viewMode === 'cards'
          ? 'bg-amber-500 text-black shadow-lg'
          : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
      ]"
      title="Vue en fiches"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
      </svg>
      <span>Fiches</span>
    </button>
    
    <button
      @click="$emit('update:view-mode', 'list')"
      :class="[
        'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
        viewMode === 'list'
          ? 'bg-amber-500 text-black shadow-lg'
          : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
      ]"
      title="Vue en liste"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
      </svg>
      <span>Liste</span>
    </button>
  </div>
</template>

<script>
export default {
  name: 'ViewToggle',
  props: {
    viewMode: {
      type: String,
      required: true,
      validator: (value) => ['cards', 'list'].includes(value),
    },
  },
  emits: ['update:view-mode'],
};
</script>
