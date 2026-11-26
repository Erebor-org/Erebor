<template>
  <DashCard title="Quand partent-ils ?">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    </template>
    <p class="dh-rule">
      Temps passé dans la guilde par les {{ departures.total }} membre{{ departures.total > 1 ? 's' : '' }} parti{{ departures.total > 1 ? 's' : '' }} {{ periodLabel }}.
    </p>
    <p v-if="departures.medianDays != null" class="dh-headline">
      La moitié d'entre eux sont partis dans {{ medianText }}.
    </p>
    <p class="dh-legend">
      <span class="dh-swatch dh-swatch--early"></span> avant 2 mois
      <span class="dh-swatch dh-swatch--late"></span> après 2 mois
    </p>
    <div class="dh-chart">
      <canvas ref="canvas" role="img" :aria-label="ariaLabel"></canvas>
    </div>
    <p v-if="departures.undatedExcluded" class="dh-note">
      {{ departures.undatedExcluded }} départ{{ departures.undatedExcluded > 1 ? 's' : '' }} plus ancien{{ departures.undatedExcluded > 1 ? 's' : '' }}, sans date connue, {{ departures.undatedExcluded > 1 ? 'ne sont' : "n'est" }} pas compté{{ departures.undatedExcluded > 1 ? 's' : '' }}.
    </p>
  </DashCard>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import DashCard from '@/components/home/DashCard.vue';
import { useThemeStore } from '@/stores/themeStore';
import { cssVar } from '@/utils/dashboardFormat';
import { durationLabel } from '@/utils/plainLanguage';

const props = defineProps({
  departures: { type: Object, required: true },
  months: { type: Number, default: 12 },
});

const canvas = ref(null);
const themeStore = useThemeStore();
let chart = null;

const periodLabel = computed(() => (props.months ? `ces ${props.months} derniers mois` : 'depuis le début du suivi'));
const medianText = computed(() => {
  const d = Math.round(props.departures.medianDays);
  return d <= 7 ? 'la première semaine' : `les ${durationLabel(d)} qui ont suivi leur arrivée`;
});
const ariaLabel = computed(() => props.departures.buckets.map((b) => `${b.label} : ${b.count}`).join(', '));

function render() {
  if (!canvas.value) return;
  chart?.destroy();
  const primary = cssVar('--primary', '#9E1B32');
  const accent = cssVar('--accent', '#C9A227');
  const muted = cssVar('--text-muted', '#6B625B');
  const border = cssVar('--border', '#ECE6DC');
  // Les départs précoces (avant 2 mois) en rouge, les plus tardifs en or.
  const colors = props.departures.buckets.map((b) => ((b.to ?? Infinity) < 61 ? primary : accent));

  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      labels: props.departures.buckets.map((b) => b.label),
      datasets: [{ label: 'Départs', data: props.departures.buckets.map((b) => b.count), backgroundColor: colors, borderRadius: 6, maxBarThickness: 34 }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: muted, font: { size: 10 } } },
        y: { beginAtZero: true, grid: { color: border }, border: { display: false }, ticks: { color: muted, font: { size: 10 }, precision: 0 } },
      },
    },
  });
}

watch(() => [props.departures, themeStore.currentTheme], () => nextTick(render), { deep: true, immediate: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<style scoped>
.dh-headline {
  margin: -0.35rem 0 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text);
}

.dh-legend {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin: 0 0 0.5rem;
  font-size: 0.72rem;
  color: var(--text-muted);
}

.dh-swatch {
  display: inline-block;
  width: 0.65rem;
  height: 0.65rem;
  border-radius: 0.15rem;
}

.dh-swatch--early { background-color: var(--primary); }
.dh-swatch--late { background-color: var(--accent); margin-left: 0.6rem; }

.dh-rule,
.dh-note {
  margin: 0 0 0.75rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.dh-note {
  margin: 0.75rem 0 0;
}

/* Hauteur donnée par l'espace libre (flex-basis 0), jamais par le canvas : sinon Chart.js
   agrandit le canvas, qui agrandit le conteneur, à chaque redimensionnement. */
.dh-chart {
  position: relative;
  flex: 1 1 0;
  min-height: 220px;
  overflow: hidden;
}
</style>
