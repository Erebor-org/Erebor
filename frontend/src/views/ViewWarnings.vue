<template>
  <div class="min-h-screen bg-black text-white">
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-amber-400 mb-6">Avertissements de {{ characterPseudo }}</h1>
        <div class="w-32 h-1 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-600 mx-auto rounded-full shadow-lg shadow-amber-500/50"></div>
        <p class="text-gray-400 mt-6 text-lg">Gérez les avertissements de ce personnage</p>
      </div>

      <!-- Back Button -->
      <div class="mb-8 flex justify-center">
        <button
          @click="goBack"
          class="px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-800 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-gray-500/30 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>Retour aux personnages</span>
        </button>
      </div>

      <!-- Main Content -->
      <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl overflow-hidden">
        <!-- Header with gradient -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-amber-400">Liste des Avertissements</h2>
                <p class="text-gray-400 mt-1">{{ warnings.length }} avertissement(s) au total</p>
              </div>
            </div>
            
            <button
              @click="openAddWarningModal"
              class="px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-black font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-yellow-500/30 flex items-center space-x-2"
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
          <div v-if="warnings.length > 0" class="overflow-hidden rounded-xl border border-gray-700">
            <table class="w-full text-left">
              <thead class="bg-gray-800 border-b border-gray-700">
                <tr class="text-gray-300">
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Description</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Auteur</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-gray-900 divide-y divide-gray-700">
                <tr
                  v-for="warning in warnings"
                  :key="warning.id"
                  class="hover:bg-gray-800 transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <span class="text-gray-300">{{ formatDate(warning.createdAt) }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="max-w-md">
                      <p class="text-gray-300 text-sm leading-relaxed">{{ warning.description }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="warning.authorCharacter" class="flex items-center space-x-3">
                      <img
                        :src="getClassIcon(warning.authorCharacter.class)"
                        :alt="`Classe ${warning.authorCharacter.class}`"
                        class="w-8 h-8 rounded-lg border-2 border-gray-600"
                      />
                      <span class="text-gray-300">{{ warning.authorCharacter.pseudo }}</span>
                    </div>
                    <span v-else class="text-gray-500 italic">Inconnu</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex justify-center space-x-2">
                      <button
                        @click="openEditWarningModal(warning)"
                        class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                        title="Modifier"
                      >
                        Modifier
                      </button>
                      <button
                        @click="openDeleteWarningModal(warning)"
                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                        title="Supprimer"
                      >
                        Supprimer
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-gray-400">
              <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-lg font-medium text-gray-500">Aucun avertissement</p>
              <p class="text-sm text-gray-600">Ce personnage n'a pas encore reçu d'avertissement</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Warning Modal -->
    <div
      v-if="showAddWarningModal"
      class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeAddWarningModal"
    >
      <div class="w-full max-w-2xl bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl relative">
        <!-- Header -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 p-6 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-amber-400">Ajouter un Avertissement</h2>
            </div>
            
            <button
              class="w-8 h-8 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition-all duration-200"
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
            <label for="description" class="block text-sm font-medium text-gray-300">
              Description <span class="text-red-400">*</span>
            </label>
            <textarea
              id="description"
              v-model="newWarning.description"
              rows="4"
              class="w-full bg-gray-800 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400 resize-none"
              placeholder="Décrivez la raison de l'avertissement"
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-gray-400">
              {{ newWarning.description.length }}/1000
            </div>
          </div>

          <!-- Author Character Selection -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-300">
              Personnage Auteur <span class="text-red-400">*</span>
            </label>
            
            <!-- If author character is selected -->
            <div v-if="newWarning.authorCharacterId" class="flex items-center space-x-4 p-4 bg-gray-800 rounded-lg border border-gray-600">
              <img :src="selectedAuthorIcon" :alt="`Classe ${selectedAuthorName}`" class="w-10 h-10 rounded-lg border-2 border-gray-600" />
              <div class="flex-1">
                <span class="text-lg font-semibold text-amber-400">{{ selectedAuthorName }}</span>
              </div>
              <button
                type="button"
                @click="clearSelectedAuthor"
                class="w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-105"
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
                class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
              />
              <div class="max-h-48 overflow-y-auto bg-gray-800 border-2 border-gray-600 rounded-lg p-3">
                <div
                  v-for="char in filteredLeadCharacters"
                  :key="char.id"
                  @click="selectAuthor(char)"
                  class="flex items-center space-x-3 p-3 cursor-pointer hover:bg-gray-700 rounded-lg transition-colors duration-200 group"
                >
                  <img :src="getClassIcon(char.class)" :alt="`Classe ${char.class}`" class="w-8 h-8 rounded-lg border-2 border-gray-600 group-hover:border-amber-500 transition-colors duration-200" />
                  <span class="text-base text-gray-300 group-hover:text-amber-400 transition-colors duration-200">{{ char.pseudo }} - {{ char.rank.name }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeAddWarningModal"
              class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="addWarning"
              class="px-6 py-2 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-black font-bold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-yellow-500/30"
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
      class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeEditWarningModal"
    >
      <div class="w-full max-w-2xl bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl relative">
        <!-- Header -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 p-6 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-amber-400">Modifier l'Avertissement</h2>
            </div>
            
            <button
              class="w-8 h-8 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition-all duration-200"
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
            <label for="edit-description" class="block text-sm font-medium text-gray-300">
              Description <span class="text-red-400">*</span>
            </label>
            <textarea
              id="edit-description"
              v-model="editWarning.description"
              rows="4"
              class="w-full bg-gray-800 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400 resize-none"
              placeholder="Décrivez la raison de l'avertissement"
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-gray-400">
              {{ editWarning.description.length }}/1000
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeEditWarningModal"
              class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="updateWarning"
              class="px-6 py-2 bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 hover:to-yellow-700 text-black font-bold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-amber-500/30"
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
      class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeDeleteWarningModal"
    >
      <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-800 to-red-900 border-b border-red-700 p-6 rounded-t-2xl">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-white">Confirmer la Suppression</h3>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <p class="text-gray-300 mb-6">
            Êtes-vous sûr de vouloir supprimer cet avertissement ?
          </p>
          
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteWarningModal"
              class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="deleteWarning"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-200"
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

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    Notification
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
