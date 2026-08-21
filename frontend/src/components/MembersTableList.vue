<template>
  <div class="glass-card rounded-2xl overflow-hidden">
    <div class="ladder-header">
      <span></span>
      <span>Personnage</span>
      <span>Rang</span>
      <span>Recruteur</span>
      <span>Arrivée</span>
      <span>Mules</span>
      <span>Avert.</span>
      <span></span>
    </div>

    <div v-for="({ member, id }) in filteredMembers" :key="`member-${member.id}`" class="ladder-row">
      <div class="ladder-row-main">
        <span @click.stop class="ladder-avatar">
          <ClassDropdown
            :class-name="member.class"
            :classes="classes"
            :entity-id="member.id"
            :entity-type="'character'"
            size="sm"
            @update-class="updateCharacterClass"
          />
          <span
            v-if="ghostTotalVotes[member.id]"
            class="ladder-ghost-total-pastille"
            :title="`Signalé ${ghostTotalVotes[member.id]} fois comme fantôme au total`"
          >
            👻{{ ghostTotalVotes[member.id] }}
          </span>
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

        <div class="ladder-col ladder-col--rank">
          <RankBadge :rank="member.rank" size="sm" />
        </div>

        <button class="ladder-col ladder-col--btn ladder-col--recruiter" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
          <span class="ladder-col-label">Recruteur</span>
          <span class="ladder-col-value">{{ member.recruiter?.pseudo || '—' }}</span>
        </button>

        <button class="ladder-col ladder-col--btn ladder-col--arrival" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
          <span class="ladder-col-label">Arrivée</span>
          <span class="ladder-col-value">{{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : '—' }}</span>
        </button>

        <button
          class="ladder-col ladder-col--btn ladder-col--mules"
          :class="{ 'ladder-col--active': expandedRows[id] }"
          @click="toggleExpand(id)"
          :title="expandedRows[id] ? 'Réduire' : 'Voir les mules'"
        >
          <span class="ladder-col-label">Mules</span>
          <span class="ladder-col-value">{{ filteredMulesByCharacter(id).length }}</span>
        </button>

        <button
          class="ladder-col ladder-col--btn ladder-col--warnings"
          :class="{ 'ladder-col--warning': characterWarningCounts[member.id] > 0 }"
          @click="viewWarnings(member.id, member.pseudo)"
          title="Voir les avertissements"
        >
          <span class="ladder-col-label">Avert.</span>
          <span class="ladder-col-value">{{ characterWarningCounts[member.id] || 0 }}</span>
        </button>

        <div class="ladder-actions">
          <button @click="$emit('open-notes-modal', member)" class="p-2 text-theme-primary hover:bg-theme-primary/10 rounded-lg transition-all duration-200" title="Note">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
          </button>
          <button
            @click="toggleGhostVote(member)"
            class="relative p-2 rounded-lg transition-all duration-200"
            :class="ghostVotedCharacterIds.has(member.id) ? 'text-theme-primary bg-theme-primary/12' : 'text-theme-text-muted hover:text-theme-primary hover:bg-theme-primary/10'"
            :title="(ghostVotedCharacterIds.has(member.id) ? 'Retirer mon vote fantôme' : 'Signaler comme fantôme') + (ghostVoteCounts[member.id] ? ` (${ghostVoteCounts[member.id]} vote${ghostVoteCounts[member.id] !== 1 ? 's' : ''} ce mois-ci)` : '')"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2a7 7 0 0 0-7 7v11.5a.5.5 0 0 0 .82.38L7.5 19l1.7 1.62a.75.75 0 0 0 1 0L12 19l1.8 1.62a.75.75 0 0 0 1 0L16.5 19l1.68 1.88A.5.5 0 0 0 19 20.5V9a7 7 0 0 0-7-7z" />
              <circle cx="9.5" cy="10" r="1.25" />
              <circle cx="14.5" cy="10" r="1.25" />
            </svg>
            <span v-if="ghostVoteCounts[member.id]" class="ladder-ghost-count">{{ ghostVoteCounts[member.id] }}</span>
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
        <button class="ladder-detail-line ladder-detail-line--recruitment ladder-detail-line--clickable" @click="openRecruitmentModal(member)" title="Modifier le recrutement">
          <span>Recruteur</span> {{ member.recruiter?.pseudo || 'personne' }}
          <span class="mx-2">·</span>
          <span>Arrivée</span> {{ member.createdAt ? new Date(member.createdAt).toLocaleDateString('fr-FR') : 'inconnue' }}
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
    toggleGhostVote(member) {
      this.$emit('toggle-ghost-vote', member.id);
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
    'toggle-ghost-vote',
  ],
};
</script>

