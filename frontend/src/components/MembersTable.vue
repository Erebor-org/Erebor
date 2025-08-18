<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-theme-primary mb-2">Membres Actifs</h2>
      <p class="text-theme-text-muted">{{ filteredMembers.length }} membre(s) trouvé(s)</p>
    </div>

    <!-- Members Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="({ member, id }) in filteredMembers"
        :key="`member-${member.id}`"
        class="bg-theme-card border border-theme-border rounded-2xl p-6 hover:border-theme-primary/50 transition-all duration-300 group"
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
              <h3 class="text-3xl font-bold text-theme-primary mb-3 group-hover:text-theme-primary-hover transition-colors duration-300 drop-shadow-lg">
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
                  class="text-theme-text-muted text-sm hover:text-theme-primary transition-colors duration-200 flex items-center space-x-2"
                  :title="ankamaDisplayed[member.id] ? 'Masquer Ankama ID' : 'Afficher Ankama ID'"
                >
                  <span class="text-theme-primary font-semibold">Ankama:</span> 
                  <span v-if="ankamaDisplayed[member.id]">{{ member.ankamaPseudo }}</span>
                  <span v-else class="text-theme-text-muted">••••••••</span>
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
              class="p-3 text-theme-warning hover:text-theme-warning hover:bg-theme-warning/20 rounded-xl transition-all duration-200 flex items-center space-x-2"
              :title="`${(characterWarningCounts && characterWarningCounts[member.id]) || 0} avertissement(s)`"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 
                        1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 
                        0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <span class="text-sm font-bold text-theme-warning">
                {{ (characterWarningCounts && characterWarningCounts[member.id]) || 0 }}
              </span>
            </button>
            <button
              @click="openModal(member)"
              class="p-3 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/20 rounded-xl transition-all duration-200"
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
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Rang</p>
            <p class="text-sm text-theme-text font-medium">{{ member?.rank?.name || 'Aucun' }}</p>
          </div>
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Recruteur</p>
            <p class="text-sm text-theme-text font-medium">{{ member?.recruiter?.pseudo || 'Aucun' }}</p>
          </div>
        </div>

        <!-- Arrival Date -->
        <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border mb-6">
          <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Arrivée</p>
          <p class="text-sm text-theme-text font-medium">
            {{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : 'Date inconnue' }}
          </p>
        </div>

            <!-- Notes Modal Trigger -->
            <div class="mt-4 mb-4">
              <button
                @click="openNotesModal(member)"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-theme-bg-muted hover:bg-theme-primary/10 border border-theme-primary rounded-xl text-theme-primary hover:text-theme-primary-hover font-semibold text-base transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-primary/30"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
                <span>{{ member.notes && member.notes.trim() !== '' ? 'Voir la note' : 'Ajouter une note' }}</span>
              </button>
            </div>
        <!-- Mules Section -->
        <div class="border-t border-theme-border pt-6">
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-semibold text-theme-text uppercase tracking-wider">Mules</h4>
            <button
              v-if="filteredMulesByCharacter && filteredMulesByCharacter(id) && filteredMulesByCharacter(id).length > 0"
              @click="toggleExpand(id)"
              class="text-theme-primary hover:text-theme-primary text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
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
            <span v-else class="text-theme-text-muted text-sm">Aucune mule</span>
          </div>

          <!-- Expanded Mules -->
          <div v-if="localExpandedRows[id]" class="space-y-4">
            <div
              v-for="(mule, muleIndex) in filteredMulesByCharacter(id)"
              :key="`mule-${mule.id}-${muleIndex}`"
              class="bg-theme-bg/50 rounded-xl p-4 border border-theme-bg-muted hover:border-theme-primary/30 transition-all duration-200"
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
                    <h5 class="text-lg font-bold text-theme-primary mb-2 drop-shadow-lg">
                      <EditablePseudo
                        :entity="mule"
                        :entity-type="'mule'"
                        :editing-pseudo="editingPseudo"
                        :edit-pseudo="editPseudo"
                        @start-editing="startEditingPseudo"
                        @save-pseudo="savePseudo"
                      />
                    </h5>
                    <p class="text-theme-text-muted text-sm">
                      <span class="text-theme-primary font-semibold">Ankama:</span> 
                      <span v-if="ankamaDisplayed[`mule-${mule.id}`]">{{ mule.ankamaPseudo }}</span>
                      <span v-else class="text-theme-text-muted">••••••••</span>
                      <button 
                        @click="toggleAnkamaDisplay(`mule-${mule.id}`)"
                        class="ml-2 text-theme-text-muted hover:text-theme-primary transition-colors duration-200"
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
                <div class="flex flex-col items-end space-y-2">
                  <button
                    @click="openMuleModal(mule)"
                    class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/20 rounded-lg transition-all duration-200"
                    title="Archiver la mule"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmSwitchWithMule(member, mule)"
                    class="p-2 text-theme-primary hover:bg-theme-primary/10 rounded-lg transition-all duration-200 text-xs font-semibold border border-theme-primary"
                    title="Échanger ce personnage principal avec cette mule"
                  >
                    Switch avec ce main
                  </button>
                </div>
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
                  class="w-12 h-12 rounded-lg border-2 border-theme-bg-muted group-hover:border-theme-primary transition-all duration-300 cursor-pointer"
                  @click="toggleExpand(id)"
                />
              </div>
            </div>
            <p class="text-center text-xs text-theme-text-muted mt-2">Cliquez sur une icône pour voir les détails</p>
          </div>

          <!-- Empty Mules State -->
          <div v-else-if="!filteredMulesByCharacter || !filteredMulesByCharacter(id) || filteredMulesByCharacter(id).length === 0" class="text-center py-6">
            <button
              @click="openAddMuleModal(member)"
              class="group inline-flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-theme-border hover:border-theme-primary hover:bg-theme-primary/10 transition-all duration-300"
              title="Ajouter une mule pour ce personnage"
            >
              <div class="w-16 h-16 bg-theme-bg-muted rounded-full flex items-center justify-center mb-3 group-hover:bg-theme-primary/20 transition-all duration-300">
                <svg class="w-8 h-8 text-theme-text-muted group-hover:text-theme-primary transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <p class="text-sm text-theme-text-muted group-hover:text-theme-primary transition-colors duration-300">Ajouter une mule</p>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredMembers.length === 0" class="text-center py-16 text-theme-text-muted">
      <svg class="w-20 h-20 mx-auto mb-6 text-theme-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <p class="text-xl font-medium mb-2">Aucun membre trouvé</p>
      <p class="text-theme-text-muted">Essayez de modifier vos critères de recherche</p>
    </div>
    <ConfirmModal
      :show="showConfirmSwitch"
      title="Confirmer le switch"
      :message="confirmSwitchMessage"
      confirmText="Oui, switcher"
      @confirm="doSwitchWithMule"
      @cancel="showConfirmSwitch = false"
    />
  </div>
