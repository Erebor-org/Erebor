<template>
  <div class="glass-card rounded-2xl p-4 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
      <span
        v-if="item.class"
        class="portrait-ring w-12 h-12 shrink-0"
        :class="{ grayscale: item.status === 'already_archived' }"
      >
        <img :src="getClassIcon(item.class)" :alt="`Classe ${item.class}`" />
      </span>
      <span
        v-else
        class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center bg-theme-bg-muted text-theme-text-muted border border-theme-border"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </span>

      <div class="min-w-0">
        <p class="font-serif font-bold text-theme-text truncate">{{ item.pseudo }}</p>
        <p class="text-xs text-theme-text-muted capitalize truncate">
          <span v-if="item.class">{{ item.class }}</span>
          <span v-else>Aucune correspondance en base</span>
          <span v-if="item.type === 'mule' && item.mainCharacterPseudo"> · mule de {{ item.mainCharacterPseudo }}</span>
        </p>
      </div>
    </div>

    <div class="flex flex-col items-end gap-1.5 shrink-0">
      <span v-if="item.type !== 'not_found'" class="meta-chip" :class="typeBadgeClass">{{ typeLabel }}</span>
      <span class="meta-chip" :class="statusBadgeClass">{{ statusLabel }}</span>
    </div>

    <button
      v-if="removable"
      type="button"
      @click="$emit('remove', item)"
      title="Retirer de l'archivage"
      class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-theme-text-muted hover:text-theme-error hover:bg-theme-error/10 transition-colors duration-200"
    >
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>

<script>
import { getClassIcon } from '@/config/classIcons';

const TYPE_LABELS = { character: 'Personnage', mule: 'Mule' };
const TYPE_BADGE_CLASSES = { mule: '!border-theme-accent/40 !text-theme-accent !bg-theme-accent/10' };
const STATUS_LABELS = {
  to_archive: 'À archiver',
  archived: 'Archivé',
  already_archived: 'Déjà archivé',
  not_found: 'Introuvable',
};
const STATUS_BADGE_CLASSES = {
  to_archive: '!border-theme-success/40 !text-theme-success !bg-theme-success/10',
  archived: '!border-theme-success/40 !text-theme-success !bg-theme-success/10',
  already_archived: '!border-theme-border !text-theme-text-muted !bg-theme-bg-muted',
  not_found: '!border-theme-error/40 !text-theme-error !bg-theme-error/10',
};

export default {
  name: 'ArchiveLogMemberCard',
  props: {
    item: {
      type: Object,
      required: true,
    },
    removable: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['remove'],
  computed: {
    typeLabel() {
      return TYPE_LABELS[this.item.type] || '—';
    },
    typeBadgeClass() {
      return TYPE_BADGE_CLASSES[this.item.type] || '';
    },
    statusLabel() {
      return STATUS_LABELS[this.item.status] || this.item.status;
    },
    statusBadgeClass() {
      return STATUS_BADGE_CLASSES[this.item.status] || '';
    },
  },
  methods: {
    getClassIcon,
  },
};
</script>
