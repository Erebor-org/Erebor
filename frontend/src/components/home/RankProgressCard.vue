<template>
  <DashCard title="Ma progression" :loading="loading" accent>
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
    </template>

    <p v-if="!progress" class="dash-empty">
      Associez votre personnage depuis votre <RouterLink to="/profil" class="progress-link">profil</RouterLink>
      pour suivre votre ancienneté.
    </p>

    <template v-else>
      <div class="progress-ranks">
        <span class="progress-current">{{ progress.seniorityRank?.name || '—' }}</span>
        <template v-if="progress.nextRank">
          <svg class="progress-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
          <span class="progress-next">{{ progress.nextRank.name }}</span>
        </template>
      </div>

      <div
        class="progress-track"
        role="progressbar"
        :aria-valuenow="progress.progressPercent"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="`Progression vers ${progress.nextRank?.name || 'le rang maximal'}`"
      >
        <span class="progress-fill" :style="{ width: `${progress.progressPercent}%` }"></span>
      </div>

      <p class="progress-caption">
        <span class="progress-days">J{{ Math.max(progress.seniorityDays, 1) }}</span>
        <template v-if="progress.nextRank">
          · encore <strong>{{ progress.nextRank.daysLeft }} j</strong>
          <span class="progress-muted">({{ shortDate(progress.nextRank.date) }})</span>
        </template>
        <template v-else> · rang d'ancienneté maximal atteint</template>
      </p>

      <p v-if="progress.storedRankIsSpecial" class="progress-note">
        Rang de fonction : <strong>{{ progress.storedRank }}</strong>. L'ancienneté continue de compter.
      </p>
    </template>
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import { shortDate } from '@/utils/dashboardFormat';

defineProps({
  progress: { type: Object, default: null },
  loading: { type: Boolean, default: false },
});
</script>

<style scoped>
.progress-ranks {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.3rem;
}

.progress-current { color: var(--text); }
.progress-next { color: var(--accent); }

.progress-arrow {
  width: 1rem;
  height: 1rem;
  color: var(--text-muted);
}

.progress-track {
  height: 0.6rem;
  border-radius: 9999px;
  background-color: rgba(var(--text-rgb), 0.08);
  overflow: hidden;
}

.progress-fill {
  display: block;
  height: 100%;
  border-radius: 9999px;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  transition: width 0.6s ease;
}

.progress-caption {
  margin: 0.6rem 0 0;
  font-size: 0.85rem;
  color: var(--text);
}

.progress-days {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-weight: 700;
}

.progress-muted,
.progress-note {
  color: var(--text-muted);
}

.progress-note {
  margin: 0.6rem 0 0;
  font-size: 0.75rem;
}

.progress-link {
  color: var(--link);
  text-decoration: underline;
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
