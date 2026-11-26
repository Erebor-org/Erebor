<template>
  <div class="feed-wrap">
    <p v-if="!entries.length" class="dash-empty">{{ emptyText }}</p>
    <ol v-else class="feed" :style="{ maxHeight }">
      <li v-for="(item, index) in entries" :key="`${item.type}-${item.occurredAt}-${item.pseudo}-${index}`" class="feed-item">
        <span class="feed-dot" :class="`feed-dot--${tone(item.type)}`" aria-hidden="true"></span>
        <div class="feed-body">
          <p class="feed-line">
            <template v-if="item.type === 'joined'">
              <strong>{{ item.pseudo }}</strong> rejoint la guilde
              <span v-if="item.details.recruiter" class="feed-meta">· recruté par {{ item.details.recruiter }}</span>
            </template>
            <template v-else-if="item.type === 'promoted' && item.others.length">
              <strong>{{ item.pseudo }}</strong>
              <span :title="item.others.join(', ')"> et {{ item.others.length }} autre{{ item.others.length > 1 ? 's' : '' }}</span>
              deviennent <span class="feed-rank">{{ item.details.rank }}</span>
            </template>
            <template v-else-if="item.type === 'promoted'">
              <strong>{{ item.pseudo }}</strong> devient <span class="feed-rank">{{ item.details.rank }}</span>
            </template>
            <template v-else-if="item.type === 'left'">
              <strong>{{ item.pseudo }}</strong> quitte la guilde
              <span v-if="item.details.daysInGuild != null" class="feed-meta">· après {{ durationLabel(item.details.daysInGuild) }}</span>
            </template>
            <template v-else-if="item.type === 'returned'">
              <strong>{{ item.pseudo }}</strong> est de retour
            </template>
            <template v-else-if="item.type === 'rank_changed'">
              <strong>{{ item.pseudo }}</strong> :
              {{ item.details.from?.name || '—' }} → <span class="feed-rank">{{ item.details.to?.name || '—' }}</span>
            </template>
            <template v-else-if="item.type === 'mule_added'">
              Nouvelle mule <strong>{{ item.mulePseudo }}</strong>
              <span class="feed-meta">· {{ item.pseudo }}</span>
            </template>
            <template v-else-if="item.type === 'main_switched'">
              <strong>{{ item.details.oldMain }}</strong> joue désormais <strong>{{ item.details.newMain }}</strong>
            </template>
            <template v-else>{{ item.pseudo }}</template>
          </p>
          <time class="feed-time" :datetime="item.occurredAt">{{ relativeDay(item.occurredAt) }}</time>
        </div>
      </li>
    </ol>

    <button v-if="hasMore" type="button" class="feed-more" :disabled="loadingMore" @click="$emit('load-more')">
      {{ loadingMore ? 'Chargement…' : 'Voir plus' }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { relativeDay } from '@/utils/dashboardFormat';
import { durationLabel } from '@/utils/plainLanguage';

// Liste d'événements de guilde (arrivées, départs, montées de rang…), partagée par les
// blocs « Entrées & sorties » et « Montées de rang » de l'accueil.
const props = defineProps({
  items: { type: Array, default: () => [] },
  hasMore: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  emptyText: { type: String, default: "Rien de neuf pour l'instant." },
  maxHeight: { type: String, default: '24rem' },
});
defineEmits(['load-more']);

// Les montées d'un même rang le même jour (membres importés ensemble) tiennent sur une ligne.
const entries = computed(() => {
  const grouped = [];
  for (const item of props.items) {
    const previous = grouped[grouped.length - 1];
    if (
      item.type === 'promoted' && previous?.type === 'promoted'
      && previous.occurredAt === item.occurredAt && previous.details.rank === item.details.rank
    ) {
      previous.others.push(item.pseudo);
    } else {
      grouped.push({ ...item, others: [] });
    }
  }
  return grouped;
});

function tone(type) {
  return {
    joined: 'in',
    returned: 'in',
    left: 'out',
    promoted: 'gold',
    rank_changed: 'gold',
  }[type] || 'muted';
}
</script>

<style scoped>
.feed-wrap {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.feed {
  display: flex;
  flex-direction: column;
  margin: 0;
  padding: 0 0.25rem 0 0;
  list-style: none;
  overflow-y: auto;
}

.feed-item {
  position: relative;
  display: flex;
  gap: 0.75rem;
  padding: 0.55rem 0;
}

.feed-item + .feed-item {
  border-top: 1px solid var(--border);
}

.feed-dot {
  flex-shrink: 0;
  width: 0.55rem;
  height: 0.55rem;
  margin-top: 0.4rem;
  border-radius: 9999px;
}

.feed-dot--in { background-color: var(--success); }
.feed-dot--out { background-color: var(--primary); }
.feed-dot--gold { background-color: var(--accent); }
.feed-dot--muted { background-color: var(--text-muted); }

.feed-body {
  display: flex;
  flex: 1;
  min-width: 0;
  justify-content: space-between;
  gap: 0.75rem;
}

.feed-line {
  margin: 0;
  font-size: 0.86rem;
  color: var(--text);
}

.feed-meta {
  color: var(--text-muted);
}

.feed-rank {
  color: var(--accent);
  font-weight: 600;
}

.feed-time {
  flex-shrink: 0;
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.feed-more {
  margin-top: 0.75rem;
  align-self: center;
  padding: 0.4rem 1rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  color: var(--text);
  background-color: var(--card);
  border: 1px solid var(--border);
  cursor: pointer;
  transition: border-color 0.3s;
}

.feed-more:hover:not(:disabled) { border-color: var(--accent); }
.feed-more:disabled { opacity: 0.6; cursor: wait; }

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
