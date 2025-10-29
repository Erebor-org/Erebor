<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-theme-primary mb-6">Avertissements de {{ characterPseudo }}</h1>
        <div class="w-32 h-1 bg-theme-primary mx-auto rounded-full shadow-lg shadow-theme-primary/50"></div>
        <p class="text-theme-text-muted mt-6 text-lg">Gérez les avertissements de ce personnage</p>
      </div>

      <!-- Back Button -->
      <div class="mb-8 flex justify-center">
        <button
          @click="goBack"
          class="px-6 py-3 bg-theme-bg-muted hover:bg-theme-text-muted text-theme-text font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-text-muted/30 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>Retour aux personnages</span>
        </button>
      </div>

      <!-- Main Content -->
      <div class="bg-theme-card rounded-2xl border border-theme-border shadow-2xl overflow-hidden">
        <!-- Header with gradient -->
        <div class="bg-theme-bg-muted border-b border-theme-bg-muted p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-theme-primary rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-theme-primary">Liste des Avertissements</h2>
                <p class="text-theme-text-muted mt-1">{{ warnings.length }} avertissement(s) au total</p>
              </div>
            </div>
            
            <button
              v-if="canManageWarnings"
              @click="openAddWarningModal"
              class="px-6 py-3 bg-theme-primary hover:bg-theme-warning text-white font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-primary/30 flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Ajouter un avertissement</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Warnings Table -->
          <div v-if="warnings.length > 0" class="overflow-hidden rounded-xl border border-theme-bg-muted">
            <table class="w-full text-left">
              <thead class="bg-theme-bg-muted border-b border-theme-bg-muted">
                <tr class="text-theme-text">
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Description</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Auteur</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-theme-card divide-y divide-gray-700">
                <tr
                  v-for="warning in warnings"
                  :key="warning.id"
                  class="hover:bg-theme-bg-muted transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <span class="text-theme-text">{{ formatDate(warning.createdAt) }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="max-w-md">
                      <p class="text-theme-text text-sm leading-relaxed">{{ warning.description }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="warning.authorCharacter" class="flex items-center space-x-3">
                      <img
                        :src="getClassIcon(warning.authorCharacter.class)"
                        :alt="`Classe ${warning.authorCharacter.class}`"
                        class="w-8 h-8 rounded-lg border-2 border-theme-border"
                      />
                      <span class="text-theme-text">{{ warning.authorCharacter.pseudo }}</span>
                    </div>
                    <span v-else class="text-theme-text-muted italic">Inconnu</span>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="canManageWarnings" class="flex justify-center space-x-2">
                      <button
                        @click="openEditWarningModal(warning)"
                        class="px-3 py-1 bg-theme-primary hover:bg-theme-primary-hover text-white text-xs font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-primary/30 shadow-sm hover:shadow-md"
                        title="Modifier"
                      >
                        Modifier
                      </button>
                      <button
                        @click="openDeleteWarningModal(warning)"
                        class="px-3 py-1 bg-theme-error hover:bg-theme-error/80 text-white text-xs font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
                        title="Supprimer"
                      >
                        Supprimer
                      </button>
                    </div>
                    <div v-else class="text-center text-theme-text-muted text-sm">
                      Lecture seule
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-theme-text-muted">
              <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-lg font-medium text-theme-text-muted">Aucun avertissement</p>
              <p class="text-sm text-theme-text-muted">Ce personnage n'a pas encore reçu d'avertissement</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Warning Modal -->
    <div
      v-if="showAddWarningModal"
      class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeAddWarningModal"
    >
      <div class="w-full max-w-2xl bg-theme-card border border-theme-border rounded-2xl shadow-2xl relative">
        <!-- Header -->
        <div class="bg-theme-bg-muted border-b border-theme-bg-muted p-6 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-primary rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-theme-primary">Ajouter un Avertissement</h2>
            </div>
            
            <button
              class="w-8 h-8 bg-theme-bg-muted hover:bg-theme-bg-muted text-theme-text-muted hover:text-theme-text rounded-lg flex items-center justify-center transition-all duration-200"
              @click="closeAddWarningModal"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form Content -->
        <div class="p-6 space-y-6">
          <!-- Description Field -->
          <div class="space-y-2">
            <label for="description" class="block text-sm font-medium text-theme-text">
              Description <span class="text-theme-error">*</span>
            </label>
            <textarea
              id="description"
              v-model="newWarning.description"
              rows="4"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400 resize-none"
              placeholder="Décrivez la raison de l'avertissement"
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-theme-text-muted">
              {{ newWarning.description.length }}/1000
            </div>
          </div>

          <!-- Author Character Selection -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-theme-text">
              Personnage Auteur <span class="text-theme-error">*</span>
            </label>
            
            <!-- If author character is selected -->
            <div v-if="newWarning.authorCharacterId" class="flex items-center space-x-4 p-4 bg-theme-bg-muted rounded-lg border border-theme-border">
              <img :src="selectedAuthorIcon" :alt="`Classe ${selectedAuthorName}`" class="w-10 h-10 rounded-lg border-2 border-theme-border" />
              <div class="flex-1">
                <span class="text-lg font-semibold text-theme-primary">{{ selectedAuthorName }}</span>
              </div>
              <button
                type="button"
                @click="clearSelectedAuthor"
                class="w-8 h-8 bg-theme-error hover:bg-theme-error/80 text-white rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
                title="Changer d'auteur"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <!-- If no author character is selected -->
            <div v-else class="space-y-3">
              <input
                type="text"
                v-model="authorSearchQuery"
                placeholder="Rechercher un auteur..."
                class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              />
              <div class="max-h-48 overflow-y-auto bg-theme-bg-muted border-2 border-theme-border rounded-lg p-3">
                <div
                  v-for="char in filteredLeadCharacters"
                  :key="char.id"
                  @click="selectAuthor(char)"
                  class="flex items-center space-x-3 p-3 cursor-pointer hover:bg-theme-bg-muted rounded-lg transition-colors duration-200 group"
                >
                  <img :src="getClassIcon(char.class)" :alt="`Classe ${char.class}`" class="w-8 h-8 rounded-lg border-2 border-theme-border group-hover:border-theme-primary transition-colors duration-200" />
                  <span class="text-base text-theme-text group-hover:text-theme-primary transition-colors duration-200">{{ char.pseudo }} - {{ char.rank.name }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeAddWarningModal"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="addWarning"
              class="px-6 py-2 bg-theme-primary hover:bg-theme-warning text-theme-bg font-bold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-primary/30"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Warning Modal -->
    <div
      v-if="showEditWarningModal"
      class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeEditWarningModal"
    >
      <div class="w-full max-w-2xl bg-theme-card border border-theme-border rounded-2xl shadow-2xl relative">
        <!-- Header -->
        <div class="bg-theme-bg-muted border-b border-theme-bg-muted p-6 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-theme-primary-hover rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-theme-primary">Modifier l'Avertissement</h2>
            </div>
            
            <button
              class="w-8 h-8 bg-theme-bg-muted hover:bg-theme-bg-muted text-theme-text-muted hover:text-theme-text rounded-lg flex items-center justify-center transition-all duration-200"
              @click="closeEditWarningModal"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form Content -->
        <div class="p-6 space-y-6">
          <!-- Description Field -->
          <div class="space-y-2">
            <label for="edit-description" class="block text-sm font-medium text-theme-text">
              Description <span class="text-theme-error">*</span>
            </label>
            <textarea
              id="edit-description"
              v-model="editWarning.description"
              rows="4"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400 resize-none"
              placeholder="Décrivez la raison de l'avertissement"
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-theme-text-muted">
              {{ editWarning.description.length }}/1000
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeEditWarningModal"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="updateWarning"
              class="px-6 py-2 bg-theme-primary hover:bg-theme-primary-hover text-theme-bg font-bold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-primary/30"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteWarningModal"
      class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeDeleteWarningModal"
    >
      <div class="w-full max-w-md bg-theme-card border border-theme-border rounded-2xl shadow-2xl">
        <!-- Header -->
        <div class="bg-theme-error border-b border-theme-error p-6 rounded-t-2xl">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-theme-error rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-theme-text">Confirmer la Suppression</h3>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <p class="text-theme-text mb-6">
            Êtes-vous sûr de vouloir supprimer cet avertissement ?
          </p>
          
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteWarningModal"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="deleteWarning"
                              class="px-4 py-2 bg-theme-error hover:bg-theme-error/80 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Suppression...' : 'Supprimer' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Notification from '@/components/NotificationCenter.vue';
import { useAuthStore } from '@/stores/authStore';
import { API_URL } from '@/config/apiUrl';

export default {
  components: {
    Notification
  },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {

      characterId: null,
      character: null,
      warnings: [],
      leadCharacters: [],
      showAddWarningModal: false,
      showEditWarningModal: false,
      showDeleteWarningModal: false,
      newWarning: {
        description: '',
        authorCharacterId: ''
      },
      editWarning: {
        id: null,
        description: ''
      },
      selectedWarning: null,
      isSubmitting: false,
      authorSearchQuery: '',
      selectedAuthorName: '',
      selectedAuthorIcon: '',
      characterPseudo: '',
      classIcons: {
        sram: new URL('@/assets/icon_classe/sram.avif', import.meta.url).href,
        forgelance: new URL('@/assets/icon_classe/forgelance.avif', import.meta.url).href,
        cra: new URL('@/assets/icon_classe/cra.avif', import.meta.url).href,
        ecaflip: new URL('@/assets/icon_classe/ecaflip.avif', import.meta.url).href,
        eniripsa: new URL('@/assets/icon_classe/eniripsa.avif', import.meta.url).href,
        enutrof: new URL('@/assets/icon_classe/enutrof.avif', import.meta.url).href,
        feca: new URL('@/assets/icon_classe/feca.avif', import.meta.url).href,
        eliotrope: new URL('@/assets/icon_classe/eliotrope.avif', import.meta.url).href,
        iop: new URL('@/assets/icon_classe/iop.avif', import.meta.url).href,
        osamodas: new URL('@/assets/icon_classe/osamodas.avif', import.meta.url).href,
        pandawa: new URL('@/assets/icon_classe/pandawa.avif', import.meta.url).href,
        roublard: new URL('@/assets/icon_classe/roublard.avif', import.meta.url).href,
        sacrieur: new URL('@/assets/icon_classe/sacrieur.avif', import.meta.url).href,
        sadida: new URL('@/assets/icon_classe/sadida.avif', import.meta.url).href,
        steamer: new URL('@/assets/icon_classe/steamer.avif', import.meta.url).href,
        xelor: new URL('@/assets/icon_classe/xelor.avif', import.meta.url).href,
        zobal: new URL('@/assets/icon_classe/zobal.avif', import.meta.url).href,
        huppermage: new URL('@/assets/icon_classe/huppermage.avif', import.meta.url).href,
        ouginak: new URL('@/assets/icon_classe/ouginak.avif', import.meta.url).href,
      }
    };
  },
  created() {
    // Get character ID from route params
    this.characterId = this.$route.params.id;
    console.log("id", this.characterId)
    if (this.characterId) {
      this.fetchCharacter();
      this.fetchWarnings();
      this.fetchLeadCharacters();
    }
  },
  computed: {
    filteredLeadCharacters() {
      if (!this.authorSearchQuery) return this.leadCharacters;
      const query = this.authorSearchQuery.toLowerCase();
      return this.leadCharacters.filter(character => 
        character.pseudo.toLowerCase().includes(query)
      );
    },
    canManageWarnings() {
      const roles = this.authStore.user?.roles || [];
      return roles.includes('ROLE_OWNERS');
    }
  },
  methods: {
    getClassIcon(className) {
      return this.classIcons[className] || '';
    },
    selectAuthor(character) {
      this.newWarning.authorCharacterId = character.id;
      this.selectedAuthorName = character.pseudo;
      this.selectedAuthorIcon = this.getClassIcon(character.class);
      this.authorSearchQuery = '';
    },
    clearSelectedAuthor() {
      this.newWarning.authorCharacterId = '';
      this.selectedAuthorName = '';
      this.selectedAuthorIcon = '';
      this.authorSearchQuery = '';
    },
    async fetchCharacter() {
      this.characterPseudo = this.$route.params.pseudo
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
        minute: '2-digit'
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
      this.newWarning = { 
        description: '',
        authorCharacterId: ''
      };
      this.showAddWarningModal = true;
    },
    closeAddWarningModal() {
      this.showAddWarningModal = false;
    },
    openEditWarningModal(warning) {
      this.editWarning = {
        id: warning.id,
        description: warning.description
      };
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
        this.$refs.notificationRef.showNotification('La description et l\'auteur sont obligatoires', 'error');
        return;
      }

      this.isSubmitting = true;
      try {
        const response = await axios.post(`${API_URL}/warnings`, {
          characterId: this.characterId,
          description: this.newWarning.description,
          authorCharacterId: this.newWarning.authorCharacterId
        });

        // Add the new warning to the list
        this.warnings.unshift(response.data);
        this.closeAddWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement ajouté avec succès');
      } catch (error) {
        console.error('Error adding warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Erreur lors de l\'ajout de l\'avertissement',
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
          description: this.editWarning.description
        });

        // Update the warning in the list
        const index = this.warnings.findIndex(w => w.id === this.editWarning.id);
        if (index !== -1) {
          this.warnings[index] = response.data;
        }

        this.closeEditWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement mis à jour avec succès');
      } catch (error) {
        console.error('Error updating warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Erreur lors de la mise à jour de l\'avertissement',
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    },
    async deleteWarning() {
      if (!this.selectedWarning) return;

      this.isSubmitting = true;
      try {
        await axios.delete(`${API_URL}/warnings/${this.selectedWarning.id}`);

        // Remove the warning from the list
        this.warnings = this.warnings.filter(w => w.id !== this.selectedWarning.id);
        this.closeDeleteWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement supprimé avec succès');
      } catch (error) {
        console.error('Error deleting warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Erreur lors de la suppression de l\'avertissement',
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style scoped>
/* Table styling */
table {
  border-spacing: 0;
}

tbody tr:last-child td {
  border-bottom: none;
}
</style>
