<template>
  <!-- One node of a legendary recipe tree, and its children (recursive) -->
  <div class="gw-node" :class="{ 'gw-node--dim': dim }">
    <div class="gw-node-row" :style="{ paddingLeft: `${depth * 1.25 + 0.5}rem` }">
      <button
        v-if="node.children.length"
        @click="open = !open"
        class="gw-node-toggle"
        :aria-expanded="open"
        :aria-label="open ? 'Replier' : 'Déplier'"
      >
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
      </button>
      <span v-else class="gw-node-toggle"></span>

      <img v-if="meta?.icon" :src="meta.icon" :alt="name" class="gw-node-icon" :style="{ '--rarity': rarityColor(meta?.rarity) }" loading="lazy" />

      <span class="flex-1 min-w-0">
        <button
          @click="howTo?.open(node.type, node.id)"
          class="block max-w-full truncate font-medium text-left hover:underline"
          :style="{ color: node.type === 'item' ? rarityColor(meta?.rarity) : 'var(--text)' }"
          title="Comment l'obtenir"
        >
          <template v-if="node.count > 1">{{ node.count.toLocaleString('fr-FR') }} × </template>{{ name }}
        </button>
        <span class="flex items-center gap-2 text-xs text-theme-text-muted">
          <span v-if="node.count > 1 || node.owned" class="gw-node-bar" :title="`${node.owned} possédé(s) sur ${node.count}`"><span :style="{ width: `${(node.owned / node.count) * 100}%` }"></span></span>
          <span>{{ node.owned.toLocaleString('fr-FR') }} / {{ node.count.toLocaleString('fr-FR') }}</span>
          <span v-if="node.source && node.decision === 'craft' && node.source !== 'vendor'">· {{ RECIPE_SOURCES[node.source] || node.source }}</span>
        </span>
      </span>

      <span v-if="node.objectives" class="gw-chip gw-chip--objective" title="Étapes à faire en jeu, non achetables">
        {{ node.objectives }} objectif{{ node.objectives > 1 ? 's' : '' }}
      </span>
      <span class="gw-chip" :style="{ '--chip': PLAN_DECISIONS[node.decision]?.color }">{{ decisionLabel }}</span>
      <span class="gw-node-cost">
        <Gw2Coins v-if="node.cost" :value="node.cost" />
      </span>
    </div>

    <p v-if="node.childrenIgnored && open" class="gw-node-note" :style="{ paddingLeft: `${(depth + 1) * 1.25 + 2.25}rem` }">
      L'acheter (≈ <Gw2Coins :value="node.buyCost" />) revient moins cher que le fabriquer (≈ <Gw2Coins :value="node.craftCost" />) : la recette est indiquée pour information.
    </p>

    <template v-if="open">
      <Gw2RecipeNode
        v-for="(child, i) in node.children"
        :key="`${child.type}:${child.id}:${i}`"
        :node="child"
        :items="items"
        :currencies="currencies"
        :depth="depth + 1"
        :dim="dim || node.childrenIgnored"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { PLAN_DECISIONS, RECIPE_SOURCES, rarityColor } from '@/utils/gw2';

defineOptions({ name: 'Gw2RecipeNode' });

// Provided by the legendary page: opens the "how to get" side panel
const howTo = inject('gw2HowTo', null);

const props = defineProps({
  node: { type: Object, required: true },
  items: { type: Object, default: () => ({}) },
  currencies: { type: Object, default: () => ({}) },
  depth: { type: Number, default: 0 },
  // Branch not used by the plan (its parent is cheaper to buy)
  dim: { type: Boolean, default: false },
});

// The legendary and its unfinished components start open, finished branches closed
const open = ref(props.depth === 0 || (props.depth === 1 && props.node.missing > 0 && !props.node.childrenIgnored));

const meta = computed(() => (props.node.type === 'currency' ? props.currencies[props.node.id] : props.items[props.node.id]));
const name = computed(() => meta.value?.name || `#${props.node.id}`);
// Bought from a merchant (with gold, karma, dungeon tokens...) rather than crafted
const decisionLabel = computed(() =>
  props.node.decision === 'craft' && props.node.source === 'vendor' ? 'Marchand' : PLAN_DECISIONS[props.node.decision]?.label
);
</script>

<style scoped>
.gw-node-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding-top: 0.45rem;
  padding-bottom: 0.45rem;
  padding-right: 0.9rem;
  border-bottom: 1px solid var(--border);
}
.gw-node-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}
.gw-node--dim > .gw-node-row {
  opacity: 0.5;
}
.gw-node-toggle {
  width: 1.25rem;
  height: 1.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-muted);
}
.gw-node-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.4rem;
  border: 1px solid var(--rarity, var(--border));
  flex-shrink: 0;
}
.gw-node-bar {
  display: inline-block;
  width: 4rem;
  height: 0.3rem;
  border-radius: 9999px;
  background-color: var(--border);
  overflow: hidden;
}
.gw-node-bar > span {
  display: block;
  height: 100%;
  background-color: var(--success);
}
.gw-chip {
  flex-shrink: 0;
  padding: 0.05rem 0.55rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--chip, var(--text-muted));
  border: 1px solid color-mix(in srgb, var(--chip, var(--border)) 45%, transparent);
  background-color: color-mix(in srgb, var(--chip, transparent) 10%, transparent);
}
.gw-chip--objective {
  --chip: #B679D5;
}
.gw-node-cost {
  width: 7.5rem;
  flex-shrink: 0;
  text-align: right;
  font-size: 0.85rem;
}
.gw-node-note {
  padding-top: 0.35rem;
  padding-bottom: 0.35rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border);
}

@media (max-width: 640px) {
  .gw-node-cost {
    width: auto;
  }
  .gw-node-row .gw-chip:not(.gw-chip--objective) {
    display: none;
  }
}
</style>
