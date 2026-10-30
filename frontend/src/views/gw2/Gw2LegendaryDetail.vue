<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <RouterLink to="/gw2/legendaires" class="inline-flex items-center gap-1 text-sm text-theme-text-muted hover:text-theme-primary mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Légendaires
      </RouterLink>

      <div v-if="isLoading && !data" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">Calcul de l'arbre de fabrication...</div>
      <div v-else-if="error && !data" class="glass-card rounded-2xl text-center py-16 px-4">
        <p class="text-theme-error font-medium mb-3">{{ error }}</p>
        <button @click="load()" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
      </div>

      <template v-else-if="data">
        <!-- Header: what it is, where you stand -->
        <div class="glass-card rounded-2xl p-6 mb-6">
          <div class="flex flex-col md:flex-row md:items-center gap-5">
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <img v-if="meta?.icon" :src="meta.icon" :alt="meta.name" class="w-16 h-16 rounded-xl border-2 flex-shrink-0" style="border-color: #8E4FD6" />
              <div class="min-w-0">
                <h1 class="text-3xl font-serif font-bold text-theme-primary truncate">{{ meta?.name }}</h1>
                <p class="text-sm text-theme-text-muted">
                  {{ equipmentLabel(meta) }} · {{ GENERATIONS[legendary.generation]?.title || 'Bijou légendaire' }}
                </p>
                <div class="flex flex-wrap gap-2 mt-2">
                  <span v-if="legendary.unlocked" class="gw-badge gw-badge--success">Déjà débloqué</span>
                  <span v-if="legendary.tradingPostPrice" class="gw-badge" title="Prix de l'arme finie au comptoir, pour comparer">
                    Au comptoir : <Gw2Coins :value="legendary.tradingPostPrice" class="ml-1" />
                  </span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-3 md:w-[26rem]">
              <div class="gw-stat">
                <span class="gw-stat-label">Progression</span>
                <span class="gw-stat-value">{{ Math.floor(legendary.progress * 100) }} %</span>
              </div>
              <div class="gw-stat" :title="`Coût en partant de rien : ${coinsText(legendary.costFromScratch)}`">
                <span class="gw-stat-label">Reste à acheter</span>
                <span class="gw-stat-value text-base"><Gw2Coins :value="legendary.cost" /></span>
              </div>
              <div class="gw-stat">
                <span class="gw-stat-label">Objectifs</span>
                <span class="gw-stat-value">{{ legendary.objectives.done }}<span class="text-sm text-theme-text-muted">/{{ legendary.objectives.total }}</span></span>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-3 mt-5 pt-5 border-t border-theme-border">
            <button
              @click="toggleTrack"
              :disabled="busy"
              class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 disabled:opacity-50"
              :class="data.tracked ? 'bg-theme-primary/10 text-theme-primary hover:bg-theme-primary/20' : 'bg-theme-primary text-white hover:bg-theme-primary-hover'"
            >
              {{ data.tracked ? 'Suivi · ne plus suivre' : 'Suivre ce légendaire' }}
            </button>
            <span class="text-xs text-theme-text-muted ml-auto">
              <template v-if="error">{{ error }}</template>
              <template v-else-if="loadedAt">Mis à jour à {{ loadedAt }}</template>
            </span>
            <button @click="load({ refresh: true })" :disabled="isLoading" class="inline-flex items-center gap-2 px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium disabled:opacity-50 disabled:cursor-wait">
              <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              Actualiser
            </button>
          </div>
        </div>

        <!-- Sub tabs -->
        <div class="flex flex-wrap gap-2 mb-4" role="tablist">
          <button v-for="t in subTabs" :key="t.key" role="tab" :aria-selected="view === t.key" @click="view = t.key" class="gw-tab" :class="{ 'gw-tab--active': view === t.key }">
            {{ t.label }}<span v-if="t.count !== undefined" class="gw-tab-count">{{ t.count }}</span>
          </button>
        </div>

        <!-- Tree -->
        <div v-if="view === 'tree'" class="glass-card rounded-2xl overflow-hidden">
          <Gw2RecipeNode :node="data.tree" :items="data.items" :currencies="data.currencies" />
        </div>

        <!-- Shopping list -->
        <Gw2ShoppingList v-else-if="view === 'list'" :rows="data.shoppingList" :items="data.items" :currencies="data.currencies" />

        <!-- Objectives -->
        <template v-else>
          <!-- In-game collections, with the account's progress step by step -->
          <template v-if="data.achievements.length">
            <h2 class="font-serif font-bold text-lg text-theme-text mb-1">Collections du jeu</h2>
            <p class="text-sm text-theme-text-muted mb-3">Ta progression réelle dans le Panthéon des succès (Collections › Armes légendaires).</p>
            <div class="glass-card rounded-2xl mb-8">
              <div v-for="a in data.achievements" :key="a.id" class="gw-collection">
                <button @click="toggleAchievement(a.id)" class="gw-collection-head" :aria-expanded="openAchievements.includes(a.id)">
                  <svg class="w-4 h-4 text-theme-text-muted transition-transform flex-shrink-0" :class="{ 'rotate-90': openAchievements.includes(a.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  <span class="flex-1 min-w-0 text-left">
                    <span class="block font-semibold truncate" :class="a.done ? 'text-theme-success' : 'text-theme-text'">{{ a.name }}</span>
                    <span class="block text-xs text-theme-text-muted">{{ COLLECTION_KINDS[a.kind] }}</span>
                  </span>
                  <span class="w-28 h-1.5 rounded-full bg-theme-border overflow-hidden flex-shrink-0"><span class="block h-full rounded-full bg-[#B679D5]" :style="{ width: `${a.max ? (Math.min(a.current, a.max) / a.max) * 100 : 0}%` }"></span></span>
                  <span class="w-14 text-right text-sm tabular-nums text-theme-text-muted">{{ a.done ? '✓' : `${a.current}/${a.max}` }}</span>
                </button>
                <ul v-if="openAchievements.includes(a.id)" class="gw-collection-steps">
                  <li v-for="(bit, i) in a.bits" :key="i" :class="{ 'gw-step-done': bit.done }">
                    <span class="gw-step-check">{{ bit.done ? '✓' : '' }}</span>
                    <img v-if="bit.item && data.items[bit.item]?.icon" :src="data.items[bit.item].icon" alt="" class="w-6 h-6 rounded flex-shrink-0" loading="lazy" />
                    <span class="min-w-0">
                      <!-- Some collection pieces are hidden from the API: their step text is enough -->
                      <span v-if="bit.item && data.items[bit.item]?.name" class="block font-medium text-theme-text">{{ data.items[bit.item].name }}</span>
                      <span v-if="bit.text" class="block text-xs" :class="bit.item && data.items[bit.item]?.name ? 'text-theme-text-muted' : 'text-theme-text'">{{ bit.text }}</span>
                      <span v-else-if="!data.items[bit.item]?.name" class="block text-xs text-theme-text-muted">Étape {{ i + 1 }}</span>
                    </span>
                  </li>
                </ul>
              </div>
            </div>
          </template>

          <h2 class="font-serif font-bold text-lg text-theme-text mb-1">À obtenir en jeu</h2>
          <p class="text-sm text-theme-text-muted mb-3">Ce qui ne s'achète pas au comptoir : monnaies, dons d'activité, objets liés.</p>
          <div v-if="!data.objectives.length" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
            Aucun objectif en attente : tout le reste s'achète ou se fabrique
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="o in data.objectives"
              :key="`${o.type}:${o.id}`"
              class="glass-card rounded-2xl p-5 flex gap-4 cursor-pointer hover:border-theme-primary transition-colors"
              @click="openHowTo(o.type, o.id)"
              title="Comment l'obtenir"
            >
              <img v-if="objectiveMeta(o)?.icon" :src="objectiveMeta(o).icon" :alt="objectiveMeta(o)?.name" class="w-12 h-12 rounded-lg border border-theme-border flex-shrink-0" />
              <div class="min-w-0 flex-1">
                <p class="font-semibold text-theme-text">{{ objectiveMeta(o)?.name || `#${o.id}` }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="flex-1 h-1.5 rounded-full bg-theme-border overflow-hidden"><span class="block h-full rounded-full bg-[#B679D5]" :style="{ width: `${(o.owned / o.needed) * 100}%` }"></span></span>
                  <span class="text-sm tabular-nums text-theme-text-muted">{{ o.owned.toLocaleString('fr-FR') }} / {{ o.needed.toLocaleString('fr-FR') }}</span>
                </div>
                <p class="text-sm text-theme-text-muted mt-2">
                  {{ o.hint || (o.type === 'currency' ? 'Monnaie à gagner en jeu.' : 'Objet lié au compte : il s\'obtient en jeu, il ne s\'achète pas au comptoir.') }}
                </p>
                <a v-if="o.hintSource" :href="o.hintSource" target="_blank" rel="noopener" class="inline-block text-xs text-theme-link hover:underline mt-1">Source : Le Bus Magique</a>
              </div>
            </div>
          </div>
        </template>
      </template>
    </div>

    <Gw2HowToGet
      v-if="data"
      ref="howToPanel"
      :how-to="data.howTo"
      :items="data.items"
      :currencies="data.currencies"
      :usage="usage"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, provide } from 'vue';
