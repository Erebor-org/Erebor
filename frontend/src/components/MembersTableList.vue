<template>
  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden shadow-xl">
    <!-- Table Header -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
      <h3 class="text-xl font-bold text-amber-400">Membres Actifs</h3>
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
              Mules
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Avertissements
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-gray-900 divide-y divide-gray-700">
          <tr
            v-for="member in filteredMembers"
            :key="`member-${member.id}`"
            class="hover:bg-gray-800 transition-colors duration-200"
          >
            <!-- Member Info -->
            <td class="px-6 py-4">
              <div class="flex items-center space-x-4">
                <div class="relative">
                  <ClassDropdown
                    :class-name="member.member.class"
                    :classes="classes"
                    :entity-id="member.member.id"
                    :entity-type="'character'"
                    @update-class="updateCharacterClass"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <EditablePseudo
                    :entity="member.member"
                    :entity-type="'character'"
                    :editing-pseudo="editingPseudo"
                    :edit-pseudo="editPseudo"
                    @save-pseudo="savePseudo"
                  />
                  <p class="text-sm text-gray-400 truncate">
                    {{ member.member.ankama_pseudo }}
                  </p>
                </div>
              </div>
            </td>

            <!-- Rank -->
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-amber-400 border border-gray-600">
                {{ member.member.rank?.name || 'N/A' }}
              </span>
            </td>

            <!-- Recruiter -->
            <td class="px-6 py-4">
              <span class="text-gray-300">
                {{ member.member.recruiter?.pseudo || 'N/A' }}
              </span>
            </td>

            <!-- Mules Count -->
            <td class="px-6 py-4">
              <button
                @click="openMulesModal(member)"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-300 hover:bg-gray-600 hover:text-amber-400 transition-colors duration-200 border border-gray-600"
              >
                <span class="mr-2">{{ filteredMulesByCharacter(member.id).length }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </button>
            </td>

            <!-- Warnings -->
            <td class="px-6 py-4">
              <div class="flex items-center space-x-2">
                <div class="flex flex-col items-center">
                  <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-300">{{ characterWarningCounts[member.id] || 0 }}</span>
                </div>
                <button
                  @click="viewWarnings(member.id, member.member)"
                  class="ml-2 px-2 py-1 text-xs bg-gray-700 hover:bg-gray-600 text-gray-300 rounded transition-colors duration-200"
                >
                  Voir
                </button>
              </div>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4">
              <div class="flex items-center space-x-2">
                <button
                  @click="openModal(member)"
                  class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition-colors duration-200"
                >
                  Archiver
                </button>
                <button
                  @click="openMuleModal(member)"
                  class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200"
                >
                  Gérer Mules
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { reactive } from 'vue';
import EditablePseudo from './EditablePseudo.vue';
import ClassDropdown from './ClassDropdown.vue';

export default {
  name: 'MembersTableList',
  components: {
    EditablePseudo,
    ClassDropdown,
  },
  props: {
    filteredMembers: {
      type: Array,
      required: true,
    },
    classes: {
      type: Object,
      required: true,
    },
    filteredMulesByCharacter: {
      type: Function,
      required: true,
    },
    characterWarningCounts: {
      type: Object,
      required: true,
    },
    editingPseudo: {
      type: Object,
      default: () => ({ type: null, id: null }),
    },
    editPseudo: {
      type: String,
      default: '',
    },
  },
  setup() {
    const localExpandedRows = reactive({});
    
    return {
      localExpandedRows,
    };
  },

  methods: {
    savePseudo(entity, type, newPseudo) {
      this.$emit('save-pseudo', entity, type, newPseudo);
    },
    openModal(member) {
      this.$emit('open-modal', member);
    },
    openMuleModal(mule) {
      this.$emit('open-mule-modal', mule);
    },
    viewWarnings(characterId, member) {
      this.$emit('view-warnings', characterId, member);
    },
    updateCharacterClass(characterId, newClass) {
      this.$emit('update-character-class', characterId, newClass);
    },
    updateMuleClass(muleId, newClass) {
      this.$emit('update-mule-class', muleId, newClass);
    },
    openAddMuleModal(member) {
      this.$emit('open-add-mule-modal', member);
    },
    openMulesModal(member) {
      this.$emit('open-mules-modal', member);
    },
  },
  emits: [
    'save-pseudo',
    'open-modal',
    'open-mule-modal',
    'view-warnings',
    'update-character-class',
    'update-mule-class',
    'open-add-mule-modal',
    'open-mules-modal',
  ],
};
</script>
