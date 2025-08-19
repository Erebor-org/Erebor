<template>
  <td class="px-4 py-3">
    <span :class="chipClass">
      {{ value }}
      <span v-if="colorize && hint" class="ml-2">{{ symbol }}</span>
    </span>
  </td>
</template>
<script setup>
import { computed } from 'vue'
import { classFromHint } from '../../utils/dofusdleHintUtils'
const props = defineProps({
  value: Number,
  hint: String,
  colorize: Boolean,
  label: String,
})
const symbol = computed(() => {
  if (!props.colorize || !props.hint) return ''
  if (props.hint === 'up') return '▲'
  if (props.hint === 'down') return '▼'
  if (props.hint === 'eq') return '■'
  return ''
})
const chipClass = computed(() => {
  if (!props.colorize || !props.hint) return 'inline-flex items-center px-3 py-1 rounded-xl bg-gray-100 text-gray-800 text-xl shadow'
  return `inline-flex items-center px-3 py-1 rounded-xl text-xl font-bold shadow ${classFromHint(props.hint, props.label?.toLowerCase() || 'stat')}`
})
</script>
