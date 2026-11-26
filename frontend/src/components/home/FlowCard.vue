<template>
  <DashCard title="Mouvements" :loading="loading">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
    </template>

    <div class="flow-kpis">
      <div class="flow-kpi">
        <span class="flow-kpi-value is-in">{{ flow.entries ? `+${flow.entries}` : 0 }}</span>
        <span class="flow-kpi-label">entrées · 30 j</span>
      </div>
      <div class="flow-kpi">
        <span class="flow-kpi-value is-out">{{ flow.exits ? `−${flow.exits}` : 0 }}</span>
        <span class="flow-kpi-label">sorties · 30 j</span>
      </div>
      <div class="flow-kpi">
        <span class="flow-kpi-value" :class="flow.net >= 0 ? 'is-net-up' : 'is-net-down'">{{ signed(flow.net) }}</span>
        <span class="flow-kpi-label">solde net</span>
      </div>
    </div>

    <MonthlyFlowChart :monthly="monthly" />

    <p v-if="undatedDepartures > 0" class="flow-note">
      {{ undatedDepartures }} départ{{ undatedDepartures > 1 ? 's' : '' }} antérieur{{ undatedDepartures > 1 ? 's' : '' }}
      au suivi des dates n'apparai{{ undatedDepartures > 1 ? 'ssent' : 't' }} pas dans les sorties.
    </p>
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import MonthlyFlowChart from './MonthlyFlowChart.vue';
import { signed } from '@/utils/dashboardFormat';

defineProps({
  flow: { type: Object, default: () => ({ entries: 0, exits: 0, net: 0 }) },
  monthly: { type: Array, default: () => [] },
  undatedDepartures: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
});
</script>

<style scoped>
.flow-kpis {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.flow-kpi {
  display: flex;
  flex-direction: column;
}

.flow-kpi-value {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 1.5rem;
  line-height: 1.15;
}

.flow-kpi-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.is-in { color: var(--accent); }
.is-out { color: var(--primary); }
.is-net-up { color: var(--success); }
.is-net-down { color: var(--error); }

.flow-note {
  margin: 0.75rem 0 0;
  font-size: 0.72rem;
  color: var(--text-muted);
}
</style>
