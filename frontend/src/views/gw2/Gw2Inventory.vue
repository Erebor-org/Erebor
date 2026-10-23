<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <p class="text-xs uppercase tracking-[0.2em] text-theme-text-muted mb-2">Guild Wars 2</p>
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Inventaire & banque</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Ce que tu transportes et ce qui dort en banque : quoi vendre, quoi recycler, quoi garder</p>
      </div>

      <Gw2KeyStatus />

      <div v-if="isLoading" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        Chargement de l'inventaire et de la banque...
      </div>
      <div v-else-if="loadError" class="glass-card rounded-2xl text-center py-16 px-4">
        <p class="text-theme-error font-medium mb-3">{{ loadError }}</p>
        <button @click="load" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
      </div>

      <template v-else-if="data">
        <!-- Inventory / bank tabs, and refresh -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <div class="flex gap-2" role="tablist">
            <button
              v-for="t in tabs"
              :key="t.key"
              role="tab"
              :aria-selected="tab === t.key"
              @click="selectTab(t.key)"
              class="gw-tab"
              :class="{ 'gw-tab--active': tab === t.key }"
            >
              {{ t.label }}
              <span class="gw-tab-count">{{ t.count }}</span>
            </button>
          </div>

          <div class="flex items-center gap-3 ml-auto">
            <span v-if="refreshError" class="text-xs text-theme-error">{{ refreshError }}</span>
            <span v-else-if="loadedAt" class="text-xs text-theme-text-muted">Mis à jour à {{ loadedAt }}</span>
            <button
              @click="refresh"
              :disabled="isRefreshing"
              class="inline-flex items-center gap-2 px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium disabled:opacity-50 disabled:cursor-wait"
              title="Relire l'inventaire, la banque et les prix depuis Guild Wars 2"
            >
              <svg class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              {{ isRefreshing ? 'Actualisation...' : 'Actualiser' }}
            </button>
          </div>
        </div>

        <!-- Where: every character, one character or the shared slots -->
        <div v-if="tab === 'inventory'" class="flex flex-wrap gap-2 mb-6">
          <button
            v-for="p in places"
            :key="p.key"
            @click="place = p.key"
            class="gw-filter"
            :class="{ 'gw-filter--active': place === p.key }"
          >
            {{ p.label }}
            <span class="text-theme-text-muted text-xs">{{ p.detail }}</span>
          </button>
        </div>
        <p v-else class="text-sm text-theme-text-muted mb-6">
          {{ data.bank.used }} emplacements occupés sur {{ data.bank.slots }}
        </p>

        <!-- One card per advice: count, value, and a click filters the list -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
          <button
            v-for="group in summary"
            :key="group.action"
            @click="actionFilter = actionFilter === group.action ? null : group.action"
            class="gw-summary"
            :class="{ 'gw-summary--active': actionFilter === group.action, 'gw-summary--dim': actionFilter && actionFilter !== group.action }"
            :style="{ '--action': ADVICE_ACTIONS[group.action].color }"
            :aria-pressed="actionFilter === group.action"
          >
            <span class="gw-summary-label">{{ ADVICE_ACTIONS[group.action].label }}</span>
            <span class="gw-summary-count">{{ group.rows.length }} <span class="text-sm font-medium text-theme-text-muted">objet{{ group.rows.length > 1 ? 's' : '' }}</span></span>
            <span class="gw-summary-value">
              <template v-if="group.action === 'keep'">Rien à faire</template>
              <template v-else-if="group.total">
                <template v-if="group.action === 'salvage'">≈ </template><Gw2Coins :value="group.total" />
                <template v-if="group.action === 'salvage' && group.unvalued"> + matériaux</template>
              </template>
              <template v-else-if="group.rows.length && group.action === 'salvage'">Matériaux</template>
              <template v-else>—</template>
            </span>
          </button>
        </div>

        <!-- How the advice is computed -->
        <details class="gw-rules mb-6">
          <summary>Comment c'est calculé ?</summary>
          <ul>
            <li><strong>Prix instantané</strong> : le meilleur ordre d'achat du comptoir, moins {{ Math.round(data.rules.fees * 100) }} % de taxes (5 % de mise en vente + 10 % de vente). C'est ce que tu touches vraiment en vendant tout de suite.</li>
            <li><strong>Recyclage des rares et exotiques niv. {{ data.rules.ectoMinLevel }}+</strong> : estimé à {{ formatRate(data.rules.ectoPerRare) }} ecto par rare et {{ formatRate(data.rules.ectoPerExotic) }} par exotique (moyennes avec un nécessaire de maître ou mystique), au prix instantané de l'ecto : <Gw2Coins :value="data.ecto.net" />. On garde l'option qui rapporte le plus.</li>
            <li><strong>Équipement plus faible</strong> : recyclé s'il vaut moins de <Gw2Coins :value="data.rules.lowValueThreshold" /> au comptoir comme au marchand (ses matériaux valent plus), sinon vendu au meilleur prix.</li>
            <li><strong>Objets liés</strong> (à l'âme ou au compte) : invendables au comptoir. Recyclés si possible, sinon marchand ou à garder.</li>
            <li>Les objets inutiles vont au marchand ; les matériaux, conteneurs, élevés et légendaires sont à garder.</li>
            <li>Les données sont gardées 5 minutes ; « Actualiser » les relit tout de suite. Guild Wars 2 peut lui-même mettre quelques minutes à refléter un changement en jeu (souvent à la déconnexion du personnage).</li>
          </ul>
        </details>

        <div v-if="visibleGroups.length === 0" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
          {{ tab === 'bank' ? 'La banque est vide' : 'Aucun objet ici' }}
        </div>

        <!-- Items, grouped by advice -->
        <section v-for="group in visibleGroups" :key="group.action" class="mb-6">
          <div class="flex items-center gap-3 mb-3 px-1">
            <span class="gw-action-dot" :style="{ '--action': ADVICE_ACTIONS[group.action].color }"></span>
            <h2 class="font-serif font-bold text-lg text-theme-text">{{ ADVICE_ACTIONS[group.action].label }}</h2>
            <span class="text-xs text-theme-text-muted">{{ group.rows.length }}</span>
            <div class="flex-1 h-px bg-theme-border"></div>
          </div>

          <div class="glass-card rounded-2xl">
            <div v-for="row in group.rows" :key="row.key" class="gw-row">
              <Gw2ItemTooltip :item="row">
                <span class="gw-icon" :style="{ '--rarity': rarityColor(row.rarity) }">
                  <img v-if="row.icon" :src="row.icon" :alt="row.name" loading="lazy" />
                  <span v-if="row.count > 1" class="gw-icon-count">{{ row.count }}</span>
                </span>
              </Gw2ItemTooltip>

              <div class="flex-1 min-w-0">
                <p class="font-semibold truncate" :style="{ color: rarityColor(row.rarity) }">{{ row.name || `Objet #${row.id}` }}</p>
                <p class="text-xs text-theme-text-muted">{{ row.advice.reason }}</p>
                <p v-if="tab === 'inventory' && place === 'all'" class="text-xs text-theme-text-muted mt-0.5">
                  <span class="gw-where">{{ row.character || 'Partagé' }}</span>
                </p>
              </div>

              <div class="gw-values">
                <div v-if="row.total !== null" class="gw-values-main" :style="{ color: ADVICE_ACTIONS[row.advice.action].color }">
                  <template v-if="row.advice.action === 'salvage'">≈ </template><Gw2Coins :value="row.total" />
                </div>
                <div v-else-if="row.advice.action === 'salvage'" class="gw-values-main" :style="{ color: ADVICE_ACTIONS.salvage.color }">Matériaux</div>
                <!-- The outlets compared, per unit; the chosen one stands out -->
                <div class="gw-values-compare">
                  <span v-if="row.advice.unit.sell !== null" :class="{ 'gw-chosen': row.advice.action === 'sell' }" :title="`Prix instantané brut : ${coinsText(row.advice.unit.sellGross)}, après taxes : ${coinsText(row.advice.unit.sell)}`">
                    Comptoir <Gw2Coins :value="row.advice.unit.sell" />
                  </span>
                  <span v-else-if="row.advice.tradable" class="opacity-70" :title="`Prix de vente le plus bas : ${coinsText(row.price?.sell)}`">Aucun ordre d'achat</span>
                  <span v-else-if="row.price" class="opacity-70" title="Cet exemplaire est lié : il ne peut pas être mis en vente">Lié</span>
                  <span v-if="row.advice.unit.vendor !== null" :class="{ 'gw-chosen': row.advice.action === 'vendor' }">
                    Marchand <Gw2Coins :value="row.advice.unit.vendor" />
                  </span>
                  <span v-if="row.advice.unit.salvage !== null" :class="{ 'gw-chosen': row.advice.action === 'salvage' }">
                    Ectos ≈ <Gw2Coins :value="row.advice.unit.salvage" />
                  </span>
                  <span v-if="row.count > 1 && hasUnitValue(row)" class="opacity-70">/ unité</span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Gw2KeyStatus from '@/components/gw2/Gw2KeyStatus.vue';
import Gw2ItemTooltip from '@/components/gw2/Gw2ItemTooltip.vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { fetchGw2Inventory, gw2ErrorMessage } from '@/services/gw2Api';
import { ADVICE_ACTIONS, rarityColor, splitCoins } from '@/utils/gw2';

const GEAR_TYPES = ['Armor', 'Weapon', 'Trinket', 'Back'];

const data = ref(null);
const isLoading = ref(true);
const loadError = ref('');
const isRefreshing = ref(false);
const refreshError = ref('');
const loadedAt = ref('');

const timeNow = () => new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

const tab = ref('inventory');
const place = ref('all');
const actionFilter = ref(null);

const load = async () => {
  isLoading.value = true;
  loadError.value = '';
  try {
    data.value = await fetchGw2Inventory();
    loadedAt.value = timeNow();
  } catch (error) {
    loadError.value = gw2ErrorMessage(error, "Impossible de charger l'inventaire et la banque");
  } finally {
    isLoading.value = false;
  }
};

// Reads everything again from GW2 (server cache bypassed); the current list stays on screen meanwhile
const refresh = async () => {
  isRefreshing.value = true;
  refreshError.value = '';
  try {
    data.value = await fetchGw2Inventory({ refresh: true });
    loadedAt.value = timeNow();
  } catch (error) {
    refreshError.value = gw2ErrorMessage(error, "Impossible d'actualiser");
  } finally {
    isRefreshing.value = false;
  }
};

const selectTab = (key) => {
  tab.value = key;
  actionFilter.value = null;
};

const formatRate = (rate) => rate.toLocaleString('fr-FR');
const coinsText = (copper) => {
  if (copper === null || copper === undefined) return '—';
  const { gold, silver, copper: c } = splitCoins(copper);
  return [gold && `${gold} po`, silver && `${silver} pa`, (c || (!gold && !silver)) && `${c} pc`].filter(Boolean).join(' ');
};
const hasUnitValue = (row) => ['sell', 'vendor', 'salvage'].some(k => row.advice.unit[k] !== null);

// Identical stacks in the same place are shown as one row (gear is never merged: stats can differ)
const rowsOf = (slots) => {
  const rows = new Map();
  slots.forEach((slot, i) => {
    const mergeable = !GEAR_TYPES.includes(slot.itemType);
    const key = mergeable ? `${slot.location}|${slot.character}|${slot.id}|${slot.binding}` : `slot-${i}`;
    const row = rows.get(key);
    if (row) {
      row.count += slot.count;
    } else {
      rows.set(key, { ...slot, key });
    }
  });

  return [...rows.values()].map(row => {
    const unit = row.advice.unit[row.advice.action];
    return { ...row, total: unit === null || unit === undefined ? null : Math.round(unit * row.count) };
  });
};

const tabSlots = computed(() => {
  const slots = data.value?.slots || [];
  return tab.value === 'bank'
    ? slots.filter(s => s.location === 'bank')
    : slots.filter(s => s.location !== 'bank');
});

const tabs = computed(() => [
  { key: 'inventory', label: 'Inventaire', count: (data.value?.slots || []).filter(s => s.location !== 'bank').length },
  { key: 'bank', label: 'Banque', count: (data.value?.slots || []).filter(s => s.location === 'bank').length },
]);

const places = computed(() => {
  const inventorySlots = (data.value?.slots || []).filter(s => s.location !== 'bank');
  return [
    { key: 'all', label: 'Tout', detail: inventorySlots.length },
    ...(data.value?.characters || []).map(c => ({ key: c.name, label: c.name, detail: `${c.used}/${c.slots}` })),
    { key: 'shared', label: 'Partagé', detail: `${data.value?.shared.used}/${data.value?.shared.slots}` },
  ];
});

const rows = computed(() => {
  let slots = tabSlots.value;
  if (tab.value === 'inventory' && place.value !== 'all') {
    slots = place.value === 'shared'
      ? slots.filter(s => s.location === 'shared')
      : slots.filter(s => s.character === place.value);
  }
  return rowsOf(slots);
});

const summary = computed(() =>
  Object.keys(ADVICE_ACTIONS).map(action => {
    const groupRows = rows.value
      .filter(r => r.advice.action === action)
      .sort((a, b) => (b.total ?? -1) - (a.total ?? -1) || (a.name || '').localeCompare(b.name || ''));
    return {
      action,
      rows: groupRows,
      total: groupRows.reduce((sum, r) => sum + (r.total || 0), 0),
      unvalued: groupRows.some(r => r.total === null),
    };
  })
);

const visibleGroups = computed(() =>
  summary.value.filter(g => g.rows.length && (!actionFilter.value || g.action === actionFilter.value))
);

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
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
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

.gw-summary {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.2rem;
  padding: 0.9rem 1rem;
  border-radius: 1rem;
  text-align: left;
  border: 1px solid var(--border);
  border-top: 3px solid var(--action);
  background-color: rgba(var(--bg-rgb), 0.6);
  transition: all 0.2s;
}
.gw-summary:hover {
  background-color: color-mix(in srgb, var(--action) 6%, transparent);
}
.gw-summary--active {
  border-color: var(--action);
  background-color: color-mix(in srgb, var(--action) 10%, transparent);
}
.gw-summary--dim {
  opacity: 0.55;
}
.gw-summary-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--action);
  font-weight: 700;
}
.gw-summary-count {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text);
}
.gw-summary-value {
  font-size: 0.85rem;
  color: var(--text);
}

