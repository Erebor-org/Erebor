<template>
  <div class="relative theme-select-container">
    <button
      type="button"
      @click="toggleDropdown"
      :disabled="disabled"
      class="w-full relative bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 flex items-center justify-between group disabled:opacity-50 disabled:cursor-not-allowed hover:border-theme-primary/50"
      :class="{
        'border-theme-primary': isOpen,
        'shadow-lg shadow-theme-primary/25': isOpen
      }"
    >
      <span class="flex items-center">
        <span :class="selectedOption ? 'text-theme-text' : 'text-theme-text-muted'">
          {{ selectedOption || placeholder }}
        </span>
      </span>
      <svg
        class="w-5 h-5 text-theme-text-muted group-hover:text-theme-primary transition-all duration-200 flex-shrink-0 ml-2"
        :class="{ 'transform rotate-180': isOpen }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-2 w-full bg-theme-card border-2 border-theme-border rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm"
        :class="dropdownClass"
      >
        <div class="max-h-64 overflow-y-auto">
          <button
            v-for="option in options"
            :key="getOptionValue(option)"
            @click="selectOption(option)"
            type="button"
            class="w-full px-4 py-3 text-left text-theme-text hover:bg-theme-primary/10 transition-all duration-200 flex items-center justify-between group focus:outline-none focus:bg-theme-primary/10"
            :class="{
              'bg-theme-primary/20 text-theme-primary font-semibold': isSelected(option),
              'hover:text-theme-primary': !isSelected(option)
            }"
          >
            <span>{{ getOptionLabel(option) }}</span>
            <svg
              v-if="isSelected(option)"
              class="w-5 h-5 text-theme-primary flex-shrink-0 ml-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
export default {
  name: 'ThemeSelect',
  inheritAttrs: false,
  props: {
    modelValue: {
      type: [String, Number, null],
      default: null
    },
    options: {
      type: Array,
      required: true
    },
    placeholder: {
      type: String,
      default: 'Sélectionner...'
    },
    disabled: {
      type: Boolean,
      default: false
    },
    optionValue: {
      type: String,
      default: 'value'
    },
    optionLabel: {
      type: String,
      default: 'label'
    },
    dropdownClass: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      isOpen: false
    }
  },
  computed: {
    selectedOption() {
      if (this.modelValue === null || this.modelValue === undefined || this.modelValue === '') {
        return null
      }
      const option = this.options.find(opt => this.getOptionValue(opt) === this.modelValue)
      return option ? this.getOptionLabel(option) : null
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
  },
  methods: {
    getOptionValue(option) {
      return typeof option === 'object' ? option[this.optionValue] : option
    },
    getOptionLabel(option) {
      return typeof option === 'object' ? option[this.optionLabel] : option
    },
    isSelected(option) {
      return this.getOptionValue(option) === this.modelValue
    },
    toggleDropdown() {
      if (!this.disabled) {
        this.isOpen = !this.isOpen
      }
    },
    selectOption(option) {
      const value = this.getOptionValue(option)
      // If empty string or null, emit null; otherwise emit the value
      const emitValue = (value === '' || value === null || value === undefined) ? null : value
      this.$emit('update:modelValue', emitValue)
      this.isOpen = false
    },
    handleClickOutside(event) {
      if (this.$el && !this.$el.contains(event.target)) {
        this.isOpen = false
      }
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar styling */
.max-h-64::-webkit-scrollbar {
  width: 8px;
}

.max-h-64::-webkit-scrollbar-track {
  background: var(--theme-bg-muted);
  border-radius: 4px;
}

.max-h-64::-webkit-scrollbar-thumb {
  background: var(--theme-border);
  border-radius: 4px;
}

.max-h-64::-webkit-scrollbar-thumb:hover {
  background: var(--theme-primary);
}
</style>

