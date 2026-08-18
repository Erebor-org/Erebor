<template>
  <div class="glass-card rounded-2xl overflow-hidden">
    <div v-for="({ member, id }) in filteredMembers" :key="`member-${member.id}`" class="ladder-row">
      <div class="ladder-row-main">
        <span @click.stop>
          <ClassDropdown
            :class-name="member.class"
            :classes="classes"
            :entity-id="member.id"
            :entity-type="'character'"
            size="sm"
            @update-class="updateCharacterClass"
          />
        </span>

        <div class="ladder-name" @click.stop>
          <EditablePseudo
            :entity="member"
            :entity-type="'character'"
            :editing-pseudo="editingPseudo"
            :edit-pseudo="editPseudo"
            @start-editing="startEditingPseudo"
            @save-pseudo="savePseudo"
          />
        </div>

        <button class="ladder-expand-trigger" @click="toggleExpand(id)">
          <RankBadge :rank="member.rank" size="sm" />
          <span class="ladder-meta-text">
            {{ filteredMulesByCharacter(id).length }} mule{{ filteredMulesByCharacter(id).length === 1 ? '' : 's' }}
            <template v-if="characterWarningCounts[member.id]"> · {{ characterWarningCounts[member.id] }} avert.</template>
          </span>
        </button>

        <div class="ladder-actions">
          <button
            @click="viewWarnings(member.id, member.pseudo)"
            class="p-2 text-theme-warning hover:bg-theme-warning/15 rounded-lg transition-all duration-200"
            title="Voir les avertissements"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </button>
          <button @click="$emit('open-notes-modal', member)" class="p-2 text-theme-primary hover:bg-theme-primary/10 rounded-lg transition-all duration-200" title="Note">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
          </button>
          <button
            @click="openModal(member)"
            class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200"
            title="Archiver le membre"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14" /></svg>
          </button>
          <button class="ladder-chevron" @click="toggleExpand(id)" :title="expandedRows[id] ? 'Réduire' : 'Détails'">
            {{ expandedRows[id] ? '−' : '+' }}
          </button>
        </div>
      </div>

      <!-- Détail déplié -->
      <div v-if="expandedRows[id]" class="ladder-detail">
        <button class="ladder-detail-line ladder-detail-line--clickable" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
          <span>Recruteur</span> {{ member.recruiter?.pseudo || 'personne' }}
          <span class="mx-2">·</span>
          <span>Arrivée</span> {{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : 'inconnue' }}
          <svg class="w-3.5 h-3.5 opacity-60 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
        </button>

        <div class="ladder-detail-line">
          <span>Ankama</span>
          <button @click="toggleAnkamaDisplay(member.id)" class="font-mono hover:text-theme-primary transition-colors">
            {{ ankamaDisplayed[member.id] ? member.ankamaPseudo : '••••••••' }}
          </button>
        </div>

        <div class="ladder-mules">
          <div class="flex items-center justify-between mb-2">
            <span class="ladder-detail-label">Mules</span>
            <button @click="$emit('open-add-mule-modal', member)" class="text-theme-primary hover:text-theme-primary text-xs font-medium">+ Ajouter</button>
          </div>
          <p v-if="filteredMulesByCharacter(id).length === 0" class="text-theme-text-muted text-sm">Aucune mule sur ce personnage</p>
          <div v-else class="space-y-2">
            <div
              v-for="mule in filteredMulesByCharacter(id)"
              :key="`mule-${mule.id}`"
              class="ladder-mule-row"
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
              <div class="flex-1" @click.stop>
                <EditablePseudo
                  :entity="mule"
                  :entity-type="'mule'"
                  :editing-pseudo="editingPseudo"
                  :edit-pseudo="editPseudo"
                  @start-editing="startEditingPseudo"
                  @save-pseudo="savePseudo"
                />
              </div>
              <button @click="confirmSwitchWithMule(member, mule)" class="meta-chip hover:bg-theme-primary/15 transition-colors duration-200">Switch avec ce main</button>
              <button @click="openMuleModal(mule)" class="p-1.5 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200" title="Archiver la mule">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredMembers.length === 0" class="text-center py-16 text-theme-text-muted">
      <p class="text-lg font-medium mb-1">Aucun membre trouvé</p>
      <p class="text-theme-text-muted text-sm">Essayez de modifier vos critères de recherche</p>
    </div>
  </div>

  <!-- En dehors de la carte : un backdrop-filter ancêtre transformerait ces
       modales "fixed" en modales positionnées relativement à la carte. -->
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
</template>

