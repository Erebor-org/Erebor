<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Header Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6"
    >
      <h1 class="text-3xl font-bold text-[#b02e2e] mb-4 text-center">
        Warnings for {{ character ? character.pseudo : 'Character' }}
      </h1>

      <!-- Back Button -->
      <div class="mb-4">
        <button
          @click="goBack"
          class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e] transition-all duration-300"
        >
          &larr; Back to Characters
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="w-11/12 max-w-7xl">
      <!-- Warnings List -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-[#b02e2e]">Warnings</h2>
          <button
            @click="openAddWarningModal"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
          >
            Add Warning
          </button>
        </div>

        <!-- Warnings Table -->
        <div v-if="warnings.length > 0" class="overflow-y-auto max-h-96">
          <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
            <thead class="sticky top-0 bg-[#b02e2e] z-10">
              <tr class="text-[#f3d9b1] text-lg">
                <th class="p-4">Date</th>
                <th class="p-4">Description</th>
                <th class="p-4">Author</th>
                <th class="p-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="warning in warnings"
                :key="warning.id"
                class="transition-all hover:bg-[#f3d9b1]/30 border-b border-[#b07d46]/20"
              >
                <td class="p-4 text-[#b07d46]">
                  {{ formatDate(warning.createdAt) }}
                </td>
                <td class="p-4 text-[#b07d46] text-left">
                  {{ warning.description }}
                </td>
                <td class="p-4 text-[#b07d46]">
                  {{ warning.author ? warning.author.username : 'Unknown' }}
                </td>
                <td class="p-4">
                  <div class="flex justify-center space-x-2">
                    <button
                      @click="openEditWarningModal(warning)"
                      class="text-[#b07d46] hover:text-[#9c682e] transition-all duration-300"
                      title="Edit"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      @click="openDeleteWarningModal(warning)"
                      class="text-[#b02e2e] hover:text-[#942828] transition-all duration-300"
                      title="Delete"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center p-8 text-[#b07d46] italic">
          No warnings found for this character.
        </div>
      </div>
    </div>

    <!-- Add Warning Modal -->
    <div
      v-if="showAddWarningModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeAddWarningModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-full max-w-md relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeAddWarningModal"
        >
          &times;
        </button>
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Add Warning</h3>
        <form @submit.prevent="addWarning">
          <div class="mb-4">
            <label for="description" class="block text-[#b07d46] font-bold mb-2">Description</label>
            <textarea
              id="description"
              v-model="newWarning.description"
              class="w-full border-2 border-[#b07d46] rounded-lg p-2 min-h-[100px] focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              placeholder="Enter warning description..."
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-[#b07d46]">
              {{ newWarning.description.length }}/1000
            </div>
          </div>
          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="closeAddWarningModal"
              class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Warning Modal -->
    <div
      v-if="showEditWarningModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeEditWarningModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-full max-w-md relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeEditWarningModal"
        >
          &times;
        </button>
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Edit Warning</h3>
        <form @submit.prevent="updateWarning">
          <div class="mb-4">
            <label for="edit-description" class="block text-[#b07d46] font-bold mb-2"
              >Description</label
            >
            <textarea
              id="edit-description"
              v-model="editWarning.description"
              class="w-full border-2 border-[#b07d46] rounded-lg p-2 min-h-[100px] focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              placeholder="Enter warning description..."
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-[#b07d46]">
              {{ editWarning.description.length }}/1000
            </div>
          </div>
          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="closeEditWarningModal"
              class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Warning Modal -->
    <div
      v-if="showDeleteWarningModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeDeleteWarningModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-full max-w-md relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeDeleteWarningModal"
        >
          &times;
        </button>
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Delete Warning</h3>
        <p class="text-[#b07d46] mb-6">Are you sure you want to delete this warning?</p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeDeleteWarningModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Cancel
          </button>
          <button
            @click="deleteWarning"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            :disabled="isSubmitting"
          >
            {{ isSubmitting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
import Notification from '@/components/NotificationCenter.vue';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    Notification
  },
  data() {
    return {
      backgroundImage: members_bg,
      characterId: null,
      character: null,
      warnings: [],
      showAddWarningModal: false,
      showEditWarningModal: false,
      showDeleteWarningModal: false,
      newWarning: {
        description: ''
      },
      editWarning: {
        id: null,
        description: ''
      },
      selectedWarning: null,
      isSubmitting: false
    };
  },
  created() {
    // Get character ID from route params
    this.characterId = this.$route.params.id;
    if (this.characterId) {
      this.fetchCharacter();
      this.fetchWarnings();
    }
  },
  methods: {
    async fetchCharacter() {
      try {
        const response = await axios.get(`${API_URL}/characters/${this.characterId}`);
        this.character = response.data;
      } catch (error) {
        console.error('Error fetching character:', error);
        this.$refs.notificationRef.showNotification('Error fetching character details', 'error');
      }
    },
    async fetchWarnings() {
      try {
        const response = await axios.get(`${API_URL}/warnings/character/${this.characterId}`);
        this.warnings = response.data;
      } catch (error) {
        console.error('Error fetching warnings:', error);
        this.$refs.notificationRef.showNotification('Error fetching warnings', 'error');
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
      this.$router.push('/members');
    },
    openAddWarningModal() {
      this.newWarning = { description: '' };
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
      if (!this.newWarning.description.trim()) {
        this.$refs.notificationRef.showNotification('Description is required', 'error');
        return;
      }

      this.isSubmitting = true;
      try {
        const response = await axios.post(`${API_URL}/warnings`, {
          characterId: this.characterId,
          description: this.newWarning.description
        });

        // Add the new warning to the list
        this.warnings.unshift(response.data);
        this.closeAddWarningModal();
        this.$refs.notificationRef.showNotification('Warning added successfully');
      } catch (error) {
        console.error('Error adding warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Error adding warning',
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    },
    async updateWarning() {
      if (!this.editWarning.description.trim()) {
        this.$refs.notificationRef.showNotification('Description is required', 'error');
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
        this.$refs.notificationRef.showNotification('Warning updated successfully');
      } catch (error) {
        console.error('Error updating warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Error updating warning',
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
        this.$refs.notificationRef.showNotification('Warning deleted successfully');
      } catch (error) {
        console.error('Error deleting warning:', error);
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Error deleting warning',
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
