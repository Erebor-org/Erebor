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
      <h1 class="text-3xl font-bold text-[#b02e2e] mb-4 text-center">Gestion des avertissements</h1>
    </div>

    <!-- Main Content -->
    <div class="w-11/12 max-w-7xl">
      <!-- Warnings List -->
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
        <div class="flex flex-col space-y-4 mb-6">
          <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#b02e2e]">Tous les avertissements</h2>
            <button
              @click="openAddWarningModal"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
            >
              Ajouter un avertissement
            </button>
          </div>

          <!-- Search Bar -->
          <div class="w-full">
            <input
              v-model="searchQuery"
              placeholder="Rechercher par pseudo..."
              class="w-full md:w-1/2 border-2 border-[#b07d46] bg-[#fffaf0] rounded-full py-2 px-6 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            />
          </div>
        </div>

        <!-- Warnings Table -->
        <div v-if="warnings.length > 0" class="overflow-y-auto max-h-96">
          <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
            <thead class="sticky top-0 bg-[#b02e2e] z-10">
              <tr class="text-[#f3d9b1] text-lg">
                <th class="p-4">Date</th>
                <th class="p-4">Personnage</th>
                <th class="p-4">Description</th>
                <th class="p-4">Auteur</th>
                <th class="p-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="warning in displayWarnings"
                :key="warning.id"
                class="transition-all hover:bg-[#f3d9b1]/30 border-b border-[#b07d46]/20"
              >
                <td class="p-4 text-[#b07d46]">
                  {{ formatDate(warning.createdAt) }}
                </td>
                <td class="p-4 text-[#b07d46]">
                  <div class="flex items-center gap-2">
                    <img :src="getClassIcon(warning.character.class)" alt="Class" class="w-7 h-7" />
                    <div>
                      {{ warning.character.pseudo }}
                    </div>
                  </div>
                </td>
                <td class="p-4 text-[#b07d46] text-left">
                  <div class="max-h-20 overflow-y-auto pr-2">
                    {{ warning.description }}
                  </div>
                </td>
                <td class="p-4 text-[#b07d46]" v-if="warning.authorCharacter">
                  <div class="flex items-center gap-2">
                    <img
                      :src="getClassIcon(warning.authorCharacter.class)"
                      alt="Class"
                      class="w-7 h-7"
                    />
                    <div>
                      {{ warning.authorCharacter.pseudo }}
                    </div>
                  </div>
                </td>
                <td class="p-4 text-[#b07d46]" v-else>Unknown</td>
                <td class="p-4">
                  <div class="flex justify-center space-x-2">
                    <button
                      @click="openEditWarningModal(warning)"
                      class="bg-[#b07d46] text-white px-2 py-1 rounded hover:bg-[#9c682e] transition-all duration-300 text-xs"
                      title="Edit"
                    >
                      Modifier
                    </button>
                    <button
                      @click="openDeleteWarningModal(warning)"
                      class="bg-[#b02e2e] text-white px-2 py-1 rounded hover:bg-[#942828] transition-all duration-300 text-xs"
                      title="Delete"
                    >
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center p-8 text-[#b07d46] italic">Aucun avertissement trouvé.</div>
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
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Ajouter un Avertissement</h3>
        <form @submit.prevent="addWarning">
          <div class="mb-4">
            <label for="character" class="block text-[#b07d46] font-bold mb-2">Personnage</label>

            <!-- If character is selected -->
            <div v-if="newWarning.characterId" class="flex items-center gap-3">
              <img :src="selectedCharacterIcon" alt="Class" class="w-10 h-10 rounded-full" />
              <span class="text-base font-semibold text-[#b07d46]">{{
                selectedCharacterName
              }}</span>
              <button
                type="button"
                @click="clearSelectedCharacter"
                class="text-red-500 text-lg font-bold focus:outline-none"
              >
                &times;
              </button>
            </div>

            <!-- If no character is selected -->
            <div v-else>
              <input
                type="text"
                v-model="characterSearchQuery"
                placeholder="Rechercher un personnage..."
                class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              />
              <ul
                class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
              >
                <li
                  v-for="char in filteredCharacters"
                  :key="char.id"
                  @click="selectCharacter(char)"
                  class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
                >
                  <img :src="getClassIcon(char.class)" alt="Class" class="w-7 h-7" />
                  <span class="text-base text-[#b07d46]">{{ char.pseudo }}</span>
                </li>
              </ul>
            </div>
          </div>
          <div class="mb-4">
            <label for="description" class="block text-[#b07d46] font-bold mb-2">Description</label>
            <textarea
              id="description"
              v-model="newWarning.description"
              class="w-full border-2 border-[#b07d46] rounded-lg p-2 min-h-[100px] focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              placeholder="Entrer la description de l'avertissement..."
              required
              maxlength="1000"
            ></textarea>
            <div class="text-right text-sm text-[#b07d46]">
              {{ newWarning.description.length }}/1000
            </div>
          </div>
          <div class="mb-4">
            <label for="authorCharacter" class="block text-[#b07d46] font-bold mb-2"
              >Personnage Auteur</label
            >

            <!-- If author character is selected -->
            <div v-if="newWarning.authorCharacterId" class="flex items-center gap-3">
              <img :src="selectedAuthorIcon" alt="Class" class="w-10 h-10 rounded-full" />
              <span class="text-base font-semibold text-[#b07d46]">{{ selectedAuthorName }}</span>
              <button
                type="button"
                @click="clearSelectedAuthor"
                class="text-red-500 text-lg font-bold focus:outline-none"
              >
                &times;
              </button>
            </div>

            <!-- If no author character is selected -->
            <div v-else>
              <input
                type="text"
                v-model="authorSearchQuery"
                placeholder="Rechercher un auteur..."
                class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              />
              <ul
                class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
              >
                <li
                  v-for="char in filteredLeadCharacters"
                  :key="char.id"
                  @click="selectAuthor(char)"
                  class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
                >
                  <img :src="getClassIcon(char.class)" alt="Class" class="w-7 h-7" />
                  <span class="text-base text-[#b07d46]"
                    >{{ char.pseudo }} - {{ char.rank.name }}</span
                  >
                </li>
              </ul>
            </div>
          </div>
          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="closeAddWarningModal"
              class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
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
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Modifier l'Avertissement</h3>
        <form @submit.prevent="updateWarning">
          <div class="mb-4">
            <label for="edit-description" class="block text-[#b07d46] font-bold mb-2"
              >Description</label
            >
            <textarea
              id="edit-description"
              v-model="editWarning.description"
              class="w-full border-2 border-[#b07d46] rounded-lg p-2 min-h-[100px] focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              placeholder="Entrer la description de l'avertissement..."
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
              Annuler
            </button>
            <button
              type="submit"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer' }}
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
        <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Supprimer l'Avertissement</h3>
        <p class="text-[#b07d46] mb-6">Êtes-vous sûr de vouloir supprimer cet avertissement ?</p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeDeleteWarningModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="deleteWarning"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            :disabled="isSubmitting"
          >
            {{ isSubmitting ? 'Suppression...' : 'Supprimer' }}
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
    Notification,
  },
  data() {
    return {
      backgroundImage: members_bg,
      warnings: [],
      allCharacters: [],
      leadCharacters: [],
      searchQuery: '',
      filteredWarnings: [],
      showAddWarningModal: false,
      showEditWarningModal: false,
      showDeleteWarningModal: false,
      newWarning: {
        characterId: '',
        description: '',
        authorCharacterId: '',
      },
      editWarning: {
        id: null,
        description: '',
      },
      selectedWarning: null,
      isSubmitting: false,
      characterSearchQuery: '',
      authorSearchQuery: '',
      selectedCharacterName: '',
      selectedCharacterIcon: '',
      selectedAuthorName: '',
      selectedAuthorIcon: '',
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
      },
    };
  },
  created() {
    this.fetchWarnings();
    this.fetchCharacters();
    this.fetchLeadCharacters();
  },
  watch: {
    warnings: {
      immediate: true,
      handler() {
        this.filterWarnings();
      },
    },
    searchQuery() {
      this.filterWarnings();
    },
  },
  computed: {
    // Display filtered warnings based on search query
    displayWarnings() {
      if (!this.searchQuery) return this.warnings;
      return this.filteredWarnings;
    },
    filteredCharacters() {
      if (!this.characterSearchQuery) return this.allCharacters;
      const query = this.characterSearchQuery.toLowerCase();
      return this.allCharacters.filter(character => character.pseudo.toLowerCase().includes(query));
    },
    filteredLeadCharacters() {
      if (!this.authorSearchQuery) return this.leadCharacters;
      const query = this.authorSearchQuery.toLowerCase();
      return this.leadCharacters.filter(character =>
        character.pseudo.toLowerCase().includes(query)
      );
    },
  },
  methods: {
    // Filter warnings based on search query
    filterWarnings() {
      if (!this.searchQuery || !this.warnings.length) {
        this.filteredWarnings = this.warnings;
        return;
      }

      const query = this.searchQuery.toLowerCase();
      this.filteredWarnings = this.warnings.filter(warning => {
        const characterMatch = warning.character.pseudo.toLowerCase().includes(query);
        const classMatch = warning.character.class.toLowerCase().includes(query);
        const descriptionMatch = warning.description.toLowerCase().includes(query);
        const authorMatch = warning.authorCharacter
          ? warning.authorCharacter.pseudo.toLowerCase().includes(query)
          : false;

        return characterMatch || classMatch || descriptionMatch || authorMatch;
      });
    },
    getClassIcon(className) {
      return this.classIcons[className] || '';
    },
    selectCharacter(character) {
      this.newWarning.characterId = character.id;
      this.selectedCharacterName = character.pseudo;
      this.selectedCharacterIcon = this.getClassIcon(character.class);
      this.characterSearchQuery = '';
    },
    clearSelectedCharacter() {
      this.newWarning.characterId = '';
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
      this.characterSearchQuery = '';
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
    async fetchWarnings() {
      try {
        const response = await axios.get(`${API_URL}/warnings`);
        this.warnings = response.data.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
      } catch (error) {
        console.error('Error fetching warnings:', error);
        this.$refs.notificationRef.showNotification(
          'Erreur lors de la récupération des avertissements',
          'error'
        );
      }
    },
    async fetchCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters`);
        this.allCharacters = response.data.filter(char => !char.isArchived);
      } catch (error) {
        console.error('Error fetching characters:', error);
        this.$refs.notificationRef.showNotification(
          'Erreur lors de la récupération des personnages',
          'error'
        );
      }
    },
    async fetchLeadCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters/lead`);
        this.leadCharacters = response.data;
      } catch (error) {
        console.error('Error fetching lead characters:', error);
        this.$refs.notificationRef.showNotification(
          'Erreur lors de la récupération des personnages principaux',
          'error'
        );
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
    openAddWarningModal() {
      this.newWarning = {
        characterId: '',
        description: '',
        authorCharacterId: '',
      };
      this.showAddWarningModal = true;
    },
    closeAddWarningModal() {
      this.showAddWarningModal = false;
    },
    openEditWarningModal(warning) {
      this.editWarning = {
        id: warning.id,
        description: warning.description,
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
      if (
        !this.newWarning.characterId ||
        !this.newWarning.description ||
        !this.newWarning.authorCharacterId
      ) {
        this.$refs.notificationRef.showNotification('Tous les champs sont obligatoires', 'error');
        return;
      }

      this.isSubmitting = true;
      try {
        const response = await axios.post(`${API_URL}/warnings`, {
          characterId: this.newWarning.characterId,
          description: this.newWarning.description,
          authorCharacterId: this.newWarning.authorCharacterId,
        });

        // Add the new warning to the list
        this.warnings.unshift(response.data);
        this.closeAddWarningModal();
        this.$refs.notificationRef.showNotification('Avertissement ajouté avec succès');

        // Refresh warnings to get updated data
        this.fetchWarnings();
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
          error.response?.data?.error || "Erreur lors de la mise à jour de l'avertissement",
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
          error.response?.data?.error || "Erreur lors de la suppression de l'avertissement",
          'error'
        );
      } finally {
        this.isSubmitting = false;
      }
    },
  },
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
