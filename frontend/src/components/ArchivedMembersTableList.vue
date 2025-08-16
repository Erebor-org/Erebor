<template>
  <div class="bg-theme-card rounded-2xl border border-theme-border overflow-hidden shadow-xl">
    <!-- Table Header -->
    <div class="bg-gradient-to-r from-theme-bg-muted to-theme-card px-6 py-4 border-b border-theme-bg-muted">
      <h3 class="text-xl font-bold text-theme-primary">Membres Archivés</h3>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-theme-bg-muted border-b border-theme-bg-muted">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-medium text-theme-text uppercase tracking-wider">
              Membre
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-theme-text uppercase tracking-wider">
              Rang
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-theme-text uppercase tracking-wider">
              Recruteur
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-theme-text uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-theme-card divide-y divide-gray-700">
          <tr
            v-for="member in filteredArchivedMembers"
            :key="`archived-${member.id}`"
            class="hover:bg-theme-bg-muted transition-colors duration-200"
          >
              <!-- Member Info -->
              <td class="px-6 py-4">
                <div class="flex items-center space-x-4">
                  <div class="relative">
                    <img
                      :src="classes[member.member.class]"
                      :alt="`Classe ${member.member.class}`"
                      class="w-12 h-12 rounded-lg border-2 border-theme-border opacity-75"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-lg font-bold text-theme-text">{{ member.member.pseudo }}</p>
                    <p class="text-sm text-theme-text-muted truncate">
                      {{ member.member.ankama_pseudo }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Rank -->
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-theme-bg-muted text-theme-text-muted border border-theme-border">
                  {{ member.member.rank?.name || 'N/A' }}
                </span>
              </td>

              <!-- Recruiter -->
              <td class="px-6 py-4">
                <span class="text-theme-text-muted">
                  {{ member.member.recruiter?.pseudo || 'N/A' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <button
                  @click="openUnarchivedCharacterModal(member)"
                  class="px-4 py-2 text-sm bg-theme-primary hover:bg-theme-primary-hover text-theme-text rounded-lg transition-colors duration-200"
                >
                  Restaurer
                </button>
              </td>
            </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ArchivedMembersTableList',
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
  methods: {
    openUnarchivedCharacterModal(character) {
      this.$emit('open-unarchived-character-modal', character);
    },
  },
  emits: ['open-unarchived-character-modal'],
};
</script>
