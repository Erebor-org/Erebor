<template>
  <div v-if="hint && colorize" class="flex justify-center">
    <span 
      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold text-white shadow-lg transition-all duration-200 hover:scale-105"
      :class="hintClasses[hint]"
      :title="hintTooltip"
    >
      {{ hintSymbol }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  hint: {
    type: String,
    default: null,
    validator: (value) => ['exact', 'partial', 'up', 'down', 'match', 'miss', 'unknown'].includes(value)
  },
  colorize: {
    type: Boolean,
    default: false
  }
})

const hintSymbol = computed(() => {
  const symbols = {
    'exact': '●',
    'partial': '◐',
    'up': '▲',
    'down': '▼',
    'match': '✓',
    'miss': '✗',
    'unknown': '?'
  }
  return symbols[props.hint] || '?'
})

const hintClasses = computed(() => ({
  'exact': 'bg-emerald-500 shadow-emerald-500/50',      // Vert émeraude pour exact
  'partial': 'bg-amber-500 shadow-amber-500/50',       // Orange ambré pour partiel
  'up': 'bg-blue-500 shadow-blue-500/50',              // Bleu pour plus haut
  'down': 'bg-purple-500 shadow-purple-500/50',         // Violet pour plus bas
  'match': 'bg-emerald-500 shadow-emerald-500/50',     // Vert émeraude pour match
  'miss': 'bg-red-500 shadow-red-500/50',              // Rouge pour miss
  'unknown': 'bg-slate-500 shadow-slate-500/50'        // Gris ardoise pour inconnu
}))

const hintTooltip = computed(() => {
  const tooltips = {
    'exact': 'Exact',
    'partial': 'Partiel',
    'up': 'Plus haut',
    'down': 'Plus bas',
    'match': 'Correspond',
    'miss': 'Ne correspond pas',
    'unknown': 'Inconnu'
  }
  return tooltips[props.hint] || 'Inconnu'
})
</script>
