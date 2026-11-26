<template>
  <DashCard title="Fantômes du mois" :loading="loading">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h.01M15 10h.01M12 2a8 8 0 00-8 8v12l3-3 2.5 3L12 19l2.5 3L17 19l3 3V10a8 8 0 00-8-8z" /></svg>
    </template>
    <template #action>
      <RouterLink to="/fantomes" class="ghost-link">Voter →</RouterLink>
    </template>

    <p v-if="!ghost" class="dash-empty">Aucune manche ouverte pour le moment.</p>
    <template v-else>
      <div class="ghost-status" :class="ghost.hasVoted ? 'is-done' : 'is-todo'">
        <span class="ghost-badge">{{ ghost.hasVoted ? 'Vote enregistré' : 'Pas encore voté' }}</span>
        <span v-if="ghost.hasVoted" class="ghost-mine">
          {{ ghost.myVotes }} membre{{ ghost.myVotes > 1 ? 's' : '' }} signalé{{ ghost.myVotes > 1 ? 's' : '' }}
        </span>
      </div>
      <dl class="ghost-facts">
        <div><dt>Ouverte le</dt><dd>{{ shortDate(ghost.openedAt) }}</dd></div>
        <div><dt>Votants</dt><dd>{{ ghost.voters }}</dd></div>
        <div><dt>Au-dessus du seuil ({{ ghost.threshold }})</dt><dd>{{ ghost.flagged }}</dd></div>
      </dl>
    </template>
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import { shortDate } from '@/utils/dashboardFormat';

defineProps({
  ghost: { type: Object, default: null },
  loading: { type: Boolean, default: false },
});
</script>

<style scoped>
.ghost-link {
  color: var(--link);
  font-weight: 500;
}

.ghost-status {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.6rem;
  margin-bottom: 1rem;
}

.ghost-badge {
  padding: 0.25rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.is-done .ghost-badge {
  color: var(--success);
  background-color: rgba(34, 197, 94, 0.12);
}

.is-todo .ghost-badge {
  color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.14);
}

.ghost-mine {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.ghost-facts {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
  margin: 0;
}

.ghost-facts dt {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.ghost-facts dd {
  margin: 0;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--text);
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
