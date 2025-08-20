<template>
  <td class="px-4 py-3">
    <div class="flex flex-col items-center gap-1">
      <span class="font-bold text-lg">{{ zoneLabel }}</span>
      <HintIndicator :hint="hint" :colorize="colorize" />
    </div>
  </td>
</template>

<script setup>
import { computed } from 'vue'
import HintIndicator from './HintIndicator.vue'

const props = defineProps({
  area: String,
  subarea: String,
  hint: String,
  colorize: Boolean,
})

const zoneLabel = computed(() => {
  if (props.area && props.subarea) {
    return `${props.area} > ${props.subarea}`
  }
  if (props.area) {
    return props.area
  }
  if (props.subarea) {
    return props.subarea
  }
  return 'Inconnue'
})
</script>
