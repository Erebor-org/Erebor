<template>
  <!-- Side panel: every way to get one resource of a legendary. Teleported to body: a fixed
       element inside a backdrop-filter container would be mispositioned -->
  <Teleport to="body">
    <div v-if="current" class="gw-howto-backdrop" @click.self="close">
      <aside class="gw-howto glass-modal" role="dialog" aria-modal="true" :aria-label="`Comment obtenir ${name(current)}`">
        <!-- Header -->
        <div class="gw-howto-head">
          <button v-if="stack.length > 1" @click="back" class="gw-howto-icon-btn" aria-label="Retour">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          </button>
          <img v-if="meta(current)?.icon" :src="meta(current).icon" :alt="name(current)" class="gw-howto-img" :style="{ '--rarity': rarityColor(meta(current)?.rarity) }" />
          <div class="min-w-0 flex-1">
            <p class="text-xs uppercase tracking-wide text-theme-text-muted">Comment l'obtenir</p>
            <h2 class="text-lg font-bold leading-tight truncate" :style="{ color: current.type === 'item' ? rarityColor(meta(current)?.rarity) : 'var(--text)' }">{{ name(current) }}</h2>
          </div>
          <button @click="close" class="gw-howto-icon-btn" aria-label="Fermer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <nav v-if="stack.length > 1" class="gw-howto-crumbs" aria-label="Chemin">
          <template v-for="(entry, i) in stack" :key="`${entry.type}:${entry.id}:${i}`">
            <button v-if="i < stack.length - 1" @click="stack = stack.slice(0, i + 1)" class="hover:text-theme-primary">{{ name(entry) }}</button>
            <span v-else class="text-theme-text">{{ name(entry) }}</span>
            <span v-if="i < stack.length - 1" class="opacity-50">›</span>
          </template>
        </nav>

        <div class="gw-howto-body">
          <!-- For this legendary -->
          <section v-if="use" class="gw-howto-card">
            <h3>Pour ce légendaire</h3>
            <div class="grid grid-cols-3 gap-2 text-center">
              <div><p class="gw-howto-num">{{ use.needed.toLocaleString('fr-FR') }}</p><p class="gw-howto-label">requis</p></div>
              <div><p class="gw-howto-num text-theme-success">{{ use.owned.toLocaleString('fr-FR') }}</p><p class="gw-howto-label">possédés</p></div>
              <div><p class="gw-howto-num" :class="{ 'text-theme-warning': use.missing }">{{ use.missing.toLocaleString('fr-FR') }}</p><p class="gw-howto-label">manquants</p></div>
            </div>
            <p v-if="locationsText" class="text-xs text-theme-text-muted mt-2">Rangés : {{ locationsText }}</p>
          </section>

          <template v-if="current.type === 'item'">
            <!-- Trading post -->
            <section v-if="info?.tradingPost" class="gw-howto-card" style="--way: #E0B800">
              <h3><span class="gw-way-dot"></span>Comptoir</h3>
              <div class="gw-howto-row"><span>Achat immédiat</span><Gw2Coins :value="info.tradingPost.buy" /></div>
              <div class="gw-howto-row"><span>Vente immédiate</span><Gw2Coins :value="info.tradingPost.sell" /></div>
              <div v-if="use?.missing && info.tradingPost.buy" class="gw-howto-row font-semibold">
                <span>Acheter les {{ use.missing.toLocaleString('fr-FR') }} manquants</span><Gw2Coins :value="info.tradingPost.buy * use.missing" />
              </div>
            </section>

            <!-- Recipe: crafting, Mystic Forge or merchant -->
            <section v-if="info?.recipe" class="gw-howto-card" :style="{ '--way': RECIPE_COLORS[info.recipe.source] }">
              <h3><span class="gw-way-dot"></span>{{ recipeTitle }}</h3>
              <p v-if="info.recipe.output > 1" class="text-xs text-theme-text-muted mb-2">Donne {{ info.recipe.output }} unités par fabrication.</p>
              <ul class="space-y-1">
                <li v-for="ing in info.recipe.ingredients" :key="`${ing.type}:${ing.id}`">
                  <!-- Gold asked by a merchant: a price, nothing to open -->
                  <div v-if="ing.type === 'currency' && ing.id === COIN" class="gw-howto-row px-1.5">
                    <span>Prix{{ info.recipe.output > 1 ? ` les ${info.recipe.output}` : ' unitaire' }}</span>
                    <Gw2Coins :value="ing.count" />
                  </div>
                  <button v-else @click="open(ing.type, ing.id)" class="gw-howto-ingredient">
                    <img v-if="meta(ing)?.icon" :src="meta(ing).icon" alt="" class="w-7 h-7 rounded border flex-shrink-0" :style="{ borderColor: rarityColor(meta(ing)?.rarity) }" loading="lazy" />
                    <span class="flex-1 min-w-0 truncate text-left">{{ ing.count.toLocaleString('fr-FR') }} × {{ name(ing) }}</span>
                    <svg class="w-4 h-4 text-theme-text-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>
                </li>
                <li v-if="use?.missing && coinPrice" class="gw-howto-row px-1.5 font-semibold">
                  <span>Pour les {{ use.missing.toLocaleString('fr-FR') }} manquants</span>
                  <Gw2Coins :value="Math.ceil(use.missing / (info.recipe.output || 1)) * coinPrice" />
                </li>
              </ul>
            </section>

            <!-- Achievements rewarding it -->
            <section v-if="info?.achievements?.length" class="gw-howto-card" style="--way: #B679D5">
              <h3><span class="gw-way-dot"></span>Récompense de succès</h3>
              <div v-for="a in info.achievements" :key="a.id" class="mb-2 last:mb-0">
                <div class="flex items-center justify-between gap-2 text-sm">
                  <span class="font-medium" :class="a.done ? 'text-theme-success' : 'text-theme-text'">{{ a.name }}</span>
                  <span class="text-xs tabular-nums text-theme-text-muted">{{ a.done ? '✓' : `${a.current}/${a.max}` }}</span>
                </div>
                <span v-if="!a.done && a.max" class="gw-howto-bar"><span :style="{ width: `${Math.min(a.current, a.max) / a.max * 100}%` }"></span></span>
                <p v-if="a.requirement" class="text-xs text-theme-text-muted mt-0.5">{{ a.requirement }}</p>
              </div>
            </section>

            <!-- Step of the legendary's collection -->
            <section v-if="info?.collectionSteps?.length" class="gw-howto-card" style="--way: #B679D5">
              <h3><span class="gw-way-dot"></span>Étape de collection</h3>
              <p v-for="step in info.collectionSteps" :key="step.id" class="text-sm">
                <span :class="step.done ? 'text-theme-success' : 'text-theme-text-muted'">{{ step.done ? '✓ Validée' : 'Pas encore validée' }}</span>
                dans « {{ step.name }} »
              </p>
            </section>
          </template>

          <!-- In game: hint, description -->
          <section v-if="gameText" class="gw-howto-card" style="--way: #62A4DA">
            <h3><span class="gw-way-dot"></span>{{ current.type === 'currency' ? 'Où la gagner' : 'En jeu' }}</h3>
            <p class="text-sm text-theme-text whitespace-pre-line">{{ gameText }}</p>
            <a v-if="info?.hintSource && info?.hint" :href="info.hintSource" target="_blank" rel="noopener" class="inline-block text-xs text-theme-link hover:underline mt-1">Source : Le Bus Magique</a>
          </section>

          <section v-if="current.type === 'currency'" class="gw-howto-card">
            <div class="gw-howto-row"><span>Dans ton portefeuille</span><span class="font-semibold tabular-nums">{{ (info?.wallet || 0).toLocaleString('fr-FR') }}</span></div>
          </section>

          <!-- Notes -->
          <p v-if="info?.bound" class="text-xs text-theme-text-muted">Lié au compte : ne s'échange pas au comptoir.</p>
          <p v-if="info?.storable" class="text-xs text-theme-text-muted">Se range dans le stockage de matériaux.</p>

          <p v-if="noWay" class="text-sm text-theme-text-muted">Aucune information sur la façon de l'obtenir.</p>
        </div>
      </aside>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { CRAFTING, STOCK_LOCATIONS, rarityColor } from '@/utils/gw2';

