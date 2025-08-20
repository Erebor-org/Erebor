<template>
  <td class="px-4 py-4 text-center">
    <div class="flex flex-col items-center gap-2">
      <div 
        class="w-full max-w-28 px-3 py-2 rounded-xl font-bold text-white text-center shadow-lg transition-all duration-200 hover:scale-105"
        :class="cellClasses"
      >
        {{ displayValue }}
      </div>
      <HintIndicator :hint="hint" :colorize="colorize" />
    </div>
  </td>
</template>

<script setup>
import { computed } from 'vue'
import HintIndicator from './HintIndicator.vue'

const props = defineProps({
  value: { type: String, default: '' },
  hint: { type: String, default: null },
  colorize: { type: Boolean, default: false }
})

const displayValue = computed(() => {
  if (!props.value) return '?'
  return props.value.length > 12 ? props.value.substring(0, 12) + '...' : props.value
})

const cellClasses = computed(() => {
  if (!props.colorize || !props.hint) {
    return 'bg-slate-200 text-slate-800 shadow-slate-300/50'
  }
  
  const baseClasses = 'shadow-lg font-bold'
  
  switch (props.hint) {
    case 'exact':
      return `bg-emerald-500 text-white shadow-emerald-500/50 ${baseClasses}`
    case 'partial':
      return `bg-amber-500 text-white shadow-amber-500/50 ${baseClasses}`
    case 'miss':
      return `bg-red-500 text-white shadow-red-500/50 ${baseClasses}`
    default:
      return `bg-slate-500 text-white shadow-slate-500/50 ${baseClasses}`
  }
})
</script>
