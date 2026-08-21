<template>
  <div class="space-y-6">
    <!-- Members Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="({ member, id }) in filteredMembers"
        :key="`member-${member.id}`"
        class="glass-card fiche-card rounded-2xl p-5 hover:border-theme-primary/50 transition-all duration-300 group"
      >
        <!-- Signalements fantôme (total) -->
        <span
          v-if="ghostTotalVotes[member.id]"
          class="fiche-ghost-pastille"
          :title="`Signalé ${ghostTotalVotes[member.id]} fois comme fantôme au total`"
        >
          👻 {{ ghostTotalVotes[member.id] }}
        </span>

        <!-- Archiver -->
        <button
          @click="openModal(member)"
          class="fiche-archive-btn"
          title="Archiver le membre"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" />
          </svg>
        </button>

        <!-- Portrait + rang -->
        <div class="fiche-portrait-wrap">
          <ClassDropdown
            :class-name="member.class"
            :classes="classes"
            :entity-id="member.id"
            :entity-type="'character'"
            @update-class="updateCharacterClass"
          />
          <span class="fiche-rank-pill"><RankBadge :rank="member.rank" size="sm" /></span>
        </div>

        <h3 class="fiche-name">
          <EditablePseudo
            :entity="member"
            :entity-type="'character'"
            :editing-pseudo="editingPseudo"
            :edit-pseudo="editPseudo"
            @start-editing="startEditingPseudo"
            @save-pseudo="savePseudo"
          />
        </h3>
        <button
          @click="toggleAnkamaDisplay(member.id)"
          class="fiche-ankama"
          :title="ankamaDisplayed[member.id] ? 'Masquer Ankama ID' : 'Afficher Ankama ID'"
        >
          <span v-if="ankamaDisplayed[member.id]">{{ member.ankamaPseudo }}</span>
          <span v-else>••••••••</span>
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>

        <!-- Tuiles de statistiques -->
        <div class="fiche-tiles">
          <button class="fiche-tile" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
            <span class="l">Recruteur</span>
            <span class="v">{{ member?.recruiter?.pseudo || '—' }}</span>
          </button>
          <button class="fiche-tile" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
            <span class="l">Arrivée</span>
            <span class="v">{{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : '—' }}</span>
          </button>
          <button
            class="fiche-tile"
            :class="{ 'fiche-tile--active': localExpandedRows[id] }"
            @click="toggleExpand(id)"
          >
            <span class="l">Mules</span>
            <span class="v">{{ filteredMulesByCharacter(id).length }}</span>
          </button>
          <button
            class="fiche-tile"
            @click="viewWarnings(member.id, member.pseudo)"
            :class="{ 'fiche-tile--warning': (characterWarningCounts && characterWarningCounts[member.id]) > 0 }"
          >
            <span class="l">Avert.</span>
            <span class="v">{{ (characterWarningCounts && characterWarningCounts[member.id]) || 0 }}</span>
          </button>
        </div>

        <!-- Notes Modal Trigger -->
        <div class="mb-3 w-full">
          <button
            @click="openNotesModal(member)"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-theme-primary/5 hover:bg-theme-primary/10 border border-theme-primary/40 rounded-xl text-theme-primary hover:text-theme-primary-hover font-semibold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-theme-primary/30"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
            <span>{{ member.notes && member.notes.trim() !== '' ? 'Voir la note' : 'Ajouter une note' }}</span>
          </button>
        </div>

        <!-- Signaler comme fantôme -->
        <div class="mb-3 w-full">
          <button
            @click="toggleGhostVote(member)"
            class="fiche-ghost-toggle"
            :class="{ 'fiche-ghost-toggle--active': ghostVotedCharacterIds.has(member.id) }"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2a7 7 0 0 0-7 7v11.5a.5.5 0 0 0 .82.38L7.5 19l1.7 1.62a.75.75 0 0 0 1 0L12 19l1.8 1.62a.75.75 0 0 0 1 0L16.5 19l1.68 1.88A.5.5 0 0 0 19 20.5V9a7 7 0 0 0-7-7z" />
              <circle cx="9.5" cy="10" r="1.25" />
              <circle cx="14.5" cy="10" r="1.25" />
            </svg>
            <span>{{ ghostVotedCharacterIds.has(member.id) ? 'Signalé comme fantôme' : 'Signaler comme fantôme' }}</span>
            <span v-if="ghostVoteCounts[member.id]" class="fiche-ghost-count">{{ ghostVoteCounts[member.id] }}</span>
          </button>
        </div>
        <!-- Mules (repliées par défaut, dépliées via la tuile "Mules") -->
        <div v-if="localExpandedRows[id]" class="fiche-mules w-full text-left">
          <div class="flex items-center justify-between mb-2">
            <span class="fiche-mules-title">Mules</span>
            <button @click="toggleExpand(id)" class="fiche-mules-collapse">Réduire</button>
          </div>

          <div class="space-y-1.5">
            <div
              v-for="(mule, muleIndex) in filteredMulesByCharacter(id)"
              :key="`mule-${mule.id}-${muleIndex}`"
              class="fiche-mule-row"
            >
              <span @click.stop>
                <ClassDropdown
                  :class-name="mule.class"
                  :classes="classes"
                  :entity-id="mule.id"
                  :entity-type="'mule'"
                  size="sm"
                  @update-class="updateMuleClass"
                />
              </span>
              <div class="flex-1 min-w-0" @click.stop>
                <EditablePseudo
                  :entity="mule"
                  :entity-type="'mule'"
                  :editing-pseudo="editingPseudo"
                  :edit-pseudo="editPseudo"
                  @start-editing="startEditingPseudo"
                  @save-pseudo="savePseudo"
                />
              </div>
              <button
                @click="confirmSwitchWithMule(member, mule)"
                class="meta-chip hover:bg-theme-primary/15 transition-colors duration-200"
                title="Échanger ce personnage principal avec cette mule"
              >
                Switch
              </button>
              <button
                @click="openMuleModal(mule)"
                class="p-1.5 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200"
                title="Archiver la mule"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>

            <button @click="openAddMuleModal(member)" class="fiche-mule-add-row">
              <span class="fiche-mule-add-icon">+</span>
              <span>Ajouter une mule</span>
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
    <UpdateRecruitmentModal
      :show="showRecruitmentModal"
      :character="selectedCharacter"
      @close="closeRecruitmentModal"
      @saved="handleRecruitmentSaved"
    />
  </div>
