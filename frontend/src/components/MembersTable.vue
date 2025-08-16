<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-amber-400 mb-2">Membres Actifs</h2>
      <p class="text-gray-400">{{ filteredMembers.length }} membre(s) trouvé(s)</p>
    </div>

    <!-- Members Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="({ member, id }) in filteredMembers"
        :key="`member-${member.id}`"
        class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-amber-500/50 transition-all duration-300 group"
      >
        <!-- Member Header -->
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center space-x-5">
            <!-- Class Icon -->
            <ClassDropdown
              :class-name="member.class"
              :classes="classes"
              :entity-id="member.id"
              :entity-type="'character'"
              @update-class="updateCharacterClass"
            />
            <div>
              <h3 class="text-3xl font-bold text-amber-400 mb-3 group-hover:text-yellow-300 transition-colors duration-300 drop-shadow-lg">
                <EditablePseudo
                  :entity="member"
                  :entity-type="'character'"
                  :editing-pseudo="editingPseudo"
                  :edit-pseudo="editPseudo"
                  @start-editing="startEditingPseudo"
                  @save-pseudo="savePseudo"
                />
              </h3>
              <div class="space-y-2">
                <button 
                  @click="toggleAnkamaDisplay(member.id)"
                  class="text-gray-400 text-sm hover:text-amber-400 transition-colors duration-200 flex items-center space-x-2"
                  :title="ankamaDisplayed[member.id] ? 'Masquer Ankama ID' : 'Afficher Ankama ID'"
                >
                  <span class="text-amber-400 font-semibold">Ankama:</span> 
                  <span v-if="ankamaDisplayed[member.id]">{{ member.ankamaPseudo }}</span>
                  <span v-else class="text-gray-600">••••••••</span>
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Actions Menu -->
          <div class="flex items-center space-x-3">
            <button
              @click="viewWarnings(member.id, member.pseudo)"
              class="p-3 text-red-400 hover:text-red-300 hover:bg-red-900/20 rounded-xl transition-all duration-200 flex items-center space-x-2"
              :title="`${(characterWarningCounts && characterWarningCounts[member.id]) || 0} avertissement(s)`"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 
                        1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 
                        0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <span class="text-sm font-bold text-red-400">
                {{ (characterWarningCounts && characterWarningCounts[member.id]) || 0 }}
              </span>
            </button>
            <button
              @click="openModal(member)"
              class="p-3 text-gray-400 hover:text-red-400 hover:bg-red-900/20 rounded-xl transition-all duration-200"
              title="Archiver le membre"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" />
              </svg>
            </button>
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

        <!-- Arrival Date -->
        <div class="text-center p-4 bg-black/30 rounded-xl border border-gray-800 mb-6">
          <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Arrivée</p>
          <p class="text-sm text-white font-medium">
            {{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : 'Date inconnue' }}
          </p>
        </div>

        <!-- Mules Section -->
        <div class="border-t border-gray-800 pt-6">
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Mules</h4>
            <button
              v-if="filteredMulesByCharacter && filteredMulesByCharacter(id) && filteredMulesByCharacter(id).length > 0"
              @click="toggleExpand(id)"
              class="text-amber-400 hover:text-amber-300 text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
            >
              <span>{{ filteredMulesByCharacter(id).length }} mule(s)</span>
              <svg 
                class="w-4 h-4 transition-transform duration-200"
                :class="{ 'rotate-180': localExpandedRows[id] }"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <span v-else class="text-gray-500 text-sm">Aucune mule</span>
          </div>

          <!-- Expanded Mules -->
          <div v-if="localExpandedRows[id]" class="space-y-4">
            <div
              v-for="(mule, muleIndex) in filteredMulesByCharacter(id)"
              :key="`mule-${mule.id}-${muleIndex}`"
              class="bg-black/50 rounded-xl p-4 border border-gray-700 hover:border-amber-500/30 transition-all duration-200"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <ClassDropdown
                    :class-name="mule.class"
                    :classes="classes"
                    :entity-id="mule.id"
                    :entity-type="'mule'"
                    @update-class="updateMuleClass"
                  />
                  <div>
                    <h5 class="text-lg font-bold text-amber-300 mb-2 drop-shadow-lg">
                      <EditablePseudo
                        :entity="mule"
                        :entity-type="'mule'"
                        :editing-pseudo="editingPseudo"
                        :edit-pseudo="editPseudo"
                        @start-editing="startEditingPseudo"
                        @save-pseudo="savePseudo"
                      />
                    </h5>
                    <p class="text-gray-400 text-sm">
                      <span class="text-amber-400 font-semibold">Ankama:</span> 
                      <span v-if="ankamaDisplayed[`mule-${mule.id}`]">{{ mule.ankamaPseudo }}</span>
                      <span v-else class="text-gray-600">••••••••</span>
                      <button 
                        @click="toggleAnkamaDisplay(`mule-${mule.id}`)"
                        class="ml-2 text-gray-500 hover:text-amber-400 transition-colors duration-200"
                        :title="ankamaDisplayed[`mule-${mule.id}`] ? 'Masquer' : 'Afficher'"
                      >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                    </p>
                  </div>
                </div>
                <button
                  @click="openMuleModal(mule)"
                  class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-900/20 rounded-lg transition-all duration-200"
                  title="Archiver la mule"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Mules Preview (when not expanded) -->
          <div v-else-if="filteredMulesByCharacter && filteredMulesByCharacter(id) && filteredMulesByCharacter(id).length > 0" class="mt-4">
            <div class="flex flex-wrap gap-3 justify-center">
              <div
                v-for="(mule, muleIndex) in filteredMulesByCharacter(id)"
                :key="`preview-mule-${mule.id}-${muleIndex}`"
                class="group relative"
                :title="`${mule.pseudo} (${mule.class})`"
              >
                <img
                  :src="classes[mule.class]"
                  :alt="`Classe ${mule.class}`"
                  class="w-12 h-12 rounded-lg border-2 border-gray-700 group-hover:border-amber-500 transition-all duration-300 cursor-pointer"
                  @click="toggleExpand(id)"
                />
              </div>
            </div>
            <p class="text-center text-xs text-gray-500 mt-2">Cliquez sur une icône pour voir les détails</p>
          </div>

          <!-- Empty Mules State -->
          <div v-else-if="!filteredMulesByCharacter || !filteredMulesByCharacter(id) || filteredMulesByCharacter(id).length === 0" class="text-center py-6">
            <button
              @click="openAddMuleModal(member)"
              class="group inline-flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-gray-600 hover:border-amber-500 hover:bg-amber-500/10 transition-all duration-300"
              title="Ajouter une mule pour ce personnage"
            >
              <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mb-3 group-hover:bg-amber-500/20 transition-all duration-300">
                <svg class="w-8 h-8 text-gray-400 group-hover:text-amber-400 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <p class="text-sm text-gray-400 group-hover:text-amber-400 transition-colors duration-300">Ajouter une mule</p>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredMembers.length === 0" class="text-center py-16 text-gray-500">
      <svg class="w-20 h-20 mx-auto mb-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <p class="text-xl font-medium mb-2">Aucun membre trouvé</p>
      <p class="text-gray-400">Essayez de modifier vos critères de recherche</p>
    </div>
  </div>
