<template>
  <td class="px-4 py-3">
    <span :class="chipClass">
      {{ areas.join(', ') }}
      <span v-if="colorize && hint" class="ml-2">{{ symbol }}</span>
    </span>
  </td>
</template>
<script setup>
import { computed } from 'vue'
import { classFromHint } from '../../utils/dofusdleHintUtils'
const props = defineProps({
  areas: Array,
  hint: String,
  colorize: Boolean,
})
const symbol = computed(() => {
  if (!props.colorize || !props.hint) return ''
  if (props.hint === 'exact') return '●'
  if (props.hint === 'overlap') return '◐'
  if (props.hint === 'miss') return '○'
  return ''
})
const chipClass = computed(() => {
  if (!props.colorize || !props.hint) return 'inline-flex items-center px-3 py-1 rounded-xl bg-gray-100 text-gray-800 text-xl shadow'
  return `inline-flex items-center px-3 py-1 rounded-xl text-xl font-bold shadow ${classFromHint(props.hint, 'zone')}`
})
</script>
