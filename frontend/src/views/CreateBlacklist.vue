<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Notification -->
    <Notification ref="notificationRef" />
    <!-- Space Between Tabs and Content -->
    <div class="my-8"></div>
    <!-- Blacklist Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-4 border-[#b02e2e] rounded-lg shadow-lg p-6 mb-8"
    >
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-[#b02e2e]">Blacklist de la Guilde</h2>
        <button
          @click="showModal = true"
          class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-lg shadow-md hover:bg-[#942828] transition-all duration-300"
        >
          Ajouter un Personnage
        </button>
      </div>

      <!-- Search Bar -->
      <div class="mb-6">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Rechercher un pseudo"
          class="w-full border-2 border-[#b02e2e] bg-white text-[#b02e2e] rounded-lg py-2 px-6 text-lg focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
        />
      </div>

      <!-- Blacklist Table -->
      <div class="overflow-y-auto max-h-96">
        <table class="w-full text-left border-collapse bg-white rounded-lg shadow-lg">
          <thead class="sticky top-0 bg-[#b02e2e] z-10">
            <tr class="text-[#f3d9b1] text-lg">
              <th class="p-4">Pseudo</th>
              <th class="p-4">Ankama pseudo</th>
              <th class="p-4">Raison</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(entry, index) in filteredBlacklist"
              :key="index"
              class="hover:bg-[#f3d9b1] hover:shadow-md transition-all"
            >
              <td class="p-4 text-[#b02e2e] font-bold">{{ entry.pseudo }}</td>
              <td class="p-4 text-[#b02e2e] font-bold">{{ entry.ankamaPseudo }}</td>
              <td class="p-4 text-[#b02e2e]">{{ entry.reason }}</td>
              <td class="p-4 text-right">
                <button
                  @click="openConfirmModal(index)"
                  class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828] transition-all duration-300"
                >
                  Supprimer
                </button>
              </td>
            </tr>
            <tr v-if="filteredBlacklist.length === 0">
              <td colspan="4" class="p-4 text-center text-[#b02e2e] font-bold">
                Aucun personnage trouvé.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Character Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div
        class="bg-white border-4 border-[#b02e2e] rounded-lg shadow-lg w-11/12 max-w-lg p-8 relative"
      >
        <!-- Close Button -->
        <button
          class="absolute top-4 right-4 text-[#b02e2e] font-bold text-xl hover:text-[#942828]"
          @click="closeModal"
        >
          &times;
        </button>
        <h2 class="text-3xl font-bold text-[#b02e2e] mb-6 text-center">Ajouter un Personnage</h2>

        <!-- Form Fields -->
        <div class="mb-6">
          <label for="pseudo" class="block text-lg font-medium text-[#b02e2e] mb-2">
            Pseudo du Personnage:
          </label>
          <input
            type="text"
            id="pseudo"
            v-model="newBlacklist.pseudo"
            class="w-full border-2 border-[#b02e2e] bg-white text-[#b02e2e] rounded-lg py-2 px-4 text-lg focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            placeholder="Entrez le pseudo"
            required
          />
        </div>

        <div class="mb-6">
          <label for="ankamaPseudo" class="block text-lg font-medium text-[#b02e2e] mb-2">
            Pseudo Ankama:
          </label>
          <input
            type="text"
            id="ankamaPseudo"
            v-model="newBlacklist.ankamaPseudo"
            class="w-full border-2 border-[#b02e2e] bg-white text-[#b02e2e] rounded-lg py-2 px-4 text-lg focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            placeholder="Entrez le pseudo Ankama"
            required
          />
        </div>

        <div class="mb-6">
          <label for="reason" class="block text-lg font-medium text-[#b02e2e] mb-2">
            Raison:
          </label>
          <textarea
            id="reason"
            v-model="newBlacklist.reason"
            class="w-full border-2 border-[#b02e2e] bg-white text-[#b02e2e] rounded-lg py-2 px-4 text-lg focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            placeholder="Entrez la raison"
            rows="4"
            required
          ></textarea>
        </div>

        <!-- Add Button -->
        <button
          @click="submitBlacklistMember(newBlacklist)"
          class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 rounded-lg hover:bg-[#942828] transition-all duration-300"
        >
          Ajouter à la Blacklist
        </button>
      </div>
    </div>

    <!-- Notifications -->
    <div
      v-if="showNotificationAddBlacklist"
      class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-4 px-6 rounded-lg shadow-lg z-50"
    >
      Personnage ajouté à la blacklist
    </div>
    <div
      v-if="showNotificationRemoveBlacklist"
      class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg shadow-lg z-50"
    >
      Personnage supprimé de la blacklist
    </div>

    <!-- Confirmation Modal -->
    <div
      v-if="showConfirmDelete"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeConfirmModal"
    >
      <div
        class="bg-white border-4 border-[#b02e2e] rounded-lg shadow-lg w-11/12 max-w-lg p-8 relative"
      >
        <h2 class="text-3xl font-bold text-[#b02e2e] mb-6 text-center">Confirmation</h2>
        <p class="text-lg text-[#b02e2e] mb-6 text-center">
          Êtes-vous sûr de vouloir supprimer "<strong>{{ confirmDeleteMember?.pseudo }}</strong
          >" de la blacklist ?
        </p>

        <div class="flex justify-center gap-6">
          <button
            @click="closeConfirmModal"
            class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400"
          >
            Annuler
          </button>
          <button
            @click="confirmDelete()"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-lg hover:bg-[#942828] transition-all duration-300"
          >
            Oui, supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Notification from '@/components/NotificationCenter.vue';
import register_bg from '@/assets/register_bg.webp';
import axios from 'axios';

export default {
  components: {
    Notification,
  },
  data() {
    return {
      backgroundImage: register_bg,
      showModal: false, // Modal visibility state
      showNotificationAddBlacklist: false,
      showNotificationRemoveBlacklist: false,
      showConfirmDelete: false, // State for delete confirmation modal
      confirmDeleteMember: null, // Holds the member to be deleted
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
      return this.blacklist.filter(entry =>
        entry.pseudo.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
  },
  methods: {
    openConfirmModal(index) {
      this.confirmDeleteMember = this.blacklist[index]; // Store the member to delete
      this.showConfirmDelete = true; // Show the confirmation modal
    },
    closeConfirmModal() {
      this.showConfirmDelete = false; // Hide the confirmation modal
      this.confirmDeleteMember = null; // Clear the member reference
    },
    async submitBlacklistMember(blacklistMember) {
      console.log('submit', blacklistMember);
      const existingEntry = this.blacklist.find(
        entry => entry.ankamaPseudo === blacklistMember.ankamaPseudo
      );

      if (existingEntry) {
        console.log('Ce pseudo Ankama existe déjà dans la blacklist.');
        return;
      }
      try {
        // API POST request
        const response = await axios.post('http://localhost:8000/blacklist', {
          pseudo: blacklistMember.pseudo,
          ankamaPseudo: blacklistMember.ankamaPseudo,
          reason: blacklistMember.reason,
        });

        // Add the new entry to the blacklist array
        this.blacklist.push(blacklistMember);

        // Reset the form
        this.resetForm();
        this.closeModal(); // Close the modal

        // Show success notification

        this.$refs.notificationRef.showNotification(
          `${blacklistMember.pseudo} a bien été ajouté à la blacklist`
        );
        setTimeout(() => {
          this.showNotificationAddBlacklist = false;
        }, 3000);

        return response.data;
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        console.log('An error occurred while creating the character.');
      }
    },

    async fetchBlacklist() {
      try {
        const response = await axios.get('http://localhost:8000/blacklist');
        this.blacklist = response.data;
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
    async confirmDelete() {
      if (!this.confirmDeleteMember) return;

      const member = this.confirmDeleteMember;

      try {
        // API DELETE request
        await axios.delete(`http://localhost:8000/blacklist/${member.id}`);

        // Remove the entry from the blacklist array
        this.blacklist = this.blacklist.filter(entry => entry.id !== member.id);

        // Show success notification
        this.$refs.notificationRef.showNotification(
          `${member.pseudo} a bien été supprimé de la blacklist`
        );
        setTimeout(() => {
          this.showNotificationRemoveBlacklist = false;
        }, 3000);
      } catch (error) {
        console.error('Error removing character:', error.response?.data || error.message);
        console.log('An error occurred while removing the character.');
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
