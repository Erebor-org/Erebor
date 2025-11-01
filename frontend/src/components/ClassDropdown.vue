<template>
  <div class="relative inline-block">
    <!-- Class Icon Button -->
    <button 
      @click="toggleDropdown"
      class="group relative focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg rounded-xl transition-all duration-300"
      :title="`Changer la classe (actuellement: ${className})`"
    >
      <img
        :src="classes[className]"
        :alt="`Classe ${className}`"
        class="w-20 h-20 rounded-xl border-2 border-theme-bg-muted group-hover:border-theme-primary transition-all duration-300 shadow-lg group-hover:shadow-theme-primary/25"
      />
      <!-- Edit Indicator -->
      <div class="absolute -top-3 -right-3 w-7 h-7 bg-gradient-to-r from-theme-primary to-theme-primary-hover rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg">
        <svg class="w-4 h-4 text-theme-bg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
      </div>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="isVisible"
      class="absolute top-24 left-0 z-50 bg-theme-card border border-theme-bg-muted rounded-2xl shadow-2xl p-6 w-[500px] max-h-96 overflow-y-auto backdrop-blur-sm"
    >
      <!-- Header -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-theme-bg-muted">
        <h3 class="text-lg font-bold text-theme-primary">Choisir une classe</h3>
        <button
          @click="closeDropdown"
          class="text-theme-text-muted hover:text-theme-primary transition-colors duration-200"
        >
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Class Grid -->
      <div class="grid grid-cols-5 gap-3">
        <button
          v-for="(icon, classKey) in classes"
          :key="classKey"
          @click="selectClass(classKey)"
          class="group flex flex-col items-center p-3 rounded-xl hover:bg-theme-primary/10 border-2 border-transparent hover:border-theme-primary/30 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg"
          :class="{ 'bg-theme-primary/20 border-theme-primary/50': classKey === className }"
        >
          <img 
            :src="icon" 
            :alt="classKey" 
            class="w-16 h-16 rounded-xl mb-2 group-hover:scale-110 transition-transform duration-300 object-contain" 
          />
          <span class="text-xs font-medium text-theme-text text-center capitalize group-hover:text-theme-primary transition-colors duration-200">{{ classKey }}</span>
        </button>
      </div>

      <!-- Footer -->
      <div class="mt-6 pt-4 border-t border-theme-bg-muted text-center">
        <p class="text-xs text-theme-text-muted">Cliquez sur une classe pour l'appliquer</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClassDropdown',
  props: {
    className: {
      type: String,
      required: true,
    },
    classes: {
      type: Object,
      required: true,
    },
    entityId: {
      type: [String, Number],
      required: true,
    },
    entityType: {
      type: String,
      required: true,
      validator: (value) => ['character', 'mule'].includes(value),
    },
  },
  emits: ['update-class'],
  data() {
    return {
      isVisible: false,
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    toggleDropdown() {
      this.isVisible = !this.isVisible;
    },
    closeDropdown() {
      this.isVisible = false;
    },
    selectClass(selectedClass) {
      if (selectedClass !== this.className) {
        this.$emit('update-class', this.entityId, selectedClass);
      }
      this.closeDropdown();
    },
    handleClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.closeDropdown();
      }
    },
  },
};
</script>
