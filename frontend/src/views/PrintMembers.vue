<template>
  <div
    class="min-h-screen w-full flex flex-col items-center justify-center bg-cover bg-center"
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
              <th class="p-4"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(member, index) in charactersNotArchived"
              :key="index"
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
              <td class="p-4 text-[#b07d46] font-bold">{{ member.pseudo }}</td>
              <!-- Recruteur -->
              <td class="p-4 text-[#b07d46]">{{ member.recruiter.pseudo }}</td>
              <!-- Rang -->
              <td class="p-4 text-[#b07d46]">{{ member.rank.name }}</td>
              <!-- Date -->
              <td class="p-4 text-[#b07d46]">{{ member.createdAt }}</td>
              <!-- Options -->
              <td class="p-4 text-right">
                <div
                  class="hidden group-hover:block text-[#b02e2e] cursor-pointer"
                  title="Options"
                  @click="openModal(member)"
                >
                  &#x22EE;
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal archive -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeModal"
        >
          &times;
        </button>

        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Options</h2>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ selectedMember.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="archiveMember"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>

    <!-- Top-right Notification -->
    <div
      v-if="showNotification"
      class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg shadow-lg z-50"
    >
      Joueur archivé
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
    };
  },
  computed: {
  },
  methods: {
    async fetchNotArchivedCharacters() {
      try {
        const response = await axios.get('http://localhost:8000/characters');
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
    archiveMember() {
      if (this.selectedMember) {
        this.selectedMember.archived = true;
        this.showModal = false;
        this.showNotification = true;
        // Automatically hide the notification after 3 seconds
        setTimeout(() => {
          this.showNotification = false;
        }, 3000);
      }
    },
    mounted() {
      this.fetchAllCharacters();
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
