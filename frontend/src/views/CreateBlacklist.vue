<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Blacklist de la Guilde</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Personnages bannis et leurs personnages associés connus</p>
      </div>

      <!-- Toolbar -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:justify-between">
          <div class="relative flex-1 md:max-w-md">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Rechercher un pseudo, un Ankama ID..."
              class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
            />
          </div>
          <div class="flex items-center gap-3 justify-between md:justify-end">
            <span class="text-sm text-theme-text-muted whitespace-nowrap">{{ filteredBlacklist.length }} entrée{{ filteredBlacklist.length === 1 ? '' : 's' }}</span>
            <button
              @click="openCreateModal"
              class="px-4 py-2.5 bg-theme-error hover:bg-theme-error/85 text-white font-semibold text-sm rounded-xl transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
              <span>Ajouter</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Blacklist -->
      <div class="glass-card rounded-2xl overflow-hidden">
        <div v-for="entry in filteredBlacklist" :key="entry.id" class="bl-row">
          <div class="bl-row-main">
            <div class="bl-badge">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM5.636 5.636l12.728 12.728" /></svg>
            </div>

            <div class="bl-identity">
              <span class="bl-pseudo">{{ entry.pseudo }}</span>
              <span class="bl-ankama">{{ entry.ankamaPseudo }}</span>
            </div>

            <button class="bl-reason" @click="toggleExpand(entry.id)" title="Voir le détail">
              {{ entry.reason }}
            </button>

            <button
              class="bl-assoc-chip"
              :class="{ 'bl-assoc-chip--active': expandedRows[entry.id] }"
              @click="toggleExpand(entry.id)"
              title="Personnages associés"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
              {{ entry.associatedCharacters.length }}
            </button>

            <div class="bl-actions">
              <button @click="openEditModal(entry)" class="p-2 text-theme-primary hover:bg-theme-primary/10 rounded-lg transition-all duration-200" title="Modifier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
              </button>
              <button @click="openConfirmModal(entry)" class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200" title="Supprimer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
              <button class="bl-chevron" @click="toggleExpand(entry.id)" :title="expandedRows[entry.id] ? 'Réduire' : 'Détails'">
                {{ expandedRows[entry.id] ? '−' : '+' }}
              </button>
            </div>
          </div>

          <!-- Détail déplié -->
          <div v-if="expandedRows[entry.id]" class="bl-detail">
            <p class="bl-reason-full">{{ entry.reason }}</p>

            <div class="bl-associated">
              <div class="flex items-center justify-between mb-2">
                <span class="bl-detail-label">Personnages associés</span>
                <button
                  v-if="addingAssociatedFor !== entry.id"
                  @click="startAddAssociated(entry.id)"
                  class="text-theme-primary hover:text-theme-primary text-xs font-medium"
                >
                  + Ajouter
                </button>
              </div>

              <p v-if="entry.associatedCharacters.length === 0 && addingAssociatedFor !== entry.id" class="text-theme-text-muted text-sm">
                Aucun personnage associé connu
              </p>

              <div v-if="entry.associatedCharacters.length" class="flex flex-wrap gap-2 mb-2">
                <span v-for="ac in entry.associatedCharacters" :key="ac.id" class="bl-assoc-item">
                  {{ ac.pseudo }}
                  <span v-if="ac.ankamaPseudo" class="bl-assoc-item-ankama">({{ ac.ankamaPseudo }})</span>
                  <button @click="removeAssociated(entry, ac)" class="bl-assoc-item-remove" title="Retirer">×</button>
                </span>
              </div>

              <div v-if="addingAssociatedFor === entry.id" class="bl-add-row">
                <input v-model="newAssociated.pseudo" placeholder="Pseudo" class="bl-add-input" @keydown.enter="confirmAddAssociated(entry)" />
                <input v-model="newAssociated.ankamaPseudo" placeholder="Pseudo Ankama" class="bl-add-input" @keydown.enter="confirmAddAssociated(entry)" />
                <button @click="confirmAddAssociated(entry)" class="bl-add-confirm">Ajouter</button>
                <button @click="cancelAddAssociated" class="bl-add-cancel">Annuler</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredBlacklist.length === 0" class="text-center py-16 text-theme-text-muted">
          <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <p class="text-lg font-medium mb-1">{{ blacklist.length === 0 ? 'Aucun personnage en blacklist' : 'Aucun résultat' }}</p>
          <p class="text-sm">{{ blacklist.length === 0 ? 'La liste est vide pour le moment' : 'Essayez un autre pseudo' }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- En dehors du .glass-card : un backdrop-filter ancêtre transformerait ces
       modales "fixed" en modales positionnées relativement à la carte. -->
  <div
    v-if="showFormModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="closeFormModal"
  >
    <div class="glass-modal rounded-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
      <h3 class="text-xl font-serif font-bold text-theme-primary mb-1">
        {{ editingEntry ? 'Modifier une entrée' : 'Nouvelle entrée blacklist' }}
      </h3>
      <p class="text-sm text-theme-text-muted mb-5">
        {{ editingEntry ? 'Met à jour le pseudo, l\'Ankama ID ou la raison.' : 'Ajoute un personnage banni et, si connus, les autres personnages sur lesquels il peut se connecter.' }}
      </p>

      <div class="space-y-4">
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-theme-text">Pseudo <span class="text-theme-error">*</span></label>
          <input
            v-model="form.pseudo"
            type="text"
            placeholder="Pseudo du personnage"
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-theme-text">Pseudo Ankama <span class="text-theme-error">*</span></label>
          <input
            v-model="form.ankamaPseudo"
            type="text"
            placeholder="Pseudo Ankama"
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-theme-text">Raison <span class="text-theme-error">*</span></label>
          <textarea
            v-model="form.reason"
            rows="3"
            placeholder="Décrivez la raison du bannissement"
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 resize-none"
          ></textarea>
        </div>

        <div v-if="!editingEntry" class="space-y-1.5 pt-2 border-t border-theme-border">
          <label class="block text-sm font-medium text-theme-text pt-3">Personnages associés (optionnel)</label>
          <p class="text-xs text-theme-text-muted mb-2">Autres pseudos (mules, alts) sur lesquels cette personne peut se connecter.</p>

          <div v-for="(ac, i) in formAssociated" :key="i" class="flex gap-2 mb-2">
            <input v-model="ac.pseudo" placeholder="Pseudo" class="bl-add-input flex-1" />
            <input v-model="ac.ankamaPseudo" placeholder="Pseudo Ankama" class="bl-add-input flex-1" />
            <button @click="formAssociated.splice(i, 1)" class="bl-assoc-item-remove" title="Retirer">×</button>
          </div>

          <button
            @click="formAssociated.push({ pseudo: '', ankamaPseudo: '' })"
            class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-dashed border-theme-border rounded-lg text-theme-text-muted hover:text-theme-primary hover:border-theme-primary text-sm font-medium transition-all duration-200"
          >
            + Ajouter un personnage associé
          </button>
        </div>
      </div>

      <div v-if="formError" class="mt-4 p-3 bg-theme-error/10 border border-theme-error rounded-lg">
        <p class="text-sm text-theme-error">{{ formError }}</p>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="closeFormModal" class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200">
          Annuler
        </button>
        <button
          @click="submitForm"
          :disabled="loading"
          class="px-5 py-2 bg-theme-error hover:bg-theme-error/85 text-white font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Enregistrement...' : (editingEntry ? 'Enregistrer' : 'Ajouter') }}
        </button>
      </div>
    </div>
  </div>

  <ConfirmModal
    :show="showConfirmModal"
    title="Confirmer la suppression"
    :message="entryToDelete ? `Êtes-vous sûr de vouloir supprimer ${entryToDelete.pseudo} de la blacklist ? Ses ${entryToDelete.associatedCharacters.length} personnage(s) associé(s) seront aussi retirés.` : ''"
    confirmText="Supprimer"
    @confirm="confirmDelete"
    @cancel="showConfirmModal = false"
  />
</template>

<script>
import Notification from '@/components/NotificationCenter.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    Notification,
    ConfirmModal,
  },
  data() {
    return {
      blacklist: [],
      searchQuery: '',
      expandedRows: {},

      showFormModal: false,
      editingEntry: null,
      form: { pseudo: '', ankamaPseudo: '', reason: '' },
      formAssociated: [],
      formError: '',
      loading: false,

      addingAssociatedFor: null,
      newAssociated: { pseudo: '', ankamaPseudo: '' },

      showConfirmModal: false,
      entryToDelete: null,
    };
  },
  computed: {
    filteredBlacklist() {
      const query = this.searchQuery.trim().toLowerCase();
      if (!query) return this.blacklist;
      return this.blacklist.filter(entry => {
        const matchesMain =
          (entry.pseudo && entry.pseudo.toLowerCase().includes(query)) ||
          (entry.ankamaPseudo && entry.ankamaPseudo.toLowerCase().includes(query));
        const matchesAssociated = entry.associatedCharacters.some(ac =>
          (ac.pseudo && ac.pseudo.toLowerCase().includes(query)) ||
          (ac.ankamaPseudo && ac.ankamaPseudo.toLowerCase().includes(query))
        );
        return matchesMain || matchesAssociated;
      });
    },
  },
  methods: {
    toggleExpand(id) {
      this.expandedRows[id] = !this.expandedRows[id];
    },

    openCreateModal() {
      this.editingEntry = null;
      this.form = { pseudo: '', ankamaPseudo: '', reason: '' };
      this.formAssociated = [];
      this.formError = '';
      this.showFormModal = true;
    },
    openEditModal(entry) {
      this.editingEntry = entry;
      this.form = { pseudo: entry.pseudo, ankamaPseudo: entry.ankamaPseudo, reason: entry.reason };
      this.formAssociated = [];
      this.formError = '';
      this.showFormModal = true;
    },
    closeFormModal() {
      this.showFormModal = false;
    },
    async submitForm() {
      const { pseudo, ankamaPseudo, reason } = this.form;
      if (!pseudo?.trim() || !ankamaPseudo?.trim() || !reason?.trim()) {
        this.formError = 'Pseudo, Ankama ID et raison sont requis.';
        return;
      }

      this.loading = true;
      this.formError = '';
      try {
        if (this.editingEntry) {
          const response = await axios.put(`${API_URL}/blacklist/${this.editingEntry.id}`, { pseudo, ankamaPseudo, reason });
          const index = this.blacklist.findIndex(e => e.id === this.editingEntry.id);
          if (index !== -1) this.blacklist[index] = response.data;
          this.$refs.notificationRef.showNotification(`${pseudo} a été mis à jour`);
        } else {
          const associatedCharacters = this.formAssociated
            .filter(ac => ac.pseudo?.trim())
            .map(ac => ({ pseudo: ac.pseudo.trim(), ankamaPseudo: ac.ankamaPseudo?.trim() || null }));

          const response = await axios.post(`${API_URL}/blacklist`, { pseudo, ankamaPseudo, reason, associatedCharacters });
          this.blacklist.push(response.data);
          this.$refs.notificationRef.showNotification(`${pseudo} a bien été ajouté à la blacklist`);
        }
        this.closeFormModal();
      } catch (error) {
        console.error('Error saving blacklist entry:', error.response?.data || error.message);
        this.formError = error.response?.data?.message || "Erreur lors de l'enregistrement.";
      } finally {
        this.loading = false;
      }
    },

    startAddAssociated(entryId) {
      this.addingAssociatedFor = entryId;
      this.newAssociated = { pseudo: '', ankamaPseudo: '' };
    },
    cancelAddAssociated() {
      this.addingAssociatedFor = null;
    },
    async confirmAddAssociated(entry) {
      const pseudo = this.newAssociated.pseudo?.trim();
      if (!pseudo) return;

      try {
        const response = await axios.post(`${API_URL}/blacklist/${entry.id}/characters`, {
          pseudo,
          ankamaPseudo: this.newAssociated.ankamaPseudo?.trim() || null,
        });
        const index = this.blacklist.findIndex(e => e.id === entry.id);
        if (index !== -1) this.blacklist[index] = response.data;
        this.addingAssociatedFor = null;
      } catch (error) {
        console.error('Error adding associated character:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification(error.response?.data?.message || "Erreur lors de l'ajout du personnage associé.");
      }
    },
    async removeAssociated(entry, associatedCharacter) {
      try {
        const response = await axios.delete(`${API_URL}/blacklist/${entry.id}/characters/${associatedCharacter.id}`);
        const index = this.blacklist.findIndex(e => e.id === entry.id);
        if (index !== -1) this.blacklist[index] = response.data;
      } catch (error) {
        console.error('Error removing associated character:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification("Erreur lors de la suppression du personnage associé.");
      }
    },

    openConfirmModal(entry) {
      this.entryToDelete = entry;
      this.showConfirmModal = true;
    },
    async confirmDelete() {
      if (!this.entryToDelete) return;
      const entry = this.entryToDelete;
      try {
        await axios.delete(`${API_URL}/blacklist/${entry.id}`);
        this.blacklist = this.blacklist.filter(e => e.id !== entry.id);
        this.$refs.notificationRef.showNotification(`${entry.pseudo} a bien été supprimé de la blacklist`);
      } catch (error) {
        console.error('Error removing blacklist entry:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification(
          `Erreur lors de la suppression de "${entry.pseudo}" de la blacklist: ${error.response?.data?.message || error.message}`
        );
      } finally {
        this.showConfirmModal = false;
        this.entryToDelete = null;
      }
    },

    async fetchBlacklist() {
      try {
        const response = await axios.get(`${API_URL}/blacklist`);
        this.blacklist = response.data;
      } catch (error) {
        console.error('Error fetching blacklist:', error);
      }
    },
  },
  async mounted() {
    await this.fetchBlacklist();
  },
};
</script>

<style scoped>
.bl-row {
  border-bottom: 1px solid var(--border);
}
.bl-row:last-child {
  border-bottom: none;
}

.bl-row-main {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1.25rem;
  transition: background-color 0.2s;
}
.bl-row-main:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.bl-badge {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--error);
  background-color: rgba(220, 38, 38, 0.12);
}

