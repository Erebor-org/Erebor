<template>
  <span class="kept-cell" :class="`is-${keptLevel(value?.retained, value?.n)}`" :title="tooltip">
    <template v-if="value?.n">
      <span class="kept-text"><strong>{{ value.retained }}</strong> sur {{ value.n }}</span>
      <PeopleDots :kept="value.retained" :total="value.n" size="sm" />
    </template>
    <span v-else class="kept-early">trop tôt</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';
import PeopleDots from '@/components/home/PeopleDots.vue';
import { keptLevel, milestoneLabel } from '@/utils/plainLanguage';

// Combien de recrues sont restées après un délai : « 17 sur 19 » + petits personnages.
// Seules les recrues arrivées depuis au moins ce délai sont comptées (calcul serveur).
const props = defineProps({
  value: { type: Object, default: null }, // { retained, n }
  milestone: { type: Number, default: 61 }, // délai en jours
});

const tooltip = computed(() => {
  const delay = milestoneLabel(props.milestone);
  if (!props.value?.n) return `Aucune recrue n'est encore arrivée depuis ${delay}.`;
  return `${props.value.retained} des ${props.value.n} recrues arrivées depuis plus de ${delay} sont restées au moins ${delay}.`;
});
</script>

<style scoped>
.kept-cell {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
  min-width: 5.5rem;
  padding: 0.3rem 0.5rem;
  border-radius: 0.5rem;
  line-height: 1.15;
}

.kept-text {
  font-size: 0.82rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.kept-text strong {
  font-size: 0.95rem;
  color: var(--text);
}

.kept-early {
  font-size: 0.75rem;
  font-style: italic;
  color: var(--text-muted);
}

.is-good { background-color: rgba(var(--accent-rgb), 0.18); }
.is-fair { background-color: rgba(var(--accent-rgb), 0.07); }
.is-low { background-color: rgba(var(--primary-rgb), 0.14); }
</style>
