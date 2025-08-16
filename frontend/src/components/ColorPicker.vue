<template>
  <div class="color-picker">
    <div class="flex-1">
      <label class="block text-sm font-medium text-theme-text mb-1">{{ label }}</label>
      <p class="text-xs text-theme-text-muted mb-2">{{ description }}</p>
    </div>
    
    <div class="flex items-center space-x-3">
      <input
        type="color"
        :value="modelValue"
        @input="updateColor"
        class="w-12 h-12 rounded-lg border-2 border-theme-border cursor-pointer bg-transparent"
        :title="`Pick ${label.toLowerCase()}`"
      />
      
      <input
        type="text"
        :value="modelValue"
        @input="updateText"
        @blur="validateHex"
        class="w-24 px-3 py-2 bg-theme-bg border border-theme-border rounded-lg text-theme-text text-sm font-mono focus:ring-2 focus:ring-theme-ring focus:border-theme-primary"
        placeholder="#000000"
        maxlength="7"
      />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  modelValue: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const updateColor = (event) => {
  emit('update:modelValue', event.target.value)
}

const updateText = (event) => {
  let value = event.target.value
  
  // Add # if missing
  if (value && !value.startsWith('#')) {
    value = '#' + value
  }
  
  emit('update:modelValue', value)
}

const validateHex = (event) => {
  let value = event.target.value
  
  // Validate hex color format
  const hexRegex = /^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/
  
  if (value && !hexRegex.test(value)) {
    // Reset to previous valid value
    event.target.value = props.modelValue
    alert('Veuillez entrer une couleur hexadécimale valide (ex: #FF0000 ou FF0000)')
  }
}
</script>

<style scoped>
.color-picker {
  @apply flex items-center justify-between space-x-4 p-3 bg-theme-bg-muted rounded-lg border border-theme-border;
}

/* Custom color input styling */
input[type="color"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-color: transparent;
  border: none;
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 0.5rem;
}

input[type="color"]::-moz-color-swatch {
  border: none;
  border-radius: 0.5rem;
}
</style>
