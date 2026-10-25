<template>
  <!-- One legendary: progress against the account's stock, cost to finish, next step -->
  <article class="gw-legend glass-card" :class="{ 'gw-legend--tracked': legendary.tracked && mode === 'catalog' }">
    <RouterLink :to="`/gw2/legendaires/${legendary.id}`" class="flex items-center gap-3 min-w-0 group">
      <img v-if="meta?.icon" :src="meta.icon" :alt="meta.name" class="gw-legend-icon" loading="lazy" />
      <span class="min-w-0">
        <span class="block font-bold text-theme-text truncate group-hover:text-theme-primary transition-colors">{{ meta?.name || `#${legendary.id}` }}</span>
        <span class="block text-xs text-theme-text-muted">
          {{ equipmentLabel(meta) }}
          <template v-if="GENERATIONS[legendary.generation]"> · <span :title="GENERATIONS[legendary.generation].title">{{ GENERATIONS[legendary.generation].label }}</span></template>
          <template v-else-if="meta?.type === 'Trinket'"> · Bijou</template>
        </span>
      </span>
    </RouterLink>

    <div class="flex flex-wrap gap-1.5 mt-3 min-h-[1.25rem]">
      <span v-if="legendary.unlocked" class="gw-badge gw-badge--success" title="Déjà dans ton armurerie légendaire">Débloqué</span>
      <span v-if="!legendary.hasRecipe" class="gw-badge gw-badge--muted" title="Ne se fabrique pas : s'obtient en récompense en jeu">Récompense en jeu</span>
      <span v-if="legendary.collection" class="gw-badge" :class="{ 'gw-badge--success': legendary.collection.done }" title="Collection « Arme légendaire » du jeu : composants déjà obtenus">
        Collection {{ legendary.collection.current }}/{{ legendary.collection.max }}
      </span>
      <span v-if="legendary.precursorCollections?.total" class="gw-badge" :class="{ 'gw-badge--success': legendary.precursorCollections.done === legendary.precursorCollections.total }" title="Collections du précurseur terminées">
        Précurseur {{ legendary.precursorCollections.done }}/{{ legendary.precursorCollections.total }}
      </span>
      <span v-if="legendary.relatedCollections?.total" class="gw-badge" :class="{ 'gw-badge--success': legendary.relatedCollections.done === legendary.relatedCollections.total }" title="Collections du jeu menant à ce légendaire, terminées">
        Collections {{ legendary.relatedCollections.done }}/{{ legendary.relatedCollections.total }}
      </span>
      <span v-if="legendary.sharesWith?.length" class="gw-badge gw-badge--warning" :title="`Ressources en commun avec : ${sharesWithNames}`">
        Ressources partagées
      </span>
    </div>

    <div class="mt-3">
      <div class="flex items-baseline justify-between text-sm mb-1">
        <span class="font-bold text-theme-text">{{ percent }} %</span>
        <span class="text-xs text-theme-text-muted" title="Objectifs non achetables : exploration, McM, éclats d'esprit...">
          Objectifs {{ legendary.objectives.done }}/{{ legendary.objectives.total }}
        </span>
      </div>
      <div class="gw-legend-bar"><span :style="{ width: `${percent}%` }"></span></div>
      <p class="text-xs text-theme-text-muted mt-2">
        <template v-if="legendary.cost > 0">Reste à acheter ≈ <Gw2Coins :value="legendary.cost" /></template>
        <template v-else-if="legendary.progress >= 1">Terminé</template>
        <template v-else-if="!legendary.hasRecipe">Rien à acheter : à obtenir en jeu</template>
        <template v-else>Plus rien à acheter</template>
      </p>
      <p v-if="nextStepName && legendary.progress < 1" class="text-xs text-theme-text-muted mt-0.5 truncate">
        Prochaine étape : <span class="text-theme-text">{{ nextStepName }}</span>
      </p>
    </div>

    <div class="flex gap-2 mt-4">
      <button
        @click="$emit('toggle-track', legendary)"
        :disabled="busy"
        class="gw-legend-btn"
        :class="legendary.tracked ? 'gw-legend-btn--active' : ''"
        :title="legendary.tracked ? 'Ne plus suivre' : 'Ajouter à mes suivis'"
      >
        {{ legendary.tracked ? 'Suivi' : 'Suivre' }}
      </button>
      <RouterLink :to="`/gw2/legendaires/${legendary.id}`" class="gw-legend-btn">Détails</RouterLink>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { GENERATIONS, equipmentLabel } from '@/utils/gw2';

const props = defineProps({
  legendary: { type: Object, required: true },
  items: { type: Object, default: () => ({}) },
  currencies: { type: Object, default: () => ({}) },
  mode: { type: String, default: 'tracked' },
  busy: { type: Boolean, default: false },
});
defineEmits(['toggle-track']);

const meta = computed(() => props.items[props.legendary.id]);
const percent = computed(() => Math.floor((props.legendary.progress || 0) * 100));
const nextStepName = computed(() => {
  const step = props.legendary.nextStep;
  if (!step) return null;
  return (step.type === 'currency' ? props.currencies[step.id] : props.items[step.id])?.name || null;
});
const sharesWithNames = computed(() =>
  (props.legendary.sharesWith || []).map(id => props.items[id]?.name || `#${id}`).join(', ')
);
</script>

<style scoped>
.gw-legend {
  display: flex;
  flex-direction: column;
  padding: 1rem 1.1rem;
  border-radius: 1rem;
}
.gw-legend--tracked {
  border-color: rgba(var(--primary-rgb), 0.45);
}
.gw-legend-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.6rem;
  border: 2px solid #8E4FD6;
  flex-shrink: 0;
}
.gw-legend-bar {
  height: 0.4rem;
  border-radius: 9999px;
  background-color: var(--border);
  overflow: hidden;
}
.gw-legend-bar > span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
}
.gw-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.05rem 0.55rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text-muted);
}
.gw-badge--success {
  color: var(--success);
  border-color: color-mix(in srgb, var(--success) 40%, transparent);
  background-color: color-mix(in srgb, var(--success) 10%, transparent);
}
.gw-badge--warning {
  color: var(--warning);
  border-color: color-mix(in srgb, var(--warning) 40%, transparent);
  background-color: color-mix(in srgb, var(--warning) 10%, transparent);
}
.gw-legend-btn {
  flex: 1;
  text-align: center;
  padding: 0.4rem 0.75rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text);
  transition: all 0.2s;
}
.gw-legend-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}
.gw-legend-btn--active {
  border-color: var(--primary);
  color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.1);
}
.gw-legend-btn:disabled {
  opacity: 0.5;
  cursor: wait;
}
</style>
