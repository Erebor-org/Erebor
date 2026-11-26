<template>
  <div class="monthly-chart" :style="{ minHeight: `${height}px` }">
    <canvas ref="canvas" role="img" :aria-label="chartLabel"></canvas>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';
import { useThemeStore } from '@/stores/themeStore';
import { cssVar, monthLabel } from '@/utils/dashboardFormat';

/**
 * Entrées et sorties mensuelles en barres groupées, avec le solde net mis en avant :
 * ligne pleine au premier plan, point doré (positif) ou rouge (négatif) et valeur écrite
 * au-dessus de chaque mois. Les barres sont légèrement estompées pour laisser le solde ressortir.
 */
const props = defineProps({
  monthly: { type: Array, default: () => [] },
  height: { type: Number, default: 210 },
});

const canvas = ref(null);
const themeStore = useThemeStore();
let chart = null;

const chartLabel = computed(() =>
  'Entrées, sorties et solde par mois : ' +
  props.monthly.map((m) => `${monthLabel(m.month)} +${m.entries} −${m.exits} solde ${m.net}`).join(', ')
);

function withAlpha(color, alpha) {
  const hex = color.replace('#', '');
  if (!/^[0-9a-f]{6}$/i.test(hex)) return color;
  const n = parseInt(hex, 16);
  return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
}

const signed = (v) => (v > 0 ? `+${v}` : `${v}`);

// Écrit la valeur du solde au-dessus (ou en dessous si négatif) de chaque point.
const netLabels = {
  id: 'netLabels',
  afterDatasetsDraw(chartInstance, _args, options) {
    const meta = chartInstance.getDatasetMeta(options.datasetIndex);
    if (!meta || meta.hidden) return;
    const { ctx } = chartInstance;
    const values = chartInstance.data.datasets[options.datasetIndex].data;
    ctx.save();
    ctx.font = '700 11px ui-monospace, "SF Mono", Menlo, monospace';
    ctx.textAlign = 'center';
    ctx.lineWidth = 3;
    ctx.strokeStyle = options.halo;
    meta.data.forEach((point, i) => {
      const value = values[i];
      const below = value < 0;
      ctx.textBaseline = below ? 'top' : 'bottom';
      const y = point.y + (below ? 9 : -9);
      ctx.fillStyle = value < 0 ? options.negative : options.positive;
      ctx.strokeText(signed(value), point.x, y);
      ctx.fillText(signed(value), point.x, y);
    });
    ctx.restore();
  },
};

function render() {
  if (!canvas.value) return;
  chart?.destroy();

  const accent = cssVar('--accent', '#C9A227');
  const primary = cssVar('--primary', '#9E1B32');
  const text = cssVar('--text', '#1A1412');
  const muted = cssVar('--text-muted', '#6B625B');
  const border = cssVar('--border', '#ECE6DC');
  const bg = cssVar('--bg', '#ffffff');

  const labels = props.monthly.map((m) => monthLabel(m.month));
  const entries = props.monthly.map((m) => m.entries);
  const exits = props.monthly.map((m) => m.exits);
  const net = props.monthly.map((m) => m.net);
  const pointColors = net.map((v) => (v < 0 ? primary : accent));

  chart = new Chart(canvas.value, {
    data: {
      labels,
      datasets: [
        {
          type: 'line',
          label: 'Solde net',
          data: net,
          borderColor: text,
          borderWidth: 2.5,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: pointColors,
          pointBorderColor: text,
          pointBorderWidth: 2,
          tension: 0,
          order: 0,
        },
        { type: 'bar', label: 'Entrées', data: entries, backgroundColor: withAlpha(accent, 0.55), borderRadius: 6, maxBarThickness: 12, categoryPercentage: 0.6, order: 1 },
        { type: 'bar', label: 'Sorties', data: exits, backgroundColor: withAlpha(primary, 0.55), borderRadius: 6, maxBarThickness: 12, categoryPercentage: 0.6, order: 1 },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 18, bottom: 4 } },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: muted, boxWidth: 10, boxHeight: 10, font: { size: 11 }, usePointStyle: true },
        },
        tooltip: {
          callbacks: {
            label: (ctx) => `${ctx.dataset.label} : ${ctx.dataset.type === 'line' ? signed(ctx.parsed.y) : ctx.parsed.y}`,
          },
        },
        netLabels: { datasetIndex: 0, positive: text, negative: primary, halo: bg },
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: muted, font: { size: 10 } } },
        y: {
          grace: '15%',
          border: { display: false },
          ticks: { color: muted, font: { size: 10 }, precision: 0 },
          // Ligne zéro plus marquée : c'est la frontière entre une guilde qui grandit et qui rétrécit.
          grid: {
            color: (ctx) => (ctx.tick.value === 0 ? withAlpha(muted, 0.7) : border),
            lineWidth: (ctx) => (ctx.tick.value === 0 ? 1.5 : 1),
          },
        },
      },
    },
    plugins: [netLabels],
  });
}

watch(() => [props.monthly, themeStore.currentTheme], () => nextTick(render), { deep: true, immediate: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<style scoped>
/* Occupe toute la hauteur libre de la carte (alignée sur le bloc voisin), au minimum `height`. */
.monthly-chart {
  position: relative;
  flex: 1 1 auto;
  min-height: 0;
}
</style>
