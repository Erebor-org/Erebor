<template>
  <div class="flex items-center gap-1">
    <div 
      class="w-4 h-4 rounded-full flex items-center justify-center text-xs font-bold text-white"
      :class="resistanceClasses[element]"
      :title="`Résistance ${translateElement(element)}`"
    >
      {{ element.charAt(0).toUpperCase() }}
    </div>
    <span class="text-sm font-medium">{{ translateElement(element) }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  element: {
    type: String,
    required: true,
    validator: (value) => ['earth', 'air', 'fire', 'water', 'neutral'].includes(value)
  }
})

const resistanceClasses = {
  earth: 'bg-amber-600',      // Marron/terre
  air: 'bg-blue-400',         // Bleu ciel
  fire: 'bg-red-500',         // Rouge feu
  water: 'bg-blue-600',       // Bleu eau
  neutral: 'bg-gray-500'      // Gris neutre
}

const translateElement = (element) => {
  const translations = {
    'earth': 'Terre',
    'air': 'Air', 
    'fire': 'Feu',
    'water': 'Eau',
    'neutral': 'Neutre'
  }
  return translations[element] || element
}
</script>
