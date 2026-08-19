<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-theme-primary mb-2">Membres Archivés</h2>
      <p class="text-theme-text-muted">{{ filteredArchivedMembers.length }} membre(s) archivé(s)</p>
    </div>

    <!-- Archived Members Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="({ member }) in filteredArchivedMembers"
        :key="member.id"
        class="glass-card rounded-2xl p-6 hover:border-theme-primary/40 transition-all duration-300 group opacity-80 hover:opacity-100"
      >
        <!-- Member Header -->
        <div class="flex items-start justify-between mb-5">
          <div class="flex items-center space-x-4">
            <!-- Class Icon -->
            <span class="portrait-ring w-16 h-16 grayscale group-hover:grayscale-0 transition-all duration-300">
              <img :src="classes[member.class]" :alt="`Classe ${member.class}`" />
            </span>
            <div>
              <h3 class="text-xl font-serif font-bold text-theme-text mb-1 group-hover:text-theme-primary transition-colors duration-300">
                {{ member.pseudo || 'Inconnu' }}
              </h3>
              <p class="text-theme-text-muted text-sm capitalize">{{ member.class }}</p>
            </div>
          </div>

          <!-- Archive Status -->
          <span class="meta-chip !border-theme-error/40 !text-theme-error !bg-theme-error/10">Archivé</span>
        </div>

        <!-- Member Details -->
        <div class="flex flex-wrap items-center gap-2 mb-6 text-xs text-theme-text-muted">
          <RankBadge :rank="member.rank" size="sm" />
          <span>recruté par <span class="text-theme-text font-medium">{{ member?.recruiter?.pseudo || 'personne' }}</span></span>
        </div>

        <!-- Actions -->
        <div class="flex justify-center">
          <button
            @click="openUnarchivedCharacterModal(member)"
            class="inline-flex items-center px-5 py-2.5 bg-theme-primary/10 hover:bg-theme-primary text-theme-primary hover:text-white font-semibold text-sm rounded-xl border border-theme-primary/40 focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg transition-all duration-300"
          >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Restaurer
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredArchivedMembers.length === 0" class="text-center py-16 text-theme-text-muted">
      <svg class="w-20 h-20 mx-auto mb-6 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" />
      </svg>
      <p class="text-xl font-medium mb-2">Aucun membre archivé</p>
      <p class="text-theme-text-muted">Tous vos membres sont actuellement actifs</p>
    </div>
  </div>
</template>

<script>
import RankBadge from './RankBadge.vue';

export default {
  name: 'ArchivedMembersTable',
  components: {
    RankBadge,
  },
  props: {
    filteredArchivedMembers: {
      type: Array,
      required: true,
    },
    classes: {
      type: Object,
      required: true,
    },
  },
  emits: ['open-unarchived-character-modal'],
  methods: {
    openUnarchivedCharacterModal(character) {
      this.$emit('open-unarchived-character-modal', character);
    },
  },
};
</script>
