<template>
  <DashCard :title="restricted ? 'Mes recrues' : 'Par recruteur'">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
    </template>

    <p class="rt-rule">
      Combien de recrues sont restées après 2 semaines, 2 mois et 4 mois.
      Pour chaque délai, on ne compte que les recrues arrivées depuis assez longtemps.
    </p>
    <p v-if="!rows.length" class="dash-empty">Aucune recrue sur la période.</p>
    <div v-else class="rt-scroll">
      <table class="rt-table">
        <thead>
          <tr>
            <th class="rt-left">Recruteur</th>
            <th>Recrues</th>
            <th>Toujours là</th>
            <th v-for="k in milestones" :key="k">Après {{ milestoneLabel(k) }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r.recruiterId">
            <td class="rt-left">
              <span class="rt-who">
                <img :src="getClassIcon(r.class)" alt="" class="rt-icon" />
                <span class="rt-name">{{ r.pseudo }}</span>
                <span v-if="r.recruiterArchived" class="rt-tag">parti</span>
              </span>
            </td>
            <td class="rt-num">{{ r.volume }}</td>
            <td class="rt-num">{{ r.stillHere }}</td>
            <td v-for="k in milestones" :key="k"><RateCell :value="r.retention[`d${k}`]" :milestone="k" /></td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="excluded > 0" class="rt-note">
      {{ excluded }} départ{{ excluded > 1 ? 's' : '' }} plus ancien{{ excluded > 1 ? 's' : '' }}, sans date connue, {{ excluded > 1 ? 'ne sont' : "n'est" }} pas compté{{ excluded > 1 ? 's' : '' }}.
    </p>
  </DashCard>
</template>

<script setup>
import { computed } from 'vue';
import DashCard from '@/components/home/DashCard.vue';
import RateCell from './RateCell.vue';
import { getClassIcon } from '@/config/classIcons';
import { milestoneLabel } from '@/utils/plainLanguage';

const props = defineProps({
  rows: { type: Array, default: () => [] },
  milestones: { type: Array, default: () => [14, 61, 122] },
  restricted: { type: Boolean, default: false },
});

const excluded = computed(() => props.rows.reduce((sum, r) => sum + r.excluded, 0));
</script>

<style scoped>
.rt-rule,
.rt-note {
  margin: 0 0 0.75rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.rt-note {
  margin: 0.75rem 0 0;
}

.rt-scroll {
  max-height: 24rem;
  overflow: auto;
}

.rt-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.rt-table th {
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

.rt-table td {
  padding: 0.35rem 0.5rem;
  text-align: center;
  border-bottom: 1px solid var(--border);
}

.rt-left {
  text-align: left !important;
}

.rt-who {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.rt-icon {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 9999px;
  object-fit: cover;
}

.rt-name {
  font-weight: 600;
  color: var(--text);
}

.rt-tag {
  padding: 0 0.4rem;
  border-radius: 9999px;
  font-size: 0.65rem;
  color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.12);
}

.rt-num {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
