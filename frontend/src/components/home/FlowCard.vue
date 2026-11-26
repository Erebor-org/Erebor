<template>
  <DashCard title="Mouvements" :loading="loading">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" /></svg>
    </template>

    <p class="flow-period">Ces 30 derniers jours</p>
    <div class="flow-kpis">
      <div class="flow-kpi">
        <span class="flow-kpi-value is-in">{{ flow.entries }}</span>
        <span class="flow-kpi-label">{{ flow.entries > 1 ? 'arrivées' : 'arrivée' }}</span>
      </div>
      <div class="flow-kpi">
        <span class="flow-kpi-value is-out">{{ flow.exits }}</span>
        <span class="flow-kpi-label">{{ flow.exits > 1 ? 'départs' : 'départ' }}</span>
      </div>
      <div class="flow-kpi">
        <span class="flow-kpi-sentence" :class="flow.net > 0 ? 'is-net-up' : flow.net < 0 ? 'is-net-down' : ''">
          {{ balanceSentence }}
        </span>
      </div>
    </div>

    <p class="flow-period">Mois par mois, sur un an</p>
    <MonthlyFlowChart :monthly="monthly" />

    <p v-if="undatedDepartures > 0" class="flow-note">
      {{ undatedDepartures }} départ{{ undatedDepartures > 1 ? 's' : '' }} plus ancien{{ undatedDepartures > 1 ? 's' : '' }}, sans date connue,
      n'apparai{{ undatedDepartures > 1 ? 'ssent' : 't' }} pas ici.
    </p>
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import MonthlyFlowChart from './MonthlyFlowChart.vue';
import { computed } from 'vue';

const props = defineProps({
  flow: { type: Object, default: () => ({ entries: 0, exits: 0, net: 0 }) },
  monthly: { type: Array, default: () => [] },
  undatedDepartures: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
});

// « La guilde a gagné 3 membres » / « perdu 2 membres » / « autant d'arrivées que de départs ».
const balanceSentence = computed(() => {
  const net = props.flow.net;
  if (net === 0) return "Autant d'arrivées que de départs";
  const n = Math.abs(net);
  return `La guilde a ${net > 0 ? 'gagné' : 'perdu'} ${n} membre${n > 1 ? 's' : ''}`;
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

.flow-period {
  margin: 0 0 0.4rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.66rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.flow-kpi-sentence {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.3;
  color: var(--text-muted);
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
