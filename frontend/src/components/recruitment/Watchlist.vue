<template>
  <DashCard :title="`Recrues à surveiller · ${items.length}`">
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
    </template>
    <p class="wl-rule">Recrues du dernier mois qui ont déjà un avertissement ou ont été signalées inactives.</p>

    <p v-if="!items.length" class="dash-empty">Aucune recrue à surveiller. 👍</p>
    <ul v-else class="wl-list">
      <li v-for="r in items" :key="r.characterId" class="wl-row">
        <img :src="getClassIcon(r.class)" alt="" class="wl-icon" />
        <div class="wl-who">
          <span class="wl-name">{{ r.pseudo }}</span>
          <span class="wl-meta">{{ arrivedLabel(r.days) }}<template v-if="r.recruiter"> · par {{ r.recruiter }}</template></span>
          <span v-if="r.lastWarning" class="wl-warning" :title="r.lastWarning">« {{ r.lastWarning }} »</span>
        </div>
        <span v-if="r.warnings" class="wl-flag wl-flag--warn">⚠ {{ r.warnings }} avertissement{{ r.warnings > 1 ? 's' : '' }}</span>
        <span v-if="r.ghostVotes" class="wl-flag wl-flag--ghost">👻 signalé{{ r.ghostVotes > 1 ? ` ${r.ghostVotes} fois` : '' }} inactif</span>
      </li>
    </ul>
  </DashCard>
</template>

<script setup>
import DashCard from '@/components/home/DashCard.vue';
import { getClassIcon } from '@/config/classIcons';
import { arrivedLabel } from '@/utils/plainLanguage';

defineProps({
  items: { type: Array, default: () => [] },
  days: { type: Number, default: 30 },
});
</script>

<style scoped>
.wl-rule {
  margin: 0 0 0.75rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.wl-list {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin: 0;
  padding: 0 0.25rem 0 0;
  list-style: none;
  max-height: 20rem;
  overflow-y: auto;
}

.wl-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.wl-icon {
  width: 1.9rem;
  height: 1.9rem;
  border-radius: 9999px;
  object-fit: cover;
  flex-shrink: 0;
}

.wl-who {
  display: flex;
  flex: 1;
  min-width: 0;
  flex-direction: column;
}

.wl-name {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text);
}

.wl-meta {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.wl-warning {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.72rem;
  font-style: italic;
  color: var(--text-muted);
}

.wl-flag {
  flex-shrink: 0;
  padding: 0.1rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.wl-flag--warn {
  color: var(--warning);
  background-color: rgba(var(--accent-rgb), 0.14);
}

.wl-flag--ghost {
  color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.12);
}

.dash-empty {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
</style>