const props = defineProps({
  howTo: { type: Object, default: () => ({}) },
  items: { type: Object, default: () => ({}) },
  currencies: { type: Object, default: () => ({}) },
  // "item:ID" => {needed, owned, missing} for the legendary on display
  usage: { type: Object, default: () => ({}) },
});

const RECIPE_COLORS = { crafting: '#62A4DA', mystic_forge: '#8E4FD6', vendor: '#1A9306' };
// Currency 1 is coin (copper)
const COIN = 1;

const stack = ref([]);
const current = computed(() => stack.value[stack.value.length - 1] || null);
const keyOf = (entry) => `${entry.type}:${entry.id}`;
const info = computed(() => (current.value ? props.howTo[keyOf(current.value)] : null));
const use = computed(() => (current.value ? props.usage[keyOf(current.value)] : null));

const meta = (entry) => (entry.type === 'currency' ? props.currencies[entry.id] : props.items[entry.id]);
const name = (entry) => meta(entry)?.name || `#${entry.id}`;

const recipeTitle = computed(() => {
  const recipe = info.value?.recipe;
  if (!recipe) return '';
  if (recipe.source === 'mystic_forge') return 'Forge mystique';
  if (recipe.source === 'vendor') return 'Marchand';
  const disciplines = recipe.disciplines.map(d => CRAFTING[d] || d).join(', ');
  return disciplines ? `Fabrication · ${disciplines}` : 'Fabrication';
});

