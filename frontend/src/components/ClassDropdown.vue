<template>
  <div class="relative inline-block">
    <!-- Class Icon Button -->
    <button
      ref="trigger"
      @click="toggleDropdown"
      class="group relative focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg rounded-full transition-all duration-300 hover:scale-105"
      :title="`Changer la classe (actuellement: ${className})`"
    >
      <span class="portrait-ring shadow-lg group-hover:shadow-theme-primary/25" :class="size === 'sm' ? 'w-10 h-10' : 'w-20 h-20'">
        <img
          :src="classes[className]"
          :alt="`Classe ${className}`"
        />
      </span>
      <!-- Edit Indicator -->
      <div
        class="absolute bg-gradient-to-r from-theme-primary to-theme-primary-hover rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg border-2 border-theme-card"
        :class="size === 'sm' ? '-top-0.5 -right-0.5 w-4 h-4' : '-top-1 -right-1 w-7 h-7'"
      >
        <svg class="text-white" :class="size === 'sm' ? 'w-2 h-2' : 'w-3.5 h-3.5'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
      </div>
    </button>

    <!-- Dropdown Menu: teleported to <body> so it isn't trapped in the parent card's stacking
         context (glass-card uses backdrop-filter, which made neighbouring cards paint over it) -->
    <Teleport to="body">
    <div
      v-if="isVisible"
      ref="panel"
      class="glass-modal fixed z-[60] rounded-2xl shadow-2xl p-6 max-h-96 overflow-y-auto backdrop-blur-md"
      :style="panelStyle"
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
    </Teleport>
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
    size: {
      type: String,
      default: 'lg', // 'lg' | 'sm'
      validator: (value) => ['lg', 'sm'].includes(value),
    },
  },
  emits: ['update-class'],
  data() {
    return {
      isVisible: false,
      panelStyle: {},
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
    // Capture phase: the page scrolls inside the RouterView container, not the window
    window.addEventListener('scroll', this.updatePosition, true);
    window.addEventListener('resize', this.updatePosition);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
    window.removeEventListener('scroll', this.updatePosition, true);
    window.removeEventListener('resize', this.updatePosition);
  },
  methods: {
    toggleDropdown() {
      this.isVisible = !this.isVisible;
      if (this.isVisible) this.updatePosition();
    },
    closeDropdown() {
      this.isVisible = false;
    },
    // Place the panel under the portrait, kept inside the viewport (opens upwards if needed)
    updatePosition() {
      if (!this.isVisible || !this.$refs.trigger) return;
      const margin = 8;
      const gap = 8;
      const panelHeight = 384; // max-h-96
      const width = Math.min(500, window.innerWidth - margin * 2);
      const rect = this.$refs.trigger.getBoundingClientRect();
      const left = Math.min(Math.max(margin, rect.left), window.innerWidth - width - margin);
      const spaceBelow = window.innerHeight - rect.bottom - gap - margin;
      const style = { left: `${left}px`, width: `${width}px` };
      if (spaceBelow < panelHeight && rect.top > spaceBelow) {
        style.bottom = `${window.innerHeight - rect.top + gap}px`;
      } else {
        style.top = `${rect.bottom + gap}px`;
      }
      this.panelStyle = style;
    },
    selectClass(selectedClass) {
      if (selectedClass !== this.className) {
        this.$emit('update-class', this.entityId, selectedClass);
      }
      this.closeDropdown();
    },
    handleClickOutside(event) {
      if (!this.$el.contains(event.target) && !this.$refs.panel?.contains(event.target)) {
        this.closeDropdown();
      }
    },
  },
};
</script>
