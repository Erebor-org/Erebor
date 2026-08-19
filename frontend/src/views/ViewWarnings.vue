<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">{{ characterPseudo }}</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Historique des avertissements de ce personnage</p>
      </div>

      <div class="mb-6 flex justify-center">
        <button
          @click="goBack"
          class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium text-sm rounded-xl transition-all duration-200 flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          <span>Retour aux membres</span>
        </button>
      </div>

      <!-- Toolbar -->
      <div class="glass-card rounded-2xl p-5 mb-6 flex items-center justify-between gap-4">
        <span class="text-sm text-theme-text-muted">{{ warnings.length }} avertissement{{ warnings.length === 1 ? '' : 's' }}</span>
        <button
          v-if="canManageWarnings"
          @click="openAddWarningModal"
          class="px-4 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
          <span>Ajouter</span>
        </button>
      </div>

      <!-- Warnings -->
      <div class="glass-card rounded-2xl overflow-hidden">
        <div v-for="warning in warnings" :key="warning.id" class="wr-row">
          <div class="wr-row-main">
            <span class="wr-date-badge">{{ formatDate(warning.createdAt) }}</span>

            <button class="wr-desc" @click="toggleExpand(warning.id)" title="Voir le détail">
              {{ warning.description }}
            </button>

            <div class="wr-author" :title="warning.authorCharacter ? warning.authorCharacter.pseudo : 'Auteur inconnu'">
              <img v-if="warning.authorCharacter" :src="getClassIcon(warning.authorCharacter.class)" class="wr-author-icon" :alt="`Classe ${warning.authorCharacter.class}`" />
              <span>{{ warning.authorCharacter ? warning.authorCharacter.pseudo : 'Inconnu' }}</span>
            </div>

            <div v-if="canManageWarnings" class="wr-actions">
              <button @click="openEditWarningModal(warning)" class="p-2 text-theme-primary hover:bg-theme-primary/10 rounded-lg transition-all duration-200" title="Modifier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
              </button>
              <button @click="openDeleteWarningModal(warning)" class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200" title="Supprimer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
              <button class="wr-chevron" @click="toggleExpand(warning.id)" :title="expandedRows[warning.id] ? 'Réduire' : 'Détails'">
                {{ expandedRows[warning.id] ? '−' : '+' }}
              </button>
            </div>
            <button v-else class="wr-chevron wr-chevron--solo" @click="toggleExpand(warning.id)" :title="expandedRows[warning.id] ? 'Réduire' : 'Détails'">
              {{ expandedRows[warning.id] ? '−' : '+' }}
            </button>
          </div>

          <!-- Détail déplié -->
          <div v-if="expandedRows[warning.id]" class="wr-detail">
            <p class="wr-desc-full">{{ warning.description }}</p>
            <p v-if="!canManageWarnings" class="wr-readonly">Lecture seule</p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="warnings.length === 0" class="text-center py-16 text-theme-text-muted">
          <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <p class="text-lg font-medium mb-1">Aucun avertissement</p>
          <p class="text-sm">Ce personnage n'a pas encore reçu d'avertissement</p>
        </div>
      </div>
    </div>
  </div>

  <!-- En dehors du .glass-card : un backdrop-filter ancêtre transformerait ces
       modales "fixed" en modales positionnées relativement à la carte. -->
  <div
    v-if="showAddWarningModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="closeAddWarningModal"
  >
    <div class="glass-modal rounded-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
      <h3 class="text-xl font-serif font-bold text-theme-primary mb-1">Ajouter un avertissement</h3>
      <p class="text-sm text-theme-text-muted mb-5">Pour <span class="font-semibold text-theme-text">{{ characterPseudo }}</span></p>

      <div class="space-y-4">
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-theme-text">Description <span class="text-theme-error">*</span></label>
          <textarea
            v-model="newWarning.description"
            rows="4"
            maxlength="1000"
            placeholder="Décrivez la raison de l'avertissement"
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 resize-none"
          ></textarea>
          <div class="text-right text-xs text-theme-text-muted">{{ newWarning.description.length }}/1000</div>
        </div>

        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-theme-text">Personnage auteur <span class="text-theme-error">*</span></label>
          <div v-if="newWarning.authorCharacterId" class="wr-picked">
            <img :src="selectedAuthorIcon" :alt="`Classe ${selectedAuthorName}`" class="wr-picked-icon" />
            <span class="flex-1 font-semibold text-theme-text">{{ selectedAuthorName }}</span>
            <button type="button" @click="clearSelectedAuthor" class="wr-picked-clear" title="Changer d'auteur">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <div v-else>
            <input
              type="text"
              v-model="authorSearchQuery"
              placeholder="Rechercher un auteur..."
              class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 mb-2"
            />
            <div class="wr-picklist">
              <button
                type="button"
                v-for="char in filteredLeadCharacters"
                :key="char.id"
                @click="selectAuthor(char)"
                class="wr-pick-option"
              >
                <img :src="getClassIcon(char.class)" :alt="`Classe ${char.class}`" class="wr-pick-icon" />
                <span>{{ char.pseudo }} <span class="text-theme-text-muted">— {{ char.rank?.name }}</span></span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="closeAddWarningModal" class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200">
          Annuler
        </button>
        <button
          @click="addWarning"
          :disabled="isSubmitting"
          class="px-5 py-2 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? 'Enregistrement...' : 'Ajouter' }}
        </button>
      </div>
    </div>
  </div>

  <div
    v-if="showEditWarningModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="closeEditWarningModal"
  >
    <div class="glass-modal rounded-2xl max-w-lg w-full p-6">
      <h3 class="text-xl font-serif font-bold text-theme-primary mb-5">Modifier l'avertissement</h3>

      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-theme-text">Description <span class="text-theme-error">*</span></label>
        <textarea
          v-model="editWarning.description"
          rows="4"
          maxlength="1000"
          placeholder="Décrivez la raison de l'avertissement"
          class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 resize-none"
        ></textarea>
        <div class="text-right text-xs text-theme-text-muted">{{ editWarning.description.length }}/1000</div>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="closeEditWarningModal" class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200">
          Annuler
        </button>
        <button
          @click="updateWarning"
          :disabled="isSubmitting"
          class="px-5 py-2 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </div>
  </div>

  <ConfirmModal
    :show="showDeleteWarningModal"
    title="Confirmer la suppression"
    message="Êtes-vous sûr de vouloir supprimer cet avertissement ?"
    confirmText="Supprimer"
    @confirm="deleteWarning"
    @cancel="closeDeleteWarningModal"
  />
