<template>
  <!-- Everything a legendary (or several) needs, one row per item/currency, sortable -->
  <div>
    <div class="flex flex-wrap items-center gap-3 mb-3">
      <label class="inline-flex items-center gap-2 text-sm text-theme-text cursor-pointer">
        <input type="checkbox" v-model="missingOnly" class="accent-[var(--primary)]" />
        Seulement ce qui manque
      </label>
      <span class="text-sm text-theme-text-muted ml-auto">
        {{ visibleRows.length }} ligne{{ visibleRows.length > 1 ? 's' : '' }} · à acheter ≈ <Gw2Coins :value="totalCost" />
      </span>
    </div>

    <div class="glass-card rounded-2xl overflow-x-auto">
      <table class="gw-table">
        <thead>
          <tr>
            <th v-for="col in columns" :key="col.key" :class="col.class" @click="sortBy(col.key)" :aria-sort="sort.key === col.key ? (sort.dir > 0 ? 'ascending' : 'descending') : 'none'">
              {{ col.label }}<span v-if="sort.key === col.key" class="ml-1">{{ sort.dir > 0 ? '▲' : '▼' }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in visibleRows" :key="`${row.type}:${row.id}`" :class="{ 'gw-row-done': row.missing === 0 }">
            <td>
              <span class="flex items-center gap-2.5 min-w-0">
                <img v-if="metaOf(row)?.icon" :src="metaOf(row).icon" :alt="nameOf(row)" class="gw-cell-icon" :style="{ '--rarity': rarityColor(metaOf(row)?.rarity) }" loading="lazy" />
                <span class="min-w-0">
                  <span class="block font-medium truncate" :style="{ color: row.type === 'item' ? rarityColor(metaOf(row)?.rarity) : 'var(--text)' }">{{ nameOf(row) }}</span>
                  <span class="block text-xs" :style="{ color: PLAN_DECISIONS[row.decision]?.color }">{{ decisionLabel(row) }}</span>
                </span>
              </span>
            </td>
            <td class="text-right tabular-nums" :title="locationsText(row)">
              {{ row.owned.toLocaleString('fr-FR') }}
              <span v-if="locationsText(row)" class="block text-[0.7rem] text-theme-text-muted whitespace-nowrap">{{ locationsText(row) }}</span>
            </td>
            <td class="text-right tabular-nums">{{ row.needed.toLocaleString('fr-FR') }}</td>
            <td class="text-right tabular-nums font-semibold" :class="row.missing ? 'text-theme-text' : 'text-theme-success'">
              {{ row.missing ? row.missing.toLocaleString('fr-FR') : '✓' }}
            </td>
            <td class="text-right"><Gw2Coins v-if="row.unitPrice" :value="row.unitPrice" /><span v-else class="text-theme-text-muted">—</span></td>
            <td class="text-right font-semibold"><Gw2Coins v-if="row.cost" :value="row.cost" /><span v-else class="text-theme-text-muted">—</span></td>
          </tr>
          <tr v-if="!visibleRows.length">
            <td colspan="6" class="text-center py-8 text-theme-text-muted">Rien ne manque</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Gw2Coins from '@/components/gw2/Gw2Coins.vue';
import { PLAN_DECISIONS, STOCK_LOCATIONS, rarityColor } from '@/utils/gw2';

const props = defineProps({
  rows: { type: Array, required: true },
  items: { type: Object, default: () => ({}) },
  currencies: { type: Object, default: () => ({}) },
});

const missingOnly = ref(true);
const sort = ref({ key: 'cost', dir: -1 });

const columns = [
  { key: 'name', label: 'Objet', class: 'text-left' },
  { key: 'owned', label: 'Possédé', class: 'text-right' },
  { key: 'needed', label: 'Requis', class: 'text-right' },
  { key: 'missing', label: 'Manque', class: 'text-right' },
  { key: 'unitPrice', label: 'Prix unitaire', class: 'text-right' },
  { key: 'cost', label: 'Coût', class: 'text-right' },
];

const metaOf = (row) => (row.type === 'currency' ? props.currencies[row.id] : props.items[row.id]);
const nameOf = (row) => metaOf(row)?.name || `#${row.id}`;

const decisionLabel = (row) => {
  if (row.missing === 0) return 'Possédé';
  if (row.decision === 'buy') return 'À acheter au comptoir';
  if (row.decision === 'objective') return row.type === 'currency' ? 'À gagner en jeu' : 'Objectif : à obtenir en jeu';
  return PLAN_DECISIONS[row.decision]?.label || row.decision;
};

const locationsText = (row) =>
  Object.entries(row.locations || {})
    .filter(([, count]) => count > 0)
    .map(([where, count]) => `${STOCK_LOCATIONS[where] || where} ${count.toLocaleString('fr-FR')}`)
    .join(' · ');

const sortBy = (key) => {
  sort.value = sort.value.key === key ? { key, dir: -sort.value.dir } : { key, dir: key === 'name' ? 1 : -1 };
};

const visibleRows = computed(() => {
  const rows = props.rows.filter(r => !missingOnly.value || r.missing > 0);
  const { key, dir } = sort.value;
  const value = (r) => (key === 'name' ? nameOf(r) : r[key] ?? -1);
  return [...rows].sort((a, b) => {
    const [x, y] = [value(a), value(b)];
    return (typeof x === 'string' ? x.localeCompare(y) : x - y) * dir;
  });
});

const totalCost = computed(() => visibleRows.value.reduce((sum, r) => sum + (r.cost || 0), 0));
</script>

<style scoped>
.gw-table {
  width: 100%;
  min-width: 640px;
  border-collapse: collapse;
  font-size: 0.875rem;
}
.gw-table th {
  padding: 0.7rem 1rem;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  font-weight: 700;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  user-select: none;
  white-space: nowrap;
}
.gw-table td {
  padding: 0.55rem 1rem;
  border-bottom: 1px solid var(--border);
  color: var(--text);
}
.gw-table tbody tr:last-child td {
  border-bottom: none;
}
.gw-table tbody tr:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}
.gw-row-done td {
  opacity: 0.65;
}
.gw-cell-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.4rem;
  border: 1px solid var(--rarity, var(--border));
  flex-shrink: 0;
}
</style>
