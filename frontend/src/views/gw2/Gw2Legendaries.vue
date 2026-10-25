<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <p class="text-xs uppercase tracking-[0.2em] text-theme-text-muted mb-2">Guild Wars 2</p>
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Légendaires</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Suis tes légendaires : ce que tu as déjà, ce qui manque, ce qu'il reste à payer</p>
      </div>

      <!-- Tabs, and refresh -->
      <div class="flex flex-wrap items-center gap-2 mb-6">
        <div class="flex flex-wrap gap-2" role="tablist">
          <button
            v-for="t in TABS"
            :key="t.key"
            role="tab"
            :aria-selected="tab === t.key"
            @click="setTab(t.key)"
            class="gw-tab"
            :class="{ 'gw-tab--active': tab === t.key }"
          >
            {{ t.label }}
            <span v-if="t.key === 'suivis' && trackedIds.length" class="gw-tab-count">{{ trackedIds.length }}</span>
          </button>
        </div>
        <div class="flex items-center gap-3 ml-auto">
          <span v-if="error && current" class="text-xs text-theme-error">{{ error }}</span>
          <span v-else-if="loadedAt" class="text-xs text-theme-text-muted">Mis à jour à {{ loadedAt }}</span>
          <button
            @click="load({ refresh: true })"
            :disabled="isLoading"
            class="inline-flex items-center gap-2 px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium disabled:opacity-50 disabled:cursor-wait"
            title="Relire ton stock et les prix depuis Guild Wars 2"
          >
            <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading && current }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            Actualiser
          </button>
        </div>
      </div>

      <div v-if="isLoading && !current" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        Calcul des arbres de fabrication...
      </div>
      <div v-else-if="error && !current" class="glass-card rounded-2xl text-center py-16 px-4">
        <p class="text-theme-error font-medium mb-3">{{ error }}</p>
        <button @click="load()" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
      </div>

      <!-- My tracked legendaries -->
      <template v-else-if="tab === 'suivis' && current">
        <div v-if="current.conflicts.length" class="gw-alert mb-6">
          <p class="font-semibold mb-1">Ressources disputées entre tes suivis</p>
          <p class="text-sm mb-2">Chaque suivi compte ton stock comme s'il était seul. Pour ces objets, ils en réclament plus que tu n'en as : la <button class="underline" @click="setTab('combine')">vue combinée</button> les répartit.</p>
          <ul class="text-sm space-y-0.5">
            <li v-for="c in current.conflicts" :key="c.id">
              <span class="font-medium">{{ current.items[c.id]?.name || `#${c.id}` }}</span> : tu en as {{ c.owned.toLocaleString('fr-FR') }},
              {{ Object.entries(c.usedBy).map(([id, n]) => `${current.items[id]?.name || id} en utilise ${n.toLocaleString('fr-FR')}`).join(', ') }}
            </li>
          </ul>
        </div>

        <div v-if="current.legendaries.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <Gw2LegendaryCard
            v-for="l in current.legendaries"
            :key="l.id"
            :legendary="{ ...l, tracked: true }"
            :items="current.items"
            :currencies="current.currencies"
            :busy="busyId === l.id"
            @toggle-track="toggleTrack"
          />
        </div>
        <div v-else class="glass-card rounded-2xl text-center py-16 px-4">
          <p class="text-lg font-serif font-bold text-theme-text mb-2">Choisis ton premier légendaire</p>
          <p class="text-theme-text-muted mb-5">Le catalogue montre pour chacun où tu en es avec ce que tu possèdes déjà.</p>
          <button @click="setTab('catalogue')" class="px-5 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl transition-colors duration-200">Ouvrir le catalogue</button>
        </div>
      </template>

      <!-- Catalogue -->
      <template v-else-if="tab === 'catalogue' && current">
        <!-- Two rows: what to look for (search, type, sort), then the narrowing toggles -->
        <div class="glass-card rounded-2xl p-4 mb-6 flex flex-col gap-3">
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              v-model="search"
              placeholder="Rechercher un légendaire..."
              class="flex-1 min-w-0 bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary"
            />
            <select v-model="equipmentType" class="bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2 px-3 text-sm" aria-label="Type d'équipement">
              <option value="all">Tous les types</option>
              <option v-for="t in equipmentTypes" :key="t.key" :value="t.key">{{ t.label }} ({{ t.count }})</option>
            </select>
            <select v-model="sortKey" class="bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2 px-3 text-sm" aria-label="Tri">
              <option value="progress">Les plus avancés</option>
              <option value="cost">Les moins chers à finir</option>
              <option value="name">Par nom</option>
            </select>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <button v-for="g in groupFilters" :key="g.key" @click="group = g.key" class="gw-filter" :class="{ 'gw-filter--active': group === g.key }">{{ g.label }}</button>
            <label class="inline-flex items-center gap-2 text-sm text-theme-text cursor-pointer whitespace-nowrap sm:ml-auto">
              <input type="checkbox" v-model="hideUnlocked" class="accent-[var(--primary)]" />
              Masquer les débloqués
            </label>
          </div>
        </div>

        <div v-if="catalogRows.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <Gw2LegendaryCard
            v-for="l in catalogRows"
            :key="l.id"
            :legendary="l"
            :items="current.items"
            :currencies="current.currencies"
            mode="catalog"
            :busy="busyId === l.id"
            @toggle-track="toggleTrack"
          />
        </div>
        <div v-else class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">Aucun légendaire ne correspond</div>
      </template>

      <!-- Combined view -->
      <template v-else-if="tab === 'combine' && current">
        <div v-if="!current.legendaries.length" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
          Suis au moins un légendaire pour voir la vue combinée
        </div>
        <template v-else>
          <p class="text-sm text-theme-text-muted mb-4">
            Ton stock est réparti entre tes suivis dans l'ordre où tu les as ajoutés : le premier se sert d'abord.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass-card rounded-2xl p-5">
              <p class="text-xs uppercase tracking-wide text-theme-text-muted mb-1">Reste à acheter pour tout finir</p>
              <p class="text-2xl font-bold text-theme-text"><Gw2Coins :value="current.totalCost" /></p>
            </div>
            <div class="glass-card rounded-2xl p-5 md:col-span-2">
              <div v-for="l in current.legendaries" :key="l.id" class="flex items-center gap-3 py-1.5">
                <RouterLink :to="`/gw2/legendaires/${l.id}`" class="w-44 truncate font-medium text-theme-text hover:text-theme-primary">{{ current.items[l.id]?.name }}</RouterLink>
                <span class="flex-1 h-1.5 rounded-full bg-theme-border overflow-hidden"><span class="block h-full rounded-full" style="background-image: linear-gradient(90deg, var(--primary), var(--accent))" :style="{ width: `${Math.floor(l.progress * 100)}%` }"></span></span>
                <span class="w-12 text-right text-sm tabular-nums">{{ Math.floor(l.progress * 100) }} %</span>
                <span class="w-32 text-right text-sm"><Gw2Coins :value="l.cost" /></span>
              </div>
            </div>
          </div>
          <Gw2ShoppingList :rows="current.shoppingList" :items="current.items" :currencies="current.currencies" />
        </template>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Gw2LegendaryCard from '@/components/gw2/Gw2LegendaryCard.vue';
