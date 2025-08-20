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
        class="bg-theme-card border border-theme-border rounded-2xl p-6 hover:border-theme-primary/50 transition-all duration-300 group"
      >
        <!-- Member Header -->
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center space-x-5">
            <!-- Class Icon -->
            <img
              :src="classes[member.class]"
              :alt="`Classe ${member.class}`"
              class="w-20 h-20 rounded-xl border-2 border-theme-bg-muted group-hover:border-theme-primary/50 transition-all duration-300"
            />
            <div>
              <h3 class="text-3xl font-bold text-theme-primary mb-3 group-hover:text-theme-primary-hover transition-colors duration-300 drop-shadow-lg">
                {{ member.pseudo || 'Inconnu' }}
              </h3>
              <div class="space-y-2">
                <p class="text-theme-text text-base font-medium capitalize">{{ member.class }}</p>
              </div>
            </div>
          </div>
          
          <!-- Archive Status -->
          <div class="flex items-center space-x-2">
            <div class="px-3 py-1 bg-theme-error/30 border border-theme-error rounded-full">
              <span class="text-theme-error text-xs font-medium">Archivé</span>
            </div>
          </div>
        </div>

        <!-- Member Details -->
        <div class="grid grid-cols-2 gap-5 mb-6">
          <div class="text-center p-4 bg-theme-bg/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Rang</p>
            <p class="text-sm text-theme-text font-medium">{{ member?.rank?.name || 'Aucun' }}</p>
          </div>
          <div class="text-center p-4 bg-theme-bg/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Recruteur</p>
            <p class="text-sm text-theme-text font-medium">{{ member?.recruiter?.pseudo || 'Aucun' }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center">
          <button
            @click="openUnarchivedCharacterModal(member)"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-theme-primary to-theme-primary-hover text-theme-bg font-bold rounded-xl hover:from-theme-primary hover:to-theme-primary-hover focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg transition-all duration-300 shadow-lg hover:shadow-xl shadow-theme-primary/30 hover:shadow-theme-primary/50 transform hover:-translate-y-1"
          >
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
export default {
  name: 'ArchivedMembersTable',
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
