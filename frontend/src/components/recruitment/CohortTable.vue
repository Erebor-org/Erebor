<template>
  <DashCard title="Recrues mois par mois">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
    </template>
    <p class="ct-rule">Pour les recrues arrivées chaque mois : combien sont restées après 2 semaines, 2 mois et 4 mois. « trop tôt » : le délai n'est pas encore écoulé.</p>

    <div class="ct-scroll">
      <table class="ct-table">
        <thead>
          <tr>
            <th class="ct-left">Arrivées en</th>
            <th>Recrues</th>
            <th>Toujours là</th>
            <th v-for="k in milestones" :key="k">Après {{ milestoneLabel(k) }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in cohorts" :key="c.month" :class="{ 'is-empty-row': !c.recruits }">
            <td class="ct-left ct-month">{{ monthTitle(c.month) }}</td>
            <td class="ct-num">{{ c.recruits }}</td>
            <td class="ct-num">{{ c.recruits ? c.stillHere : '' }}</td>
            <td v-for="k in milestones" :key="k">
              <RateCell v-if="c.recruits" :value="c.retention[`d${k}`]" :milestone="k" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </DashCard>
</template>

<script setup>
import DashCard from '@/components/home/DashCard.vue';
import RateCell from './RateCell.vue';
import { milestoneLabel } from '@/utils/plainLanguage';

defineProps({
  cohorts: { type: Array, default: () => [] },
  milestones: { type: Array, default: () => [14, 61, 122] },
});

const formatter = new Intl.DateTimeFormat('fr-FR', { month: 'short', year: 'numeric' });
const monthTitle = (yyyyMm) => {
  const [y, m] = yyyyMm.split('-').map(Number);
  return formatter.format(new Date(y, m - 1, 1));
};
</script>

<style scoped>
.ct-rule {
  margin: 0 0 0.75rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.ct-scroll {
  max-height: 26rem;
  overflow: auto;
}

.ct-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.ct-table th {
  position: sticky;
  top: 0;
  z-index: 1;
  padding: 0.4rem 0.5rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.66rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  text-align: center;
  color: var(--text-muted);
  background-color: var(--card);
  border-bottom: 1px solid rgba(var(--accent-rgb), 0.25);
}

.ct-table td {
  padding: 0.3rem 0.5rem;
  text-align: center;
  border-bottom: 1px solid var(--border);
}

.ct-left {
  text-align: left !important;
}

.ct-month {
  font-weight: 600;
  color: var(--text);
  text-transform: capitalize;
  white-space: nowrap;
}

.ct-num {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}

.is-empty-row td {
  color: var(--text-muted);
  opacity: 0.6;
}
</style>