</template>

<script>
import ClassDropdown from './ClassDropdown.vue';
import EditablePseudo from './EditablePseudo.vue';

export default {
  name: 'MembersTable',
  components: {
    ClassDropdown,
    EditablePseudo,
  },
  data() {
    return {
      localExpandedRows: {},
      ankamaDisplayed: {}, // New state for Ankama ID display
    };
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
      required: false,
      default: () => ({ type: null, id: null }),
    },
    editPseudo: {
      type: String,
      required: false,
      default: '',
    },
  },
  emits: [
    'open-modal',
    'view-warnings',
    'update-character-class',
    'update-mule-class',
    'start-editing-pseudo',
    'save-pseudo',
    'open-mule-modal',
    'open-add-mule-modal',
  ],
  methods: {
    toggleExpand(memberId) {
      this.localExpandedRows[memberId] = !this.localExpandedRows[memberId];
    },
    openModal(member) {
      this.$emit('open-modal', member);
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
    startEditingPseudo(id, currentPseudo, type) {
      this.$emit('start-editing-pseudo', id, currentPseudo, type);
    },
    savePseudo(entity, type, newPseudo) {
      this.$emit('save-pseudo', entity, type, newPseudo);
    },
    openMuleModal(mule) {
      this.$emit('open-mule-modal', mule);
    },
    toggleAnkamaDisplay(characterId) {
      this.ankamaDisplayed[characterId] = !this.ankamaDisplayed[characterId];
    },
    openAddMuleModal(member) {
      this.$emit('open-add-mule-modal', member);
    },
  },
};
</script>
