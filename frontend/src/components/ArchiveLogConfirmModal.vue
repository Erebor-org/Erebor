<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    @click.self="close"
  >
    <div class="glass-modal rounded-2xl max-w-md w-full mx-4 transform transition-all duration-300">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 pb-4">
        <div class="flex items-center">
          <div class="w-11 h-11 bg-theme-primary/15 border border-theme-primary/40 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
            <svg class="w-6 h-6 text-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-serif font-bold text-theme-text">Confirmer l'archivage</h3>
            <p class="text-xs text-theme-text-muted mt-0.5">{{ countLabel }}</p>
          </div>
        </div>
        <button
          @click="close"
          class="text-theme-text-muted hover:text-theme-primary transition-colors duration-200"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- List reminder -->
      <div class="px-6">
        <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1 rounded-xl border border-theme-border bg-theme-bg-muted/60 p-2.5">
          <div
            v-for="item in items"
            :key="item.logPseudo + item.type"
            class="flex items-center gap-2.5 px-1.5 py-1 rounded-lg"
          >
            <span v-if="item.class" class="portrait-ring w-7 h-7 shrink-0">
              <img :src="getClassIcon(item.class)" :alt="`Classe ${item.class}`" />
            </span>
            <span class="text-sm text-theme-text truncate flex-1">{{ item.pseudo }}</span>
            <span
              class="meta-chip text-[0.65rem] shrink-0"
              :class="item.type === 'mule' ? '!border-theme-accent/40 !text-theme-accent !bg-theme-accent/10' : ''"
            >
              {{ item.type === 'mule' ? 'Mule' : 'Personnage' }}
            </span>
          </div>
        </div>
        <p class="text-xs text-theme-text-muted mt-3">
          Ils resteront consultables dans l'onglet « Membres archivés » et pourront être restaurés à tout moment.
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end space-x-3 p-6 pt-5">
        <button
          @click="close"
          class="px-5 py-2.5 text-sm font-medium text-theme-text bg-theme-bg-muted border border-theme-border rounded-xl hover:bg-theme-border transition-all duration-300"
        >
          Annuler
        </button>
        <button
          @click="confirm"
          class="px-5 py-2.5 text-sm font-semibold text-white bg-theme-primary border border-transparent rounded-xl hover:bg-theme-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-theme-primary focus:ring-offset-theme-bg transition-all duration-300 shadow-lg shadow-theme-primary/30"
        >
          Archiver les personnages
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { getClassIcon } from '@/config/classIcons';

export default {
  name: 'ArchiveLogConfirmModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    items: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['close', 'confirm'],
  computed: {
    countLabel() {
      const characterCount = this.items.filter((item) => item.type === 'character').length;
      const muleCount = this.items.filter((item) => item.type === 'mule').length;
      const parts = [];
      if (characterCount > 0) parts.push(`${characterCount} personnage${characterCount > 1 ? 's' : ''}`);
      if (muleCount > 0) parts.push(`${muleCount} mule${muleCount > 1 ? 's' : ''}`);
      return parts.length ? parts.join(' et ') : 'Aucun élément à archiver';
    },
  },
  methods: {
    getClassIcon,
    close() {
      this.$emit('close');
    },
    confirm() {
      this.$emit('confirm');
    },
  },
};
</script>