.bl-identity {
  flex-shrink: 0;
  width: 190px;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}
.bl-pseudo {
  font-weight: 700;
  color: var(--error);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.bl-ankama {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.bl-reason {
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
.bl-reason:hover {
  background-color: rgba(var(--primary-rgb), 0.06);
  color: var(--text);
}

.bl-assoc-chip {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text-muted);
  transition: border-color 0.2s, background-color 0.2s, color 0.2s;
}
.bl-assoc-chip:hover {
  border-color: var(--accent);
  color: var(--accent-hover);
}
.bl-assoc-chip--active {
  border-color: rgba(var(--accent-rgb), 0.35);
  color: var(--accent-hover);
  background-color: rgba(var(--accent-rgb), 0.08);
}

.bl-actions {
  display: flex;
  align-items: center;
  gap: 0.15rem;
  flex-shrink: 0;
}

.bl-chevron {
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
.bl-chevron:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}

.bl-detail {
  padding: 0 1.25rem 1.1rem calc(2.5rem + 0.85rem);
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.bl-reason-full {
  font-size: 0.85rem;
  color: var(--text);
  line-height: 1.5;
}

.bl-associated {
  padding-top: 0.65rem;
  border-top: 1px solid var(--border);
}

.bl-detail-label {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}

.bl-assoc-item {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.4rem 0.25rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.78rem;
  font-weight: 600;
  border: 1px solid var(--border);
  background-color: rgba(255, 255, 255, 0.02);
  color: var(--text);
}
.bl-assoc-item-ankama {
  font-weight: 400;
  color: var(--text-muted);
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.72rem;
}
.bl-assoc-item-remove {
  width: 1.1rem;
  height: 1.1rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  line-height: 1;
  transition: background-color 0.2s, color 0.2s;
}
.bl-assoc-item-remove:hover {
  background-color: rgba(var(--error-rgb, 220, 38, 38), 0.15);
  color: var(--error);
}

.bl-add-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}
.bl-add-input {
  min-width: 140px;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text);
  border-radius: 0.5rem;
  padding: 0.4rem 0.6rem;
  font-size: 0.8rem;
}
.bl-add-input:focus {
  outline: none;
  border-color: var(--primary);
}
.bl-add-confirm {
  padding: 0.4rem 0.75rem;
  border-radius: 0.5rem;
  background-color: var(--primary);
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
}
.bl-add-confirm:hover {
  background-color: var(--primary-hover);
}
.bl-add-cancel {
  padding: 0.4rem 0.6rem;
  border-radius: 0.5rem;
  color: var(--text-muted);
  font-size: 0.8rem;
}
.bl-add-cancel:hover {
  color: var(--text);
}

@media (max-width: 720px) {
  .bl-identity {
    width: 130px;
  }
  .bl-reason {
    display: none;
  }
}
</style>