import Gw2ShoppingList from '@/components/gw2/Gw2ShoppingList.vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import {
  fetchGw2TrackedLegendaries, fetchGw2LegendaryCatalog, fetchGw2LegendaryCombined,
  trackGw2Legendary, untrackGw2Legendary, gw2ErrorMessage,
} from '@/services/gw2Api';
import { GENERATIONS, WEAPON_TYPES, TRINKET_TYPES, ITEM_TYPES } from '@/utils/gw2';

const TABS = [
  { key: 'suivis', label: 'Mes suivis' },
  { key: 'catalogue', label: 'Catalogue' },
  { key: 'combine', label: 'Vue combinée' },
];
const FETCHERS = {
  suivis: fetchGw2TrackedLegendaries,
  catalogue: fetchGw2LegendaryCatalog,
  combine: fetchGw2LegendaryCombined,
};

const route = useRoute();
const router = useRouter();

// The tab lives in the URL (?onglet=) so a reload or a shared link keeps it
const tab = computed(() => (FETCHERS[route.query.onglet] ? route.query.onglet : 'suivis'));
const setTab = (key) => router.replace({ query: { ...route.query, onglet: key } });

const data = ref({});
const isLoading = ref(false);
const error = ref('');
const loadedAt = ref('');
const busyId = ref(null);
const trackedIds = ref([]);

const current = computed(() => data.value[tab.value] || null);