</template>

<script>
import axios from 'axios';
import Notification from '@/components/NotificationCenter.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { getClassIcon } from '@/config/classIcons';
import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    Notification,
    ConfirmModal,
  },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      characterId: null,
      characterPseudo: '',
      warnings: [],
      leadCharacters: [],
      expandedRows: {},

      showAddWarningModal: false,
      showEditWarningModal: false,
      showDeleteWarningModal: false,
      newWarning: { description: '', authorCharacterId: '' },
      editWarning: { id: null, description: '' },
      selectedWarning: null,
      isSubmitting: false,
      authorSearchQuery: '',
      selectedAuthorName: '',
      selectedAuthorIcon: '',
    };
  },
  created() {
    this.characterId = this.$route.params.id;
    this.characterPseudo = this.$route.params.pseudo;
    if (this.characterId) {
      this.fetchWarnings();
      this.fetchLeadCharacters();
    }
  },
  computed: {
    filteredLeadCharacters() {
      if (!this.authorSearchQuery) return this.leadCharacters;
      const query = this.authorSearchQuery.toLowerCase();
      return this.leadCharacters.filter(character => character.pseudo.toLowerCase().includes(query));
    },
    canManageWarnings() {
      const roles = this.authStore.user?.roles || [];
      return roles.includes('ROLE_OWNERS');
    },
  },
  methods: {
    getClassIcon,
    toggleExpand(id) {
      this.expandedRows[id] = !this.expandedRows[id];
    },
    selectAuthor(character) {
      this.newWarning.authorCharacterId = character.id;
      this.selectedAuthorName = character.pseudo;
      this.selectedAuthorIcon = getClassIcon(character.class);
      this.authorSearchQuery = '';
    },
    clearSelectedAuthor() {
      this.newWarning.authorCharacterId = '';
      this.selectedAuthorName = '';
      this.selectedAuthorIcon = '';
    },
    async fetchWarnings() {
      try {
        const response = await axios.get(`${API_URL}/warnings/character/${this.characterId}`);
        this.warnings = response.data;
      } catch (error) {
        console.error('Error fetching warnings:', error);
        this.$refs.notificationRef.showNotification('Erreur lors de la récupération des avertissements', 'error');
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }).format(date);
    },
    goBack() {
      this.$router.push('/membres');
    },
    async fetchLeadCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters/lead`);
        this.leadCharacters = response.data;
      } catch (error) {
        console.error('Error fetching lead characters:', error);
        this.$refs.notificationRef.showNotification('Erreur lors de la récupération des personnages principaux', 'error');
      }
    },
    openAddWarningModal() {
      this.newWarning = { description: '', authorCharacterId: '' };
      this.clearSelectedAuthor();
      this.showAddWarningModal = true;
    },
    closeAddWarningModal() {
      this.showAddWarningModal = false;
    },
    openEditWarningModal(warning) {
      this.editWarning = { id: warning.id, description: warning.description };
      this.showEditWarningModal = true;
    },
    closeEditWarningModal() {
      this.showEditWarningModal = false;
    },
    openDeleteWarningModal(warning) {
      this.selectedWarning = warning;
      this.showDeleteWarningModal = true;
    },
    closeDeleteWarningModal() {
      this.showDeleteWarningModal = false;
      this.selectedWarning = null;
    },
    async addWarning() {
      if (!this.newWarning.description.trim() || !this.newWarning.authorCharacterId) {
        this.$refs.notificationRef.showNotification("La description et l'auteur sont obligatoires", 'error');
        return;
      }
      this.isSubmitting = true;
      try {
        const response = await axios.post(`${API_URL}/warnings`, {
          characterId: this.characterId,
          description: this.newWarning.description,
          authorCharacterId: this.newWarning.authorCharacterId,
        });
        this.warnings.unshift(response.data);
        this.closeAddWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement ajouté avec succès');
      } catch (error) {
        console.error('Error adding warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || "Erreur lors de l'ajout de l'avertissement",
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    },
    async updateWarning() {
      if (!this.editWarning.description.trim()) {
        this.$refs.notificationRef.showNotification('La description est obligatoire', 'error');
        return;
      }
      this.isSubmitting = true;
      try {
        const response = await axios.put(`${API_URL}/warnings/${this.editWarning.id}`, {
          description: this.editWarning.description,
        });
        const index = this.warnings.findIndex(w => w.id === this.editWarning.id);
        if (index !== -1) this.warnings[index] = response.data;
        this.closeEditWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement mis à jour avec succès');
      } catch (error) {
        console.error('Error updating warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || "Erreur lors de la mise à jour de l'avertissement",
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    },
    async deleteWarning() {
      if (!this.selectedWarning) return;
      try {
        await axios.delete(`${API_URL}/warnings/${this.selectedWarning.id}`);
        this.warnings = this.warnings.filter(w => w.id !== this.selectedWarning.id);
        this.$refs.notificationRef.showNotification('Avertissement supprimé avec succès');
      } catch (error) {
        console.error('Error deleting warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || "Erreur lors de la suppression de l'avertissement",
          'error'
        );
      } finally {
        this.closeDeleteWarningModal();
      }
    },
  },
};
</script>

<style scoped>
.wr-row {
  border-bottom: 1px solid var(--border);
}
.wr-row:last-child {
  border-bottom: none;
}

.wr-row-main {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1.25rem;
  transition: background-color 0.2s;
}
.wr-row-main:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.wr-date-badge {
  flex-shrink: 0;
  width: 150px;
  font-size: 0.8rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.wr-desc {
  flex: 1;
  min-width: 0;
  text-align: left;
  font-size: 0.85rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding: 0.3rem 0.5rem;
  margin: -0.3rem -0.5rem;
  border-radius: 0.6rem;
  transition: background-color 0.2s;
}
.wr-desc:hover {
  background-color: rgba(var(--primary-rgb), 0.06);
  color: var(--text);
}

.wr-author {
  flex-shrink: 0;
  width: 150px;
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.wr-author-icon {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

.wr-actions {
  display: flex;
  align-items: center;
  gap: 0.15rem;
  flex-shrink: 0;
}

.wr-chevron {
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
.wr-chevron:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}
.wr-chevron--solo {
  margin-left: auto;
  flex-shrink: 0;
}

.wr-detail {
  padding: 0 1.25rem 1.1rem 1.25rem;
}
.wr-desc-full {
  font-size: 0.85rem;
  color: var(--text);
  line-height: 1.5;
}
.wr-readonly {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  font-style: italic;
}

/* ---------- Sélecteur auteur (modale d'ajout) ---------- */
.wr-picked {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0.75rem;
  border-radius: 0.65rem;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
}
.wr-picked-icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border);
  object-fit: cover;
}
.wr-picked-clear {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: background-color 0.2s, color 0.2s;
}
.wr-picked-clear:hover {
  background-color: rgba(var(--error-rgb, 220, 38, 38), 0.15);
  color: var(--error);
}

.wr-picklist {
  max-height: 12rem;
  overflow-y: auto;
  border: 1px solid var(--border);
  border-radius: 0.65rem;
  padding: 0.35rem;
}
.wr-pick-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem 0.6rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  color: var(--text);
  text-align: left;
  transition: background-color 0.2s;
}
.wr-pick-option:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.wr-pick-icon {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.4rem;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

@media (max-width: 620px) {
  .wr-author {
    display: none;
  }
}
</style>