import { useRoute } from 'vue-router';
import Gw2RecipeNode from '@/components/gw2/Gw2RecipeNode.vue';
import Gw2ShoppingList from '@/components/gw2/Gw2ShoppingList.vue';
import Gw2HowToGet from '@/components/gw2/Gw2HowToGet.vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { fetchGw2Legendary, trackGw2Legendary, untrackGw2Legendary, gw2ErrorMessage } from '@/services/gw2Api';
import { GENERATIONS, equipmentLabel, splitCoins } from '@/utils/gw2';

const route = useRoute();

// "How to get" side panel, opened from the tree, the shopping list and the objectives
const howToPanel = ref(null);
const openHowTo = (type, id) => howToPanel.value?.open(type, id, { reset: true });
provide('gw2HowTo', { open: openHowTo, data: computed(() => data.value?.howTo) });

// What this legendary needs of each resource, all tree nodes together
const usage = computed(() => {
  const totals = {};
  const walk = (node) => {
    const key = `${node.type}:${node.id}`;
    const t = totals[key] || (totals[key] = { needed: 0, owned: 0, missing: 0 });
    t.needed += node.count;
    t.owned += node.owned;
    t.missing += node.missing;
    node.children.forEach(walk);
  };
  if (data.value?.tree) walk(data.value.tree);
  return totals;
});