<style scoped>
.ladder-header {
  display: grid;
  grid-template-columns: 3rem minmax(150px, 1.4fr) 8.5rem 8.5rem 6.5rem 4.5rem 4.5rem 7rem;
  align-items: center;
  column-gap: 1rem;
  padding: 0.7rem 1.25rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.64rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
}
.ladder-header span:nth-child(3),
.ladder-header span:nth-child(4),
.ladder-header span:nth-child(5) {
  text-align: center;
}
.ladder-header span:nth-child(6),
.ladder-header span:nth-child(7) {
  text-align: center;
}

.ladder-row {
  border-bottom: 1px solid var(--border);
}
.ladder-row:last-child {
  border-bottom: none;
}

.ladder-row-main {
  display: grid;
  grid-template-columns: 3rem minmax(150px, 1.4fr) 8.5rem 8.5rem 6.5rem 4.5rem 4.5rem 7rem;
  align-items: center;
  column-gap: 1rem;
  padding: 0.85rem 1.25rem;
  min-height: 4.25rem;
  transition: background-color 0.2s;
}
.ladder-row-main:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.ladder-avatar {
  position: relative;
  display: flex;
}

.ladder-ghost-total-pastille {
  position: absolute;
  top: -0.35rem;
  right: -0.35rem;
  background-color: var(--card);
  border: 1px solid var(--primary);
  color: var(--primary);
  border-radius: 9999px;
  padding: 0 0.3rem;
  font-size: 0.6rem;
  font-weight: 700;
  white-space: nowrap;
  line-height: 1.4;
}

.ladder-name {
  min-width: 0;
  font-size: 0.95rem;
}

.ladder-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.15rem;
  min-width: 0;
}

.ladder-col--rank {
  flex-direction: row;
}

.ladder-col-label {
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.ladder-col-value {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.ladder-col--btn {
  border-radius: 0.65rem;
  padding: 0.3rem 0.4rem;
  margin: -0.3rem 0;
  transition: background-color 0.2s;
}
.ladder-col--btn:hover {
  background-color: rgba(var(--primary-rgb), 0.07);
}

.ladder-col--active {
  background-color: rgba(var(--primary-rgb), 0.1);
}
.ladder-col--active .ladder-col-value {
  color: var(--primary);
}

.ladder-col--warning .ladder-col-value {
  color: var(--warning);
}

.ladder-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.15rem;
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
  padding: 0 1.25rem 1.1rem calc(3rem + 1rem);
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

.ladder-detail-line--recruitment {
  display: none;
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

.ladder-ghost-count {
  position: absolute;
  top: -0.2rem;
  right: -0.2rem;
  min-width: 1.1rem;
  height: 1.1rem;
  padding: 0 0.25rem;
  border-radius: 9999px;
  background-color: var(--primary);
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
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

@media (max-width: 880px) {
  .ladder-header {
    display: none;
  }
  .ladder-row-main {
    grid-template-columns: 3rem minmax(120px, 1.4fr) 7.5rem 4.5rem 4.5rem 7rem;
  }
  .ladder-col--recruiter,
  .ladder-col--arrival {
    display: none;
  }
  .ladder-detail-line--recruitment {
    display: flex;
  }
}
</style>
