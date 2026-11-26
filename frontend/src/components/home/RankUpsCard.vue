<template>
  <DashCard :title="recruits ? 'Montées de rang & recrues' : 'Montées de rang'" :loading="loading">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7M5 21l7-7 7 7" /></svg>
    </template>

    <div class="rankups-grid" :class="{ 'rankups-grid--two': !recruits }">
      <section class="rankups-col">
        <h3 class="upcoming-heading">Prévues · 7 prochains jours</h3>
        <p v-if="!promotions.length" class="dash-empty">Aucune montée prévue cette semaine.</p>
        <ul v-else class="upcoming-list col-scroll">
          <li v-for="p in promotions" :key="`${p.characterId}-${p.rank}`" class="upcoming-row">
            <img :src="getClassIcon(p.class)" alt="" class="upcoming-icon" />
            <span class="upcoming-name">{{ p.pseudo }}</span>
            <span class="upcoming-what">→ <span class="upcoming-rank">{{ p.rank }}</span></span>
            <time class="upcoming-when" :datetime="p.date">{{ upcomingDay(p.date) }}</time>
          </li>
        </ul>
      </section>

      <section class="rankups-col">
        <h3 class="upcoming-heading">Récentes</h3>
        <FeedList
          :items="recent"
          :has-more="hasMore"
          :loading-more="loadingMore"
          empty-text="Aucune montée de rang récente."
          max-height="var(--col-height)"
          @load-more="$emit('load-more')"
        />
      </section>

      <!-- Réservé aux ROLE_ADMIN et plus : le serveur n'envoie pas les recrues aux autres -->
      <section v-if="recruits" class="rankups-col">
        <h3 class="upcoming-heading">Recrues de moins de 15 jours · {{ recruits.length }}</h3>
        <p v-if="!recruits.length" class="dash-empty">Aucune recrue ces 15 derniers jours.</p>
        <ul v-else class="fresh-list col-scroll">
          <li v-for="r in recruits" :key="r.characterId" class="fresh-row">
            <img :src="getClassIcon(r.class)" alt="" class="fresh-icon" />
            <div class="fresh-who">
              <span class="fresh-name">{{ r.pseudo }}</span>
              <span class="fresh-meta">J{{ Math.max(r.days, 1) }}<template v-if="r.recruiter"> · recruté par {{ r.recruiter }}</template></span>
            </div>
            <span v-if="r.warnings" class="fresh-flag fresh-flag--warn" :title="`${r.warnings} avertissement(s)`">⚠ {{ r.warnings }}</span>
            <span v-if="r.ghostVotes" class="fresh-flag fresh-flag--ghost" :title="`${r.ghostVotes} vote(s) fantôme cette manche`">👻 {{ r.ghostVotes }}</span>
          </li>
        </ul>
      </section>
    </div>
  </DashCard>
</template>

<script setup>
import DashCard from './DashCard.vue';
import FeedList from './FeedList.vue';
import { getClassIcon } from '@/config/classIcons';
import { upcomingDay } from '@/utils/dashboardFormat';

defineProps({
  // Montées d'ancienneté qui tombent dans les 7 prochains jours.
  promotions: { type: Array, default: () => [] },
  // Montées passées (ancienneté et changements faits sur le site), du plus récent au plus ancien.
  recent: { type: Array, default: () => [] },
  // Recrues de moins de 15 jours ; null pour les non-admins (colonne masquée).
  recruits: { type: Array, default: null },
  hasMore: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
});
defineEmits(['load-more']);
</script>

<style scoped>
/* Trois colonnes séparées par un filet doré : prévues · récentes · anniversaires */
.rankups-grid {
  /* Hauteur commune des trois colonnes ; chacune défile indépendamment au-delà. */
  --col-height: 17rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.rankups-grid--two {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.col-scroll {
  max-height: var(--col-height);
  overflow-y: auto;
  padding-right: 0.25rem;
}

.rankups-col {
  min-width: 0;
}

.rankups-col + .rankups-col {
  padding-left: 1.5rem;
  border-left: 1px solid rgba(var(--accent-rgb), 0.18);
}

@media (max-width: 1024px) {
  .rankups-grid,
  .rankups-grid--two {
    grid-template-columns: 1fr;
  }
  .rankups-col + .rankups-col {
    padding-left: 0;
    padding-top: 1.25rem;
    border-left: 0;
    border-top: 1px solid rgba(var(--accent-rgb), 0.18);
  }
}

.upcoming-heading {
  margin: 0 0 0.5rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.66rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.upcoming-heading--spaced {
  margin-top: 1.25rem;
}

.upcoming-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.upcoming-row {
  display: grid;
  grid-template-columns: 1.4rem minmax(0, 1fr) auto 4.5rem;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.85rem;
}

.upcoming-icon {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 9999px;
  object-fit: cover;
}

.upcoming-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--text);
  font-weight: 500;
}

.upcoming-what {
  color: var(--text-muted);
  white-space: nowrap;
}

.upcoming-rank {
  color: var(--accent);
  font-weight: 600;
}

.upcoming-when {
  font-size: 0.75rem;
  color: var(--text-muted);
  text-align: right;
  white-space: nowrap;
}

.fresh-list {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.fresh-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.fresh-icon {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 9999px;
  object-fit: cover;
  flex-shrink: 0;
}

.fresh-who {
  display: flex;
  flex: 1;
  min-width: 0;
  flex-direction: column;
}

.fresh-name {
  font-size: 0.86rem;
  font-weight: 500;
  color: var(--text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fresh-meta {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.fresh-flag {
  flex-shrink: 0;
  padding: 0.1rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.fresh-flag--warn {
  color: var(--warning);
  background-color: rgba(var(--accent-rgb), 0.14);
}

.fresh-flag--ghost {
  color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.12);
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