const data = ref(null);
const isLoading = ref(false);
const error = ref('');
const loadedAt = ref('');
const busy = ref(false);
const view = ref('tree');
const openAchievements = ref([]);
const COLLECTION_KINDS = {
  collection: 'Collection de la légendaire',
  precursor: 'Collection du précurseur',
  related: 'Collection liée',
};
const toggleAchievement = (id) => {
  openAchievements.value = openAchievements.value.includes(id)
    ? openAchievements.value.filter(a => a !== id)
    : [...openAchievements.value, id];
};

const legendary = computed(() => data.value?.legendary);
const meta = computed(() => data.value?.items[route.params.id]);

const subTabs = computed(() => [
  { key: 'tree', label: 'Arbre' },
  { key: 'list', label: 'Liste de courses', count: data.value?.shoppingList.filter(r => r.missing > 0).length },
  { key: 'objectives', label: 'Objectifs', count: (data.value?.objectives.length || 0) + (data.value?.achievements.filter(a => !a.done).length || 0) },
]);

const objectiveMeta = (o) => (o.type === 'currency' ? data.value.currencies[o.id] : data.value.items[o.id]);
const coinsText = (copper) => {
  const { gold, silver, copper: c } = splitCoins(copper);
  return [gold && `${gold} po`, silver && `${silver} pa`, (c || (!gold && !silver)) && `${c} pc`].filter(Boolean).join(' ');
};

const load = async ({ refresh = false } = {}) => {
  isLoading.value = true;
  error.value = '';
  try {
    data.value = await fetchGw2Legendary(route.params.id, { refresh });
    loadedAt.value = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
  } catch (e) {
    error.value = gw2ErrorMessage(e, 'Impossible de calculer ce légendaire');
  } finally {
    isLoading.value = false;
  }
};

const toggleTrack = async () => {
  busy.value = true;
  try {
    const { tracked } = data.value.tracked ? await untrackGw2Legendary(route.params.id) : await trackGw2Legendary(route.params.id);
    data.value = { ...data.value, tracked: tracked.includes(Number(route.params.id)) };
  } catch (e) {
    error.value = gw2ErrorMessage(e, 'Impossible de modifier le suivi');
  } finally {
    busy.value = false;
  }
};

watch(() => route.params.id, (id) => {
  if (id) {
    data.value = null;
    load();
  }
});
onMounted(load);
</script>

<style scoped>
.gw-collection + .gw-collection {
  border-top: 1px solid var(--border);
}
.gw-collection-head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.8rem 1.1rem;
}
.gw-collection-head:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}
.gw-collection-steps {
  padding: 0 1.1rem 0.9rem 2.85rem;
  display: grid;
  gap: 0.5rem;
}
.gw-collection-steps li {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.875rem;
}
.gw-step-check {
  width: 1.1rem;
  height: 1.1rem;
  flex-shrink: 0;
  border-radius: 0.3rem;
  border: 1px solid var(--border);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: var(--success);
  margin-top: 0.15rem;
}
.gw-step-done .gw-step-check {
  border-color: var(--success);
  background-color: color-mix(in srgb, var(--success) 12%, transparent);
}
.gw-step-done .font-medium {
  text-decoration: line-through;
  opacity: 0.7;
}
.gw-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-muted);
  border: 1px solid var(--border);
  transition: all 0.2s;
}
.gw-tab:hover {
  color: var(--text);
}
.gw-tab--active {
  color: var(--primary);
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.08);
}
.gw-tab-count {
  font-size: 0.75rem;
  padding: 0 0.45rem;
  border-radius: 9999px;
  background-color: var(--border);
  color: var(--text);
}
.gw-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.1rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text-muted);
}
.gw-badge--success {
  color: var(--success);
  border-color: color-mix(in srgb, var(--success) 40%, transparent);
  background-color: color-mix(in srgb, var(--success) 10%, transparent);
}
.gw-stat {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.7rem 0.85rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  background-color: rgba(var(--primary-rgb), 0.03);
}
.gw-stat-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.gw-stat-value {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}
</style>