</template>

<script>
import ClassDropdown from './ClassDropdown.vue';
import EditablePseudo from './EditablePseudo.vue';
import ConfirmModal from './ConfirmModal.vue';
import UpdateRecruitmentModal from './UpdateRecruitmentModal.vue';
import RankBadge from './RankBadge.vue';
import { useAuthStore } from '@/stores/authStore';
const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'MembersTable',
  components: {
    ClassDropdown,
    EditablePseudo,
    ConfirmModal,
    UpdateRecruitmentModal,
    RankBadge,
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
      showRecruitmentModal: false,
      selectedCharacter: null,
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
    totalActiveMembers: {
      type: Number,
      required: true,
    },
    ghostVotedCharacterIds: {
      type: Set,
      required: false,
      default: () => new Set(),
    },
    ghostVoteCounts: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    ghostTotalVotes: {
      type: Object,
      required: false,
      default: () => ({}),
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
    'update-recruitment',
    'toggle-ghost-vote',
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
    toggleGhostVote(member) {
      this.$emit('toggle-ghost-vote', member.id);
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
        const authStore = useAuthStore();
        const userPseudo = authStore.user?.username || '';
        const token = authStore.token || localStorage.getItem('token');
        const headers = { 'Content-Type': 'application/json' };
        if (token) {
          headers['Authorization'] = `Bearer ${token}`;
        }
        const response = await fetch(`${API_URL}/characters/${this.switchMain.id}/switch-with-mule/${this.switchMule.id}`, {
          method: 'POST',
          headers,
          body: JSON.stringify({ switchedBy: userPseudo })
        });
        if (!response.ok) throw new Error('Erreur lors du switch');
        this.$emit('refresh-data');
      } catch {
        this.$emit('refresh-data');
      } finally {
        this.switchLoading = false;
        this.switchMain = null;
        this.switchMule = null;
      }
    },
    openRecruitmentModal(member) {
      this.selectedCharacter = member;
      this.showRecruitmentModal = true;
    },
    closeRecruitmentModal() {
      this.showRecruitmentModal = false;
      this.selectedCharacter = null;
    },
    handleRecruitmentSaved(updatedCharacter) {
      // Emit event to parent to refresh data
      this.$emit('update-recruitment', updatedCharacter);
      this.$emit('refresh-data');
    },
  },
};
</script>

