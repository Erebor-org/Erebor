<template>
  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden shadow-xl">
    <!-- Table Header -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
      <h3 class="text-xl font-bold text-amber-400">Membres Archivés</h3>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-800 border-b border-gray-700">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Membre
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Rang
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Recruteur
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-gray-900 divide-y divide-gray-700">
          <tr
            v-for="member in filteredArchivedMembers"
            :key="`archived-${member.id}`"
            class="hover:bg-gray-800 transition-colors duration-200"
          >
              <!-- Member Info -->
              <td class="px-6 py-4">
                <div class="flex items-center space-x-4">
                  <div class="relative">
                    <img
                      :src="classes[member.member.class]"
                      :alt="`Classe ${member.member.class}`"
                      class="w-12 h-12 rounded-lg border-2 border-gray-600 opacity-75"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-lg font-bold text-gray-300">{{ member.member.pseudo }}</p>
                    <p class="text-sm text-gray-500 truncate">
                      {{ member.member.ankama_pseudo }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Rank -->
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-400 border border-gray-600">
                  {{ member.member.rank?.name || 'N/A' }}
                </span>
              </td>

              <!-- Recruiter -->
              <td class="px-6 py-4">
                <span class="text-gray-400">
                  {{ member.member.recruiter?.pseudo || 'N/A' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <button
                  @click="openUnarchivedCharacterModal(member)"
                  class="px-4 py-2 text-sm bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors duration-200"
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