<script>
import { reactive } from 'vue';
import EditablePseudo from './EditablePseudo.vue';
import ClassDropdown from './ClassDropdown.vue';
import ConfirmModal from './ConfirmModal.vue';
import RankBadge from './RankBadge.vue';
import UpdateRecruitmentModal from './UpdateRecruitmentModal.vue';
import { useAuthStore } from '@/stores/authStore';
const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'MembersTableList',
  components: {
    EditablePseudo,
    ClassDropdown,
    ConfirmModal,
    RankBadge,
    UpdateRecruitmentModal,
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
    totalActiveMembers: {
      type: Number,
      required: true,
    },
  },
  setup() {
    const expandedRows = reactive({});
    return { expandedRows };
  },
  data() {
    return {
      ankamaDisplayed: {},
      showConfirmSwitch: false,
      switchMain: null,
      switchMule: null,
      showRecruitmentModal: false,
      selectedCharacter: null,
    };
  },
  computed: {
    confirmSwitchMessage() {
      if (!this.switchMain || !this.switchMule) return '';
      return `Êtes-vous sûr de vouloir échanger ${this.switchMain.pseudo} avec la mule ${this.switchMule.pseudo} ?`;
    },
  },
  methods: {
    toggleExpand(memberId) {
      this.expandedRows[memberId] = !this.expandedRows[memberId];
    },
    toggleAnkamaDisplay(characterId) {
      this.ankamaDisplayed[characterId] = !this.ankamaDisplayed[characterId];
    },
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
    openRecruitmentModal(member) {
      this.selectedCharacter = member;
      this.showRecruitmentModal = true;
    },
    closeRecruitmentModal() {
      this.showRecruitmentModal = false;
      this.selectedCharacter = null;
    },
    handleRecruitmentSaved(updatedCharacter) {
      this.$emit('update-recruitment', updatedCharacter);
      this.$emit('refresh-data');
    },
    async confirmSwitchWithMule(main, mule) {
      this.switchMain = main;
      this.switchMule = mule;
      this.showConfirmSwitch = true;
    },
    async doSwitchWithMule() {
      this.showConfirmSwitch = false;
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
        this.switchMain = null;
        this.switchMule = null;
      }
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
    'open-notes-modal',
    'refresh-data',
    'update-recruitment',
  ],
};
</script>

<style scoped>
.ladder-row {
  border-bottom: 1px solid var(--border);
}
.ladder-row:last-child {
  border-bottom: none;
}

.ladder-row-main {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.65rem 1.1rem;
  transition: background-color 0.2s;
}
.ladder-row-main:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.ladder-name {
  flex-shrink: 0;
  width: 180px;
  min-width: 0;
}

.ladder-expand-trigger {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  text-align: left;
  min-width: 0;
  padding: 0.3rem 0;
}

.ladder-meta-text {
  font-size: 0.78rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ladder-actions {
  display: flex;
  align-items: center;
  gap: 0.15rem;
  flex-shrink: 0;
}

.ladder-chevron {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  color: var(--accent);
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 0.3rem;
  transition: border-color 0.2s, background-color 0.2s;
}
.ladder-chevron:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}

.ladder-detail {
  padding: 0 1.1rem 1rem 4.3rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ladder-detail-line {
  font-size: 0.82rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
}
.ladder-detail-line span {
  color: var(--accent);
  font-weight: 600;
  margin-right: 0.35rem;
}

.ladder-detail-line--clickable {
  width: fit-content;
  border-radius: 0.6rem;
  padding: 0.3rem 0.5rem;
  margin: -0.3rem -0.5rem;
  transition: background-color 0.2s;
}
.ladder-detail-line--clickable:hover {
  background-color: rgba(var(--primary-rgb), 0.06);
}

.ladder-mules {
  margin-top: 0.4rem;
  padding-top: 0.6rem;
  border-top: 1px solid var(--border);
}

.ladder-detail-label {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.ladder-mule-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 0.4rem 0.6rem;
}
</style>
