<template>
  <div class="glass-card rounded-2xl overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-theme-border">
      <h3 class="text-lg font-serif font-bold text-theme-text">Membres Archivés</h3>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="border-b border-theme-border">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-theme-text-muted uppercase tracking-wider">
              Membre
            </th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-theme-text-muted uppercase tracking-wider">
              Rang
            </th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-theme-text-muted uppercase tracking-wider">
              Recruteur
            </th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-theme-text-muted uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-theme-border">
          <tr
            v-for="member in filteredArchivedMembers"
            :key="`archived-${member.id}`"
            class="hover:bg-theme-primary/5 transition-colors duration-200"
          >
              <!-- Member Info -->
              <td class="px-6 py-3.5">
                <div class="flex items-center space-x-3">
                  <span class="portrait-ring w-10 h-10 grayscale">
                    <img :src="classes[member.member.class]" :alt="`Classe ${member.member.class}`" />
                  </span>
                  <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-theme-text">{{ member.member.pseudo }}</p>
                  </div>
                </div>
              </td>

              <!-- Rank -->
              <td class="px-6 py-3.5">
                <RankBadge :rank="member.member.rank" size="sm" />
              </td>

              <!-- Recruiter -->
              <td class="px-6 py-3.5">
                <span class="text-theme-text-muted text-sm">
                  {{ member.member.recruiter?.pseudo || 'N/A' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-3.5">
                <button
                  @click="openUnarchivedCharacterModal(member)"
                  class="px-4 py-2 text-sm bg-theme-primary/10 hover:bg-theme-primary text-theme-primary hover:text-white font-semibold rounded-lg border border-theme-primary/40 transition-all duration-200"
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
import RankBadge from './RankBadge.vue';

export default {
  name: 'ArchivedMembersTableList',
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
  methods: {
    openUnarchivedCharacterModal(character) {
      this.$emit('open-unarchived-character-modal', character);
    },
  },
  emits: ['open-unarchived-character-modal'],
};
</script>
