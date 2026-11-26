<template>
  <DashCard :title="`Semaine ${weekNumber} · du ${shortDate(week.start)} au ${shortDate(week.end)}`" accent>
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
    </template>
    <template #action>
      <span v-if="week.isCurrent" class="week-live">semaine en cours</span>
    </template>

    <div class="week-kpis">
      <div class="week-kpi week-kpi--main">
        <span class="week-kpi-label">{{ week.recruits > 1 ? 'Recrues' : 'Recrue' }}</span>
        <span class="week-kpi-value">{{ week.recruits }}</span>
        <span class="week-kpi-compare" :class="tone(week.deltaPrevious)">
          {{ compareCount(week.deltaPrevious, 'la semaine dernière') }}
        </span>
        <span class="week-kpi-compare" :class="habitTone">
          {{ habit }} <span class="muted">({{ averageText }})</span>
        </span>
      </div>
      <div class="week-kpi">
        <span class="week-kpi-label">{{ week.departures > 1 ? 'Départs' : 'Départ' }}</span>
        <span class="week-kpi-value is-out">{{ week.departures }}</span>
      </div>
      <div class="week-kpi">
        <span class="week-kpi-label">Bilan</span>
        <span class="week-kpi-sentence" :class="week.net > 0 ? 'is-up' : week.net < 0 ? 'is-down' : 'is-flat'">
          {{ balance }}
        </span>
      </div>
    </div>

    <h3 class="week-list-title">Recrues de la semaine</h3>
    <p v-if="!week.list.length" class="dash-empty">Aucune recrue cette semaine.</p>
    <ul v-else class="week-list">
      <li v-for="r in week.list" :key="r.characterId" class="week-row" :class="{ 'is-gone': !r.stillHere }">
        <img :src="getClassIcon(r.class)" alt="" class="week-icon" />
        <span class="week-name">{{ r.pseudo }}</span>
        <span class="week-meta">{{ upcomingDay(r.recruitedAt) === "aujourd'hui" ? "aujourd'hui" : shortDate(r.recruitedAt) }}</span>
        <span class="week-meta">{{ r.recruiter || '—' }}</span>
        <span v-if="!r.stillHere" class="week-gone">partie le {{ shortDate(r.leftAt) }}</span>
      </li>
    </ul>
  </DashCard>
</template>

<script setup>
import { computed } from 'vue';
import DashCard from '@/components/home/DashCard.vue';
import { getClassIcon } from '@/config/classIcons';
import { shortDate, upcomingDay } from '@/utils/dashboardFormat';
import { compareCount, habitLabel } from '@/utils/plainLanguage';

const props = defineProps({
  week: { type: Object, required: true },
});

const weekNumber = computed(() => Number(props.week.week.split('-W')[1]));
const tone = (v) => (v == null || v === 0 ? 'is-flat' : v > 0 ? 'is-up' : 'is-down');

// « plus / moins / comme d'habitude », d'habitude = moyenne des 8 semaines précédentes.
const habit = computed(() => habitLabel(props.week.recruits, props.week.average8));
const habitTone = computed(() => ({ "plus que d'habitude": 'is-up', "moins que d'habitude": 'is-down' }[habit.value] || 'is-flat'));
const averageText = computed(() => {
  const avg = props.week.average8;
  if (!avg) return "aucune recrue ces 2 derniers mois";
  if (avg < 1) return "moins d'une par semaine en moyenne";
  const rounded = Math.round(avg);
  return `environ ${rounded} par semaine en moyenne`;
});
const balance = computed(() => {
  const net = props.week.net;
  if (net === 0) return 'autant d\'arrivées que de départs';
  const n = Math.abs(net);
  return `${n} membre${n > 1 ? 's' : ''} ${net > 0 ? 'gagné' : 'perdu'}${n > 1 ? 's' : ''}`;
});
</script>

<style scoped>
.week-live {
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.14);
}

.week-kpis {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.week-kpi {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.week-kpi-label {
  font-family: ui-serif, Georgia, serif;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
}

.week-kpi-value {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 2.4rem;
  line-height: 1;
  color: var(--text);
}

.week-kpi--main .week-kpi-value {
  font-size: 3rem;
}

.week-kpi-sentence {
  margin-top: 0.35rem;
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.3;
}

.week-kpi-compare {
  font-size: 0.82rem;
  font-weight: 600;
}

.muted { color: var(--text-muted); font-weight: 400; }
.is-up { color: var(--success); }
.is-down, .is-out { color: var(--primary); }
.is-flat { color: var(--text-muted); }

.week-list-title {
  margin: 0 0 0.5rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.66rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.week-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin: 0;
  padding: 0 0.25rem 0 0;
  list-style: none;
  max-height: 14rem;
  overflow-y: auto;
}

.week-row {
  display: grid;
  grid-template-columns: 1.4rem minmax(0, 1fr) 4.5rem minmax(0, 8rem) auto;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.85rem;
}

.week-row.is-gone .week-name {
  text-decoration: line-through;
  color: var(--text-muted);
}

.week-icon {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 9999px;
  object-fit: cover;
}

.week-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
  color: var(--text);
}

.week-meta {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.week-gone {
  font-size: 0.72rem;
  color: var(--primary);
  white-space: nowrap;
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}

@media (max-width: 640px) {
  .week-kpis { grid-template-columns: 1fr 1fr; }
  .week-kpi--main { grid-column: 1 / -1; }
  .week-row { grid-template-columns: 1.4rem minmax(0, 1fr) auto; }
  .week-row .week-meta:nth-of-type(2) { display: none; }
}
</style>
