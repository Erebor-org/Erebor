<template>
  <div class="flex items-center space-x-2 bg-theme-bg-muted rounded-lg p-1 border border-theme-bg-muted">
    <button
      @click="$emit('update:view-mode', 'cards')"
      :class="[
        'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
        viewMode === 'cards'
          ? 'bg-theme-primary text-white shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-bg-muted'
      ]"
      title="Vue en fiche perso"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 100 8 4 4 0 000-8zM4 20a8 8 0 0116 0" />
      </svg>
      <span>Fiche perso</span>
    </button>
    
    <button
      @click="$emit('update:view-mode', 'list')"
      :class="[
        'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center space-x-2',
        viewMode === 'list'
          ? 'bg-theme-primary text-white shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-bg-muted'
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