// The item's own description often just repeats its recipe: only shown when there is none
const coinPrice = computed(() => info.value?.recipe?.ingredients.find(i => i.type === 'currency' && i.id === COIN)?.count || 0);

const gameText = computed(() => info.value?.hint || (current.value?.type === 'item' && !info.value?.recipe ? info.value?.description : null));

const locationsText = computed(() =>
  Object.entries(info.value?.locations || {})
    .filter(([, n]) => n > 0)
    .map(([where, n]) => `${STOCK_LOCATIONS[where] || where} ${n.toLocaleString('fr-FR')}`)
    .join(' · ')
);

const noWay = computed(() => current.value?.type === 'item'
  && !info.value?.tradingPost && !info.value?.recipe && !info.value?.achievements?.length
  && !info.value?.collectionSteps?.length && !gameText.value);

// Opening from the page starts a new path; from an ingredient it goes one level deeper
const open = (type, id, { reset = false } = {}) => {
  const entry = { type, id: Number(id) };
  stack.value = reset ? [entry] : [...stack.value, entry];
};
const back = () => { stack.value = stack.value.slice(0, -1); };
const close = () => { stack.value = []; };

const onKey = (e) => { if (e.key === 'Escape') close(); };
watch(current, (now, before) => {
  if (now && !before) window.addEventListener('keydown', onKey);
  if (!now) window.removeEventListener('keydown', onKey);
});
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));

defineExpose({ open });
</script>

<style scoped>
.gw-howto-backdrop {
  position: fixed;
  inset: 0;
  z-index: 70;
  background-color: rgba(0, 0, 0, 0.45);
  display: flex;
  justify-content: flex-end;
}
.gw-howto {
  width: min(28rem, 100vw);
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: var(--card);
  border-left: 1px solid var(--border);
}
.gw-howto-head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.1rem;
  border-bottom: 1px solid var(--border);
}
.gw-howto-img {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.5rem;
  border: 2px solid var(--rarity, var(--border));
  flex-shrink: 0;
}
.gw-howto-icon-btn {
  padding: 0.35rem;
  border-radius: 0.5rem;
  color: var(--text-muted);
  flex-shrink: 0;
}
.gw-howto-icon-btn:hover {
  color: var(--text);
  background-color: rgba(var(--primary-rgb), 0.08);
}
.gw-howto-crumbs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.5rem 1.1rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border);
}
.gw-howto-body {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.1rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}
.gw-howto-card {
  padding: 0.85rem 1rem;
  border-radius: 0.85rem;
  border: 1px solid var(--border);
  border-left: 3px solid var(--way, var(--border));
}
.gw-howto-card h3 {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text);
  margin-bottom: 0.6rem;
}
.gw-way-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 9999px;
  background-color: var(--way);
}
.gw-howto-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  font-size: 0.875rem;
  color: var(--text);
  padding: 0.15rem 0;
}
.gw-howto-num {
  font-size: 1.25rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--text);
}
.gw-howto-label {
  font-size: 0.7rem;
  color: var(--text-muted);
}
.gw-howto-ingredient {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  padding: 0.35rem 0.4rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: var(--text);
}
.gw-howto-ingredient:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.gw-howto-bar {
  display: block;
  height: 0.3rem;
  margin-top: 0.25rem;
  border-radius: 9999px;
  background-color: var(--border);
  overflow: hidden;
}
.gw-howto-bar > span {
  display: block;
  height: 100%;
  background-color: #B679D5;
}
</style>