const load = async ({ refresh = false } = {}) => {
  const key = tab.value;
  isLoading.value = true;
  error.value = '';
  try {
    data.value = { ...data.value, [key]: await FETCHERS[key]({ refresh }) };
    loadedAt.value = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    if (key === 'suivis') trackedIds.value = data.value.suivis.legendaries.map(l => l.id);
    if (key === 'catalogue') trackedIds.value = data.value.catalogue.legendaries.filter(l => l.tracked).map(l => l.id);
  } catch (e) {
    error.value = gw2ErrorMessage(e, 'Impossible de calculer les légendaires');
  } finally {
    isLoading.value = false;
  }
};

const toggleTrack = async (legendary) => {
  busyId.value = legendary.id;
  try {
    const { tracked } = legendary.tracked ? await untrackGw2Legendary(legendary.id) : await trackGw2Legendary(legendary.id);
    trackedIds.value = tracked;
    // Other tabs now show stale data: drop them, refetch the current one
    const keep = tab.value === 'catalogue' ? { catalogue: data.value.catalogue } : {};
    if (keep.catalogue) {
      keep.catalogue = { ...keep.catalogue, legendaries: keep.catalogue.legendaries.map(l => ({ ...l, tracked: tracked.includes(l.id) })) };
    }
    data.value = keep;
    if (tab.value !== 'catalogue') await load();
  } catch (e) {
    error.value = gw2ErrorMessage(e, 'Impossible de modifier le suivi');
  } finally {
    busyId.value = null;
  }
};

// Catalogue filters
const search = ref('');
const group = ref('all');
const hideUnlocked = ref(false);
const equipmentType = ref('all');
const sortKey = ref('progress');
// Weapons by generation, and trinkets
const isTrinket = (l) => data.value.catalogue?.items[l.id]?.type === 'Trinket';
const groupFilters = [
  { key: 'all', label: 'Toutes', match: () => true },
  ...Object.entries(GENERATIONS).filter(([g]) => g !== '2').map(([g, v]) => ({
    key: `gen${g}`, label: `Armes ${v.label.toLowerCase()}`, match: (l) => !isTrinket(l) && l.generation === Number(g),
  })),
  { key: 'trinkets', label: 'Bijoux', match: isTrinket },
];

// Equipment type of a legendary: weapon or trinket type (Espadon, Anneau...), or the item type otherwise
const equipmentTypeOf = (l) => {
  const meta = data.value.catalogue?.items[l.id];
  return ['Weapon', 'Trinket'].includes(meta?.type) ? meta.subType : meta?.type;
};
const equipmentTypes = computed(() => {
  const catalog = data.value.catalogue;
  if (!catalog) return [];
  const counts = {};
  catalog.legendaries.forEach(l => {
    const key = equipmentTypeOf(l);
    if (key) counts[key] = (counts[key] || 0) + 1;
  });
  return Object.entries(counts)
    .map(([key, count]) => ({ key, count, label: WEAPON_TYPES[key] || TRINKET_TYPES[key] || ITEM_TYPES[key] || key }))
    .sort((a, b) => a.label.localeCompare(b.label, 'fr'));
});

const catalogRows = computed(() => {
  const catalog = data.value.catalogue;
  if (!catalog) return [];
  const query = search.value.trim().toLowerCase();
  const nameOf = (l) => catalog.items[l.id]?.name || '';
  return catalog.legendaries
    .filter(l => groupFilters.find(g => g.key === group.value).match(l))
    .filter(l => !hideUnlocked.value || !l.unlocked)
    .filter(l => equipmentType.value === 'all' || equipmentTypeOf(l) === equipmentType.value)
    .filter(l => !query || nameOf(l).toLowerCase().includes(query))
    .sort((a, b) => {
      if (sortKey.value === 'name') return nameOf(a).localeCompare(nameOf(b));
      if (sortKey.value === 'cost') return a.cost - b.cost;
      return b.progress - a.progress || a.cost - b.cost;
    });
});

watch(tab, () => {
  if (!data.value[tab.value]) load();
});
onMounted(load);
</script>

<style scoped>
.gw-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.1rem;
  border-radius: 0.75rem;
  font-weight: 600;
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
.gw-filter {
  padding: 0.3rem 0.8rem;
  border-radius: 9999px;
  font-size: 0.85rem;
  border: 1px solid var(--border);
  color: var(--text);
  transition: all 0.2s;
}
.gw-filter--active {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.1);
  color: var(--primary);
}
.gw-alert {
  padding: 1rem 1.25rem;
  border-radius: 1rem;
  border: 1px solid color-mix(in srgb, var(--warning) 45%, transparent);
  background-color: color-mix(in srgb, var(--warning) 8%, transparent);
  color: var(--text);
}
</style>
