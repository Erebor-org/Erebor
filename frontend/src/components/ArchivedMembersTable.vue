<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-amber-400 mb-2">Membres Archivés</h2>
      <p class="text-gray-400">{{ filteredArchivedMembers.length }} membre(s) archivé(s)</p>
    </div>

    <!-- Archived Members Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="({ member }) in filteredArchivedMembers"
        :key="member.id"
        class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-amber-500/50 transition-all duration-300 group"
      >
        <!-- Member Header -->
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center space-x-5">
            <!-- Class Icon -->
            <img
              :src="classes[member.class]"
              :alt="`Classe ${member.class}`"
              class="w-20 h-20 rounded-xl border-2 border-gray-700 group-hover:border-amber-500/50 transition-all duration-300"
            />
            <div>
              <h3 class="text-3xl font-bold text-amber-400 mb-3 group-hover:text-yellow-300 transition-colors duration-300 drop-shadow-lg">
                {{ member.pseudo || 'Inconnu' }}
              </h3>
              <div class="space-y-2">
                <p class="text-gray-300 text-base font-medium capitalize">{{ member.class }}</p>
              </div>
            </div>
          </div>
          
          <!-- Archive Status -->
          <div class="flex items-center space-x-2">
            <div class="px-3 py-1 bg-red-900/30 border border-red-800 rounded-full">
              <span class="text-red-400 text-xs font-medium">Archivé</span>
            </div>
          </div>
        </div>

        <!-- Member Details -->
        <div class="grid grid-cols-2 gap-5 mb-6">
          <div class="text-center p-4 bg-black/30 rounded-xl border border-gray-800">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Rang</p>
            <p class="text-sm text-white font-medium">{{ member?.rank?.name || 'Aucun' }}</p>
          </div>
          <div class="text-center p-4 bg-black/30 rounded-xl border border-gray-800">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Recruteur</p>
            <p class="text-sm text-white font-medium">{{ member?.recruiter?.pseudo || 'Aucun' }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-center">
          <button
            @click="openUnarchivedCharacterModal(member)"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-yellow-600 text-black font-bold rounded-xl hover:from-amber-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-300 shadow-lg hover:shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 transform hover:-translate-y-1"
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
    <div v-if="filteredArchivedMembers.length === 0" class="text-center py-16 text-gray-500">
      <svg class="w-20 h-20 mx-auto mb-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" />
      </svg>
      <p class="text-xl font-medium mb-2">Aucun membre archivé</p>
      <p class="text-gray-400">Tous vos membres sont actuellement actifs</p>
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
