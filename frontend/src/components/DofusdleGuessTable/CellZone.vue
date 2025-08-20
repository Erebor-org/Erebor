<template>
  <td class="px-4 py-3">
    <span :class="chipClass">
      {{ zoneLabel }}
      <span v-if="colorize && hint" class="ml-2">{{ symbol }}</span>
    </span>
    <div v-if="allZones.length" class="flex flex-wrap gap-1 mt-1">
      <span v-for="z in allZones" :key="z" class="inline-block bg-theme-bg-muted text-theme-text-muted px-2 py-0.5 rounded text-xs border border-theme-border">{{ z }}</span>
    </div>
  </td>
</template>
<script setup>
import { computed } from 'vue'
import { classFromHint } from '../../utils/dofusdleHintUtils'
const props = defineProps({
  areas: Array,
  subareas: Array,
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
function normalizeZone(arr) {
  if (!arr) return []
  if (arr.length && typeof arr[0] === 'object' && arr[0] !== null) {
    return arr.map(z => z.name || z.label || z.toString())
  }
  return arr
}
const zoneLabel = computed(() => {
  let zones = normalizeZone(props.areas)
  if (!zones.length) zones = normalizeZone(props.subareas)
  zones = [...new Set(zones)]
  if (zones.length) return zones.join(', ')
  return 'Inconnue'
})
const allZones = computed(() => {
  const a = normalizeZone(props.areas)
  const s = normalizeZone(props.subareas)
  return [...new Set([...a, ...s])].filter(z => z && z !== 'Inconnue')
})
</script>