.gw-rules {
  font-size: 0.85rem;
  color: var(--text-muted);
}
.gw-rules summary {
  cursor: pointer;
  width: fit-content;
  color: var(--link);
}
.gw-rules ul {
  margin-top: 0.5rem;
  padding: 0.85rem 1rem 0.85rem 2rem;
  list-style: disc;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
}
.gw-rules li + li {
  margin-top: 0.35rem;
}
.gw-rules strong {
  color: var(--text);
}

.gw-action-dot {
  width: 0.7rem;
  height: 0.7rem;
  border-radius: 9999px;
  background-color: var(--action);
}

.gw-row {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 0.75rem 1.1rem;
  border-bottom: 1px solid var(--border);
}
.gw-row:last-child {
  border-bottom: none;
}
.gw-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.gw-icon {
  position: relative;
  display: block;
  width: 2.75rem;
  height: 2.75rem;
  flex-shrink: 0;
  border-radius: 0.5rem;
  border: 2px solid var(--rarity);
  overflow: hidden;
  background-color: var(--bg-muted);
}
.gw-icon img {
  width: 100%;
  height: 100%;
}
.gw-icon-count {
  position: absolute;
  top: 0;
  right: 0;
  padding: 0 0.25rem;
  font-size: 0.7rem;
  font-weight: 700;
  line-height: 1.2;
  color: #fff;
  text-shadow: 0 0 2px #000, 0 0 2px #000;
}

.gw-where {
  display: inline-block;
  padding: 0 0.45rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
}

.gw-values {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.2rem;
  flex-shrink: 0;
  text-align: right;
}
.gw-values-main {
  font-weight: 700;
  font-size: 1rem;
}
.gw-values-compare {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.15rem 0.7rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}
.gw-chosen {
  color: var(--text);
  font-weight: 600;
}

@media (max-width: 640px) {
  .gw-row {
    flex-wrap: wrap;
  }
  .gw-values {
    width: 100%;
    align-items: flex-start;
    padding-left: 3.65rem;
    text-align: left;
  }
  .gw-values-compare {
    justify-content: flex-start;
  }
}
</style>