</template>

<script>
import ClassDropdown from './ClassDropdown.vue';
import EditablePseudo from './EditablePseudo.vue';
import ConfirmModal from './ConfirmModal.vue';
const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'MembersTable',
  components: {
    ClassDropdown,
    EditablePseudo,
    ConfirmModal,
  },
  data() {
    return {
      localExpandedRows: {},
      ankamaDisplayed: {}, // New state for Ankama ID display
      showTooltip: null,
      expandedNotes: {},
      editableNotes: {},
      editingNoteId: null,
      editingNoteValue: '',
      switchLoading: false,
      showConfirmSwitch: false,
      switchMain: null,
      switchMule: null,
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
    'open-notes-modal',
    'save-note',
    'refresh-data',
  ],
  computed: {
    confirmSwitchMessage() {
      if (!this.switchMain || !this.switchMule) return '';
      return `Êtes-vous sûr de vouloir échanger ${this.switchMain.pseudo} avec la mule ${this.switchMule.pseudo} ?`;
    },
  },
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
    openNotesModal(member) {
      this.$emit('open-notes-modal', member);
    },
    toggleNotesExpand(memberId) {
      this.expandedNotes[memberId] = !this.expandedNotes[memberId];
      if (this.expandedNotes[memberId]) {
        this.editableNotes[memberId] = this.filteredMembers.find(m => m.member.id === memberId)?.member.notes || '';
      }
    },
    saveNote(memberId) {
      // Emit event to parent to save note (API call should be handled in parent)
      this.$emit('save-note', memberId, this.editableNotes[memberId]);
    },
    startEditingNote(id, value) {
      this.editingNoteId = id;
      this.editingNoteValue = value || '';
    },
    cancelEditingNote() {
      this.editingNoteId = null;
      this.editingNoteValue = '';
    },
    saveEditingNote(id) {
      this.$emit('save-note', id, this.editingNoteValue);
      this.editingNoteId = null;
      this.editingNoteValue = '';
    },
    async confirmSwitchWithMule(main, mule) {
      this.switchMain = main;
      this.switchMule = mule;
      this.showConfirmSwitch = true;
    },
    async doSwitchWithMule() {
      this.showConfirmSwitch = false;
      this.switchLoading = true;
      try {
        const response = await fetch(`${API_URL}/characters/${this.switchMain.id}/switch-with-mule/${this.switchMule.id}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
        });
        if (!response.ok) throw new Error('Erreur lors du switch');
        this.$emit('refresh-data');
      } catch (e) {
        this.$emit('refresh-data');
      } finally {
        this.switchLoading = false;
        this.switchMain = null;
        this.switchMule = null;
      }
    },
  },
};
</script>
