<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <!-- Notification -->
    <Notification ref="notificationRef" />
    
    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-theme-primary mb-6">Blacklist de la Guilde</h1>
        <div class="w-32 h-1 bg-theme-primary mx-auto rounded-full shadow-lg shadow-theme-primary/50"></div>
        <p class="text-theme-text-muted mt-6 text-lg">Gérez la liste des personnages bannis d'Erebor</p>
      </div>

      <!-- Blacklist Section -->
      <div class="bg-theme-card rounded-2xl border border-theme-border shadow-2xl overflow-hidden">
        <!-- Header with gradient -->
        <div class="bg-gradient-to-r from-theme-bg-muted to-theme-card border-b border-theme-bg-muted p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-theme-error to-theme-error rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-theme-primary">Liste des Personnages Bannis</h2>
                <p class="text-theme-text-muted mt-1">{{ filteredBlacklist.length }} personnage(s) en blacklist</p>
              </div>
            </div>
            
            <button
              @click="showModal = true"
              class="px-6 py-3 bg-gradient-to-r from-theme-error to-theme-error hover:from-theme-error hover:to-theme-error text-white font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-error/30 flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Ajouter un Personnage</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Search Bar -->
          <div class="mb-6">
            <div class="relative">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Rechercher un pseudo..."
                class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-xl py-3 px-4 pl-12 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              />
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <!-- Blacklist Table -->
          <div class="overflow-hidden rounded-xl border border-theme-bg-muted">
            <table class="w-full text-left">
              <thead class="bg-theme-bg-muted border-b border-theme-bg-muted">
                <tr class="text-theme-text">
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Pseudo</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Ankama Pseudo</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Raison</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-theme-card divide-y divide-gray-700">
                <tr
                  v-for="(entry, index) in filteredBlacklist"
                  :key="index"
                  class="hover:bg-theme-bg-muted transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <span class="text-lg font-bold text-theme-primary">{{ entry.pseudo }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-theme-text">{{ entry.ankamaPseudo }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-theme-text">{{ entry.reason }}</span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button
                      @click="openConfirmModal(index)"
                      class="px-4 py-2 bg-theme-error hover:bg-theme-error/80 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 flex items-center space-x-2 ml-auto shadow-sm hover:shadow-md"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <span>Supprimer</span>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredBlacklist.length === 0">
                  <td colspan="4" class="px-6 py-12 text-center">
                    <div class="text-theme-text-muted">
                      <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <p class="text-lg font-medium text-theme-text-muted">Aucun personnage en blacklist</p>
                      <p class="text-sm text-theme-text-muted">La liste est vide pour le moment</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Character Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="w-full max-w-lg bg-theme-card border border-theme-border rounded-2xl shadow-2xl relative">
        <!-- Header -->
        <div class="bg-gradient-to-r from-theme-bg-muted to-theme-card border-b border-theme-bg-muted p-6 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-br from-theme-error to-theme-error rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-theme-primary">Ajouter un Personnage</h2>
            </div>
            
            <button
              class="w-8 h-8 bg-theme-bg-muted hover:bg-theme-bg-muted text-theme-text-muted hover:text-theme-text rounded-lg flex items-center justify-center transition-all duration-200"
              @click="closeModal"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form Content -->
        <div class="p-6 space-y-6">
          <!-- Pseudo Field -->
          <div class="space-y-2">
            <label for="pseudo" class="block text-sm font-medium text-theme-text">
              Pseudo du Personnage <span class="text-theme-error">*</span>
            </label>
            <input
              type="text"
              id="pseudo"
              v-model="newBlacklist.pseudo"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              placeholder="Entrez le pseudo"
              required
            />
          </div>

          <!-- Ankama Pseudo Field -->
          <div class="space-y-2">
            <label for="ankamaPseudo" class="block text-sm font-medium text-theme-text">
              Pseudo Ankama <span class="text-theme-error">*</span>
            </label>
            <input
              type="text"
              id="ankamaPseudo"
              v-model="newBlacklist.ankamaPseudo"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              placeholder="Entrez le pseudo Ankama"
              required
            />
          </div>

          <!-- Reason Field -->
          <div class="space-y-2">
            <label for="reason" class="block text-sm font-medium text-theme-text">
              Raison <span class="text-theme-error">*</span>
            </label>
            <textarea
              id="reason"
              v-model="newBlacklist.reason"
              rows="3"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400 resize-none"
              placeholder="Décrivez la raison du bannissement"
              required
            ></textarea>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="addToBlacklist"
              class="px-6 py-2 bg-gradient-to-r from-theme-error to-theme-error hover:from-theme-error hover:to-theme-error text-theme-text font-bold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-error/30"
            >
              Ajouter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div
      v-if="showConfirmModal"
      class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="closeConfirmModal"
    >
      <div class="w-full max-w-md bg-theme-card border border-theme-border rounded-2xl shadow-2xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-theme-error to-theme-error border-b border-theme-error p-6 rounded-t-2xl">
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
            Êtes-vous sûr de vouloir supprimer <span class="text-theme-primary font-semibold">{{ blacklistToDelete?.pseudo }}</span> de la blacklist ?
          </p>
          
          <div class="flex justify-end space-x-3">
            <button
              @click="closeConfirmModal"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-200"
            >
              Annuler
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-theme-error hover:bg-theme-error/80 text-white font-medium rounded-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
            >
              Confirmer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Notification from '@/components/NotificationCenter.vue';
import axios from 'axios';
import { API_URL } from '@/config/apiUrl';
export default {
  components: {
    Notification,
  },
  data() {
    return {
      showModal: false, // Modal visibility state
      showConfirmModal: false, // State for delete confirmation modal
      blacklistToDelete: null, // Holds the member to be deleted
      searchQuery: '', // Search query
      newBlacklist: {
        pseudo: '',
        reason: '',
        ankamaPseudo: '',
      },
      blacklist: [],
    };
  },
  computed: {
    filteredBlacklist() {
      const query = this.searchQuery.toLowerCase();
      return this.blacklist.filter(entry => {
        return (
          (entry.pseudo && entry.pseudo.toLowerCase().includes(query)) ||
          (entry.ankamaPseudo && entry.ankamaPseudo.toLowerCase().includes(query))
        );
      });
    },
  },
  methods: {
    openConfirmModal(index) {
      this.blacklistToDelete = this.blacklist[index]; // Store the member to delete
      this.showConfirmModal = true; // Show the confirmation modal
    },
    closeConfirmModal() {
      this.showConfirmModal = false; // Hide the confirmation modal
      this.blacklistToDelete = null; // Clear the member reference
    },
    async addToBlacklist() {
      const existingEntry = this.blacklist.find(
        entry => entry.ankamaPseudo === this.newBlacklist.ankamaPseudo
      );

      if (existingEntry) {
        this.$refs.notificationRef.showNotification(
          `Le pseudo Ankama "${this.newBlacklist.ankamaPseudo}" existe déjà dans la blacklist.`
        );
        return;
      }
      try {
        // API POST request
        const response = await axios.post(`${API_URL}/blacklist`, {
          pseudo: this.newBlacklist.pseudo,
          ankamaPseudo: this.newBlacklist.ankamaPseudo,
          reason: this.newBlacklist.reason,
        });

        // Add the new entry to the blacklist array
        this.blacklist.push(this.newBlacklist);

        // Reset the form
        this.resetForm();
        this.closeModal(); // Close the modal

        // Show success notification
        this.$refs.notificationRef.showNotification(
          `${this.newBlacklist.pseudo} a bien été ajouté à la blacklist`
        );
        setTimeout(() => {
          // No specific notification state to reset here as it's a new addition
        }, 3000);

        return response.data;
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        console.log('An error occurred while creating the character.');
        this.$refs.notificationRef.showNotification(
          `Erreur lors de l'ajout de "${this.newBlacklist.pseudo}" à la blacklist: ${error.response?.data?.message || error.message}`
        );
      }
    },

    async fetchBlacklist() {
      try {
        const response = await axios.get(`${API_URL}/blacklist`);
        this.blacklist = response.data;
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
    async confirmDelete() {
      if (!this.blacklistToDelete) return;

      const member = this.blacklistToDelete;

      try {
        // API DELETE request
        await axios.delete(`${API_URL}/blacklist/${member.id}`);

        // Remove the entry from the blacklist array
        this.blacklist = this.blacklist.filter(entry => entry.id !== member.id);

        // Show success notification
        this.$refs.notificationRef.showNotification(
          `${member.pseudo} a bien été supprimé de la blacklist`
        );
        setTimeout(() => {
          // No specific notification state to reset here as it's a removal
        }, 3000);
      } catch (error) {
        console.error('Error removing character:', error.response?.data || error.message);
        console.log('An error occurred while removing the character.');
        this.$refs.notificationRef.showNotification(
          `Erreur lors de la suppression de "${member.pseudo}" de la blacklist: ${error.response?.data?.message || error.message}`
        );
      } finally {
        this.closeConfirmModal(); // Close the confirmation modal
      }
    },
    closeModal() {
      this.showModal = false;
    },
    resetForm() {
      this.newBlacklist = {
        pseudo: '',
        reason: '',
        ankamaPseudo: '',
      };
    },
  },
  async mounted() {
    try {
      await this.fetchBlacklist();
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
};
</script>

<style scoped>
table {
  border-spacing: 0;
}
th,
td {
  border-bottom: 2px solid #b07d46;
}
</style>
