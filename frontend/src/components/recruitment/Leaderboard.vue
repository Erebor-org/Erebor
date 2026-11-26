<template>
  <DashCard title="Meilleurs recruteurs">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" /></svg>
    </template>
    <p class="lb-rule">Classés selon le nombre de leurs recrues restées plus de 2 mois.</p>

    <!-- Périmètre restreint : seulement sa propre position, sans les autres noms -->
    <template v-if="restricted">
      <div v-if="mine" class="lb-mine">
        <span class="lb-mine-pos">{{ ordinal(mine.position) }}</span>
        <span class="lb-mine-total">sur {{ mine.totalRecruiters }} recruteurs</span>
        <span class="lb-mine-score">
          {{ mine.retention.d61.retained }} de vos recrues {{ mine.retention.d61.retained > 1 ? 'sont restées' : 'est restée' }} plus de 2 mois
          <template v-if="mine.retention.d61.n">(sur {{ mine.retention.d61.n }})</template>
        </span>
      </div>
      <p v-else class="dash-empty">Pas encore de recrue sur la période.</p>
    </template>

    <template v-else>
      <p v-if="!entries.length" class="dash-empty">Aucun recruteur sur la période.</p>
      <ol v-else class="lb-list">
        <li v-for="e in entries" :key="e.recruiterId" class="lb-row" :class="`lb-row--${Math.min(e.position, 4)}`">
          <span class="lb-pos">{{ medal(e.position) }}</span>
          <img :src="getClassIcon(e.class)" alt="" class="lb-icon" />
          <span class="lb-name">{{ e.pseudo }}</span>
          <span class="lb-bar"><span class="lb-bar-fill" :style="{ width: `${(e.retention.d61.retained / max) * 100}%` }"></span></span>
          <span class="lb-score" :title="`${e.retention.d61.retained} recrues restées plus de 2 mois, sur ${e.retention.d61.n} arrivées depuis plus de 2 mois`">{{ e.retention.d61.retained }}</span>
          <span class="lb-n">sur {{ e.retention.d61.n }}</span>
        </li>
      </ol>
    </template>
  </DashCard>
</template>

<script setup>
import { computed } from 'vue';
import DashCard from '@/components/home/DashCard.vue';
import { getClassIcon } from '@/config/classIcons';

const props = defineProps({
  entries: { type: Array, default: () => [] },
  mine: { type: Object, default: null },
  restricted: { type: Boolean, default: false },
});

const max = computed(() => Math.max(1, ...props.entries.map((e) => e.retention.d61.retained)));
const ordinal = (n) => (n === 1 ? '1er' : `${n}e`);
const medal = (position) => ({ 1: '🥇', 2: '🥈', 3: '🥉' }[position] || ordinal(position));
</script>

<style scoped>
.lb-rule {
  margin: 0 0 0.75rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.lb-list {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin: 0;
  padding: 0 0.25rem 0 0;
  list-style: none;
  max-height: 24rem;
  overflow-y: auto;
}

.lb-row {
  display: grid;
  grid-template-columns: 2rem 1.5rem minmax(0, 6rem) 1fr 1.75rem 3rem;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}

.lb-pos {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.9rem;
  text-align: center;
  color: var(--text-muted);
}

.lb-row--1 .lb-pos,
.lb-row--2 .lb-pos,
.lb-row--3 .lb-pos {
  font-size: 1.1rem;
}

.lb-icon {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 9999px;
  object-fit: cover;
}

.lb-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 600;
  color: var(--text);
}

.lb-bar {
  height: 0.5rem;
  border-radius: 9999px;
  background-color: var(--bg-muted);
  overflow: hidden;
}

.lb-bar-fill {
  display: block;
  height: 100%;
  border-radius: 9999px;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
}

.lb-score {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-weight: 700;
  text-align: right;
  color: var(--text);
}

.lb-n {
  font-size: 0.7rem;
  color: var(--text-muted);
}

.lb-mine {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
  padding: 1rem 0;
}

.lb-mine-pos {
  font-family: ui-serif, Georgia, serif;
  font-weight: 700;
  font-size: 3rem;
  line-height: 1;
  color: var(--accent);
}

.lb-mine-total {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.lb-mine-score {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: var(--text);
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
