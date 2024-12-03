<template>
  <div
    class="w-full flex flex-col items-center justify-center bg-cover bg-center"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Main Block -->
    <div
      class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-11/12 max-w-4xl p-6 mb-6"
    >
      <!-- Title and Button -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-[#b02e2e]">Liste des Membres</h2>
        <button
          @click="showModalMember = true"
          class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
        >
          Ajouter un Personnage
        </button>
        <ImportMember
          :showModalMember="showModalMember"
          :fetchNotArchivedCharacters="fetchNotArchivedCharacters"
          @close="showModalMember = false"
        />
      </div>

      <!-- Search Bar -->
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Rechercher par pseudo, recruteur ou rang"
        class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] mb-4"
      />

      <!-- Member Table -->
      <div class="overflow-y-auto max-h-96">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#b02e2e] text-[#f3d9b1] text-lg">
              <th class="p-4">Classe</th>
              <th class="p-4">Pseudo</th>
              <th class="p-4">Recruteur</th>
              <th class="p-4">Rang</th>
              <th class="p-4">Date d'arrivée</th>
              <th class="p-4">Mules</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(member, index) in filteredMembers"
              :key="member.id || index"
              class="hover:bg-[#f3d9b1] hover:shadow-md transition-all group relative"
            >
              <!-- Classe Icon -->
              <td class="p-4">
                <img
                  :src="`${iconFolder}/${member.class}.avif`"
                  :alt="member.class"
                  class="w-10 h-10 object-cover"
                />
              </td>
              <!-- Pseudo -->
              <td class="p-4 text-[#b07d46] font-bold">{{ member?.pseudo || 'Unknown' }}</td>
              <!-- Recruteur -->
              <td class="p-4 text-[#b07d46]">{{ member?.recruiter?.pseudo || 'No Recruiter' }}</td>
              <!-- Rang -->
              <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
              <!-- Date -->
              <td class="p-4 text-[#b07d46]">{{ member?.createdAt || 'No Date' }}</td>
              <!-- View Mules -->
              <td class="p-4">
                <button
                  class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
                  @click="showMules(member.id)"
                >
                  Voir les mules
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mules Modal -->
    <div
      v-if="showMulesModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeMulesModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg p-6 w-1/2">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeMulesModal"
        >
          &times;
        </button>
        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Mules pour {{ currentCharacter?.pseudo }}</h2>
        <div v-if="currentCharacterMules.length > 0">
          <ul>
            <li
              v-for="(mule, index) in currentCharacterMules"
              :key="mule.id || index"
              class="flex items-center gap-4 mb-2"
            >
              <img
                :src="`${iconFolder}/${mule.class}.avif`"
                :alt="mule.class"
                class="w-8 h-8"
              />
              <div>
                <p class="text-lg font-medium text-[#b07d46]">{{ mule.pseudo }}</p>
                <p class="text-sm text-[#b07d46]">
                  <strong>Ankama Pseudo:</strong> {{ mule.ankamaPseudo || 'Unknown' }}
                </p>
              </div>
            </li>
          </ul>
        </div>
        <div v-else class="text-[#b07d46]">Aucun mule trouvé pour ce personnage.</div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
import ImportMember from '@/components/ImportMember.vue';

export default {
  components: {
    ImportMember,
  },
  data() {
    return {
      backgroundImage: members_bg, // Add your medieval-themed background image
      iconFolder: 'src/assets/icon_classe/', // Folder containing your class icons
      searchQuery: '',
      charactersNotArchived: [], // Holds the list of all characters
      showModal: false,
      showModalMember: false,
      selectedMember: null,
      showNotification: false,
      notArchivedMules: {}, // Stores all mules fetched at once, grouped by character ID
      showMulesModal: false,
      currentCharacter: null,
      currentCharacterMules: [],
    };
  },
  methods: {
    async fetchNotArchivedCharacters() {
      try {
        const response = await axios.get('http://localhost:8000/characters');

        console.log('Characters Response:', response.data); // Debug log
        const notArchivedCharacters = response.data.filter(character => !character.isArchived);
        this.charactersNotArchived = response.data.filter(character => !character.isArchived);
        console.log('Not archived characters:', this.charactersNotArchived);
        return notArchivedCharacters;
      } catch (error) {
        console.error(
          'Error fetching not archived characters:',
          error.response?.data || error.message
        );
        alert('An error occurred while fetching not archived characters.');
      }
    },
    async archiveCharacter(characterId) {
      try {
        const response = await axios.put(
          `http://localhost:8000/characters/${characterId}/archive`,
          {
            isArchived: true,
          }
        );
        console.log('Character archived successfully:', response.data);

        // Remove the character from the list
        this.charactersNotArchived = this.charactersNotArchived.filter(
          char => char.id !== characterId
        );
        this.showModal = false;
        this.showNotification = true;
        // Automatically hide the notification after 3 seconds
        setTimeout(() => {
          this.showNotification = false;
        }, 3000);
      } catch (error) {
        console.error('Error archiving character:', error.response?.data || error.message);
        alert('An error occurred while archiving the character.');
      }
    },
    async fetchAllMules() {
      try {
        const response = await axios.get('http://localhost:8000/mules');
        const mules = response.data.filter(mule => !mule.isArchived);
        this.notArchivedMules = mules.reduce((acc, mule) => {
          const charId = mule.mainCharacter?.id;
          if (!acc[charId]) acc[charId] = [];
          acc[charId].push(mule);
          return acc;
        }, {});
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
    filteredMulesByCharacter(characterId) {
      return this.notArchivedMules[characterId] || [];
    },
    showMules(characterId) {
      this.currentCharacter = this.charactersNotArchived.find(char => char.id === characterId);
      this.currentCharacterMules = this.filteredMulesByCharacter(characterId);
      this.showMulesModal = true;
    },
    closeMulesModal() {
      this.showMulesModal = false;
      this.currentCharacter = null;
      this.currentCharacterMules = [];
    },
    openModal(member) {
      this.selectedMember = member;
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    openModalMember() {
      this.showModalMember = true;
    },
    closeModalMember() {
      this.showModalMember = false;
    },
  },
  async mounted() {
    try {
      await this.fetchNotArchivedCharacters();
      await this.fetchAllMules(); // Fetch all mules once when the component mounts
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
  computed: {
    filteredMembers() {
      const query = this.searchQuery.toLowerCase();
      const normalize = str => str?.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      if (!this.charactersNotArchived) return []; // Ensure it defaults to an empty array
      return this.charactersNotArchived.filter(
        member =>
          normalize(member.pseudo).toLowerCase().includes(normalize(query)) ||
          normalize(member.recruiter?.pseudo).toLowerCase().includes(normalize(query)) ||
          normalize(member.class).toLowerCase().includes(normalize(query)) ||
          normalize(member.rank?.name).toLowerCase().includes(normalize(query))
      );
    },
  },
};
</script>

<style scoped>
/* Table styling */
table {
  border-spacing: 0;
}

th,
td {
  border-bottom: 2px solid #b07d46;
}

/* Optional row highlight */
tbody tr:hover {
  background-color: #f3d9b1;
}
</style>