<style scoped>
.fiche-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.fiche-archive-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem;
  border-radius: 0.75rem;
  color: var(--text-muted);
  transition: color 0.2s, background-color 0.2s;
}
.fiche-archive-btn:hover {
  color: var(--error);
  background-color: rgba(var(--primary-rgb), 0.08);
}

.fiche-ghost-toggle {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-weight: 600;
  font-size: 0.85rem;
  transition: color 0.2s, background-color 0.2s, border-color 0.2s;
}
.fiche-ghost-toggle:hover {
  color: var(--primary);
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.06);
}
.fiche-ghost-toggle--active {
  color: white;
  border-color: var(--primary);
  background-color: var(--primary);
}
.fiche-ghost-toggle--active:hover {
  background-color: var(--primary-hover);
}
.fiche-ghost-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.35rem;
  height: 1.35rem;
  padding: 0 0.3rem;
  border-radius: 9999px;
  background-color: rgba(255, 255, 255, 0.25);
  font-size: 0.72rem;
  font-weight: 700;
}
.fiche-ghost-toggle:not(.fiche-ghost-toggle--active) .fiche-ghost-count {
  background-color: rgba(var(--primary-rgb), 0.15);
  color: var(--primary);
}

.fiche-portrait-wrap {
  position: relative;
  margin-bottom: 0.6rem;
}

.fiche-rank-pill {
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--card);
  border: 1px solid var(--border);
  border-radius: 9999px;
  padding: 0.2rem 0.65rem;
  white-space: nowrap;
}

.fiche-ghost-pastille {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background-color: var(--card);
  border: 1px solid var(--primary);
  color: var(--primary);
  border-radius: 9999px;
  padding: 0.2rem 0.55rem;
  font-size: 0.7rem;
  font-weight: 700;
  white-space: nowrap;
}

.fiche-name {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.4rem;
  color: var(--primary);
  margin: 0.6rem 0 0.3rem;
}

.fiche-ankama {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-bottom: 0.85rem;
  transition: color 0.2s;
}
.fiche-ankama:hover {
  color: var(--primary);
}

.fiche-tiles {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
  margin-bottom: 0.85rem;
}

.fiche-tile {
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  padding: 0.55rem 0.5rem;
  text-align: center;
  transition: border-color 0.2s, background-color 0.2s;
}
.fiche-tile:hover {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}
.fiche-tile .l {
  display: block;
  font-size: 0.62rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-muted);
}
.fiche-tile .v {
  display: block;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text);
  margin-top: 0.15rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.fiche-tile--warning .v {
  color: var(--warning);
}

.fiche-tile--active {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.08);
}

/* ---------- Mules (dépliées) ---------- */
.fiche-mules {
  border-top: 1px solid var(--border);
  padding-top: 0.75rem;
  margin-top: 0.15rem;
}

.fiche-mules-title {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.fiche-mules-collapse {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--primary);
}
.fiche-mules-collapse:hover {
  color: var(--primary-hover);
}

.fiche-mule-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.4rem;
  border-radius: 0.65rem;
  transition: background-color 0.2s;
}
.fiche-mule-row:hover {
  background-color: rgba(255, 255, 255, 0.02);
}

.fiche-mule-add-row {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0.6rem;
  border-radius: 0.65rem;
  border: 1px dashed var(--border);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 500;
  transition: border-color 0.2s, color 0.2s, background-color 0.2s;
}
.fiche-mule-add-row:hover {
  border-color: var(--primary);
  color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}

.fiche-mule-add-icon {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 9999px;
  border: 1px dashed currentColor;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.9rem;
  line-height: 1;
}
</style>
