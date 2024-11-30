<template>
  <div
    v-if="showModalMember"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-10/12 max-w-3xl p-4 relative"
    >
      <!-- Close Button -->
      <button
        class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
        @click="closeModal"
      >
        &times;
      </button>

      <!-- Tabs -->
      <div class="flex mb-4 sticky top-0 bg-[#fff5e6] z-10">
        <button
          :class="[
            'flex-1 py-2 text-lg font-bold border-b-4',
            activeTab === 'character'
              ? 'bg-[#b02e2e] text-[#f3d9b1] border-[#f3d9b1]'
              : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]',
          ]"
          @click="activeTab = 'character'"
        >
          Personnage principal
        </button>
        <button
          :class="[
            'flex-1 py-2 text-lg font-bold border-b-4',
            activeTab === 'muleCharacter'
              ? 'bg-[#b02e2e] text-[#f3d9b1] border-[#f3d9b1]'
              : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]',
          ]"
          @click="activeTab = 'muleCharacter'"
        >
          Mule
        </button>
      </div>

      <!-- Scrollable Content Area -->
      <div class="px-4">
        <!-- Main Character Form -->
        <form v-if="activeTab === 'character'" @submit.prevent="submitCharacter()">
          <h2 class="text-2xl font-bold text-[#b02e2e] mb-4">Personnage Principal</h2>
          <!-- Pseudo -->
          <div class="mb-4">
            <label for="mainPseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
              Pseudo:
            </label>
            <input
              type="text"
              id="mainPseudo"
              v-model="character.pseudo"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>
          <!-- Pseudo ankama -->
          <div class="mb-4">
            <label for="mainPseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
              Pseudo Ankama:
            </label>
            <input
              type="text"
              id="ankamaPseudo"
              v-model="character.ankamaPseudo"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>
          <!-- Classe -->
          <div class="mb-4">
            <label class="block text-lg font-medium text-[#b07d46] mb-2"
              >Classe du personnage:</label
            >
            <div class="grid grid-cols-7 gap-4">
              <div
                v-for="(icon, index) in classes"
                :key="index"
                :class="[
                  'cursor-pointer border-2 rounded-lg p-2',
                  character.class === index
                    ? 'border-[#b02e2e] bg-[#f3d9b1]'
                    : 'border-[#b07d46] bg-[#fffaf0]',
                ]"
                @click="((character.class = index), selectClass(index))"
              >
                <img :src="icon" alt="Classe" class="w-full h-auto" />
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label for="mainDate" class="block text-lg font-medium text-[#b07d46] mb-2">
              Date:
            </label>
            <input
              type="date"
              id="recruitedAt"
              name="recruitedAt"
              v-model="character.recruitedAt"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>

          <!-- Sélectionner un recruteur -->
          <div class="mb-4">
            <label for="recruiter" class="block text-lg font-medium text-[#b07d46] mb-2">
              Sélectionner un recruteur:
            </label>

            <!-- If recruiter is selected -->
            <div v-if="character.recruiterId" class="flex items-center gap-4">
              <img :src="selectedRecruiterIcon" alt="Class Icon" class="w-15 h-15 rounded-full" />
              <span class="text-lg font-large text-[#b07d46]">{{ selectedRecruiterName }}</span>
              <button
                type="button"
                @click="clearSelectedRecruiter"
                class="text-red-500 text-lg font-bold focus:outline-none"
              >
                &times;
              </button>
            </div>

            <!-- If recruiter is not selected -->
            <div v-else>
              <input
                type="text"
                v-model="recruiterSearchQuery"
                placeholder="Rechercher un recruteur..."
                class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              />
              <ul
                class="max-h-20 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-lg p-2"
              >
                <li
                  v-for="recruiter in filteredRecruiters"
                  :key="recruiter.id"
                  @click="selectRecruiter(recruiter)"
                  class="flex items-center gap-4 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-lg"
                >
                  <img :src="classes[recruiter.class]" alt="Classe" class="w-8 h-8" />
                  <span class="text-lg font-medium text-[#b07d46]">{{ recruiter.pseudo }}</span>
                </li>
              </ul>
            </div>
          </div>
          <div v-if="errorMessage" class="text-1xl font-bold text-[#b02e2e] mb-6">
            {{ errorMessage }}
          </div>
          <!-- Submit -->
          <button
            type="submit"
            class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
            :disabled="!character.class"
          >
            Importer un personnage principal
          </button>
        </form>

        <!-- Mule Character Form -->
        <form v-else @submit.prevent="submitMuleCharacter">
          <h2 class="text-2xl font-bold text-[#b02e2e] mb-4">Mule</h2>
          <!-- Pseudo -->
          <div class="mb-4">
            <label for="mulePseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
              Pseudo:
            </label>
            <input
              type="text"
              id="mulePseudo"
              v-model="muleCharacter.pseudo"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>
          <!-- Pseudo ankama -->
          <div class="mb-4">
            <label for="mainPseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
              Pseudo Ankama
            </label>
            <input
              type="text"
              id="ankamaPseudo"
              v-model="muleCharacter.ankamaPseudo"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>
          <div class="mb-4">
            <label class="block text-lg font-medium text-[#b07d46] mb-2">
              Classe du personnage
            </label>
            <div class="grid grid-cols-7 gap-4">
              <div
                v-for="(icon, index) in classes"
                :key="index"
                :class="[
                  'cursor-pointer border-2 rounded-lg p-2',
                  muleCharacter.class === icon
                    ? 'border-[#b02e2e] bg-[#f3d9b1]'
                    : 'border-[#b07d46] bg-[#fffaf0]',
                ]"
                @click="muleCharacter.class = icon"
              >
                <img :src="icon" alt="Classe" class="w-full h-auto" />
              </div>
            </div>
          </div>

          <!-- Select Character with Search and Limited List -->
          <div class="mb-4">
            <label for="selectedCharacter" class="block text-lg font-medium text-[#b07d46] mb-2">
              Personnage Principal :
            </label>
            <input
              v-if="!muleCharacter.linkedCharacterId"
              type="text"
              v-model="searchQuery"
              placeholder="Rechercher un personnage..."
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            />
            <ul
              v-if="!muleCharacter.linkedCharacterId"
              class="max-h-20 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-lg p-2"
            >
              <li
                v-for="character in filteredNotArchivedCharacters"
                :key="character.id"
                @click="selectNotArchivedCharacter(character)"
                class="flex items-center gap-4 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-lg"
              >
                <img :src="classes[character.class]" alt="Classe" class="w-8 h-8" />
                <span class="text-lg font-medium text-[#b07d46]">{{ character.pseudo }}</span>
              </li>
            </ul>
          </div>

          <!-- Display Selected Character -->
          <div v-if="muleCharacter.linkedCharacterId" class="mb-4">
            <div class="flex items-center gap-4">
              <img :src="selectedCharacterIcon" alt="Class Icon" class="w-15 h-15 rounded-full" />
              <span class="text-lg font-large text-[#b07d46]">{{ selectedCharacterName }}</span>
              <button
                type="button"
                @click="clearSelectedCharacter"
                class="text-red-500 text-lg font-bold focus:outline-none"
              >
                &times;
              </button>
            </div>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
          >
            Importer une mule
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import register_bg from '@/assets/register_bg.webp';

export default {
  props: {
    showModalMember: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      register_bg,
      errorMessage: '', // Message d'erreur à afficher
      selectedClass: null, // Classe actuellement sélectionnée
      activeTab: 'character', // Onglet actif
      character: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null, // Optional user ID
      },
      recruiters: [],
      recruiterSearchQuery: '', // Query for the recruiter search bar
      selectedRecruiterName: '', // Name of the selected recruiter
      selectedRecruiterIcon: '', // Icon of the selected recruiter
      muleCharacter: {
        pseudo: '',
        linkedCharacterId: null, // The selected not archived character's ID
        class: null,
      },
      notArchivedCharacters: [], // List of not archived characters
      searchQuery: '', // Query for the search bar
      selectedCharacterName: '',
      selectedCharacterIcon: '', // Display the icon of the selected character
      showCharacterSelection: false, // Toggle visibility of character selection
      blacklistList: ['toto', 'Bard', 'Siisko', 'Lae', 'Shira'],
      classes: {
        sram: '/src/assets/icon_classe/sram.avif',
        forgelance: '/src/assets/icon_classe/forgelance.avif',
        cra: '/src/assets/icon_classe/cra.avif',
        ecaflip: '/src/assets/icon_classe/ecaflip.avif',
        eniripsa: '/src/assets/icon_classe/eniripsa.avif',
        enutrof: '/src/assets/icon_classe/enutrof.avif',
        feca: '/src/assets/icon_classe/feca.avif',
        eliotrope: '/src/assets/icon_classe/eliotrope.avif',
        iop: '/src/assets/icon_classe/iop.avif',
        osamodas: '/src/assets/icon_classe/osamodas.avif',
        pandawa: '/src/assets/icon_classe/pandawa.avif',
        roublard: '/src/assets/icon_classe/roublard.avif',
        sacrieur: '/src/assets/icon_classe/sacrieur.avif',
        sadida: '/src/assets/icon_classe/sadida.avif',
        steamer: '/src/assets/icon_classe/steamer.avif',
        xelor: '/src/assets/icon_classe/xelor.avif',
        zobal: '/src/assets/icon_classe/zobal.avif',
        huppermage: '/src/assets/icon_classe/huppermage.avif',
      },
    };
  },
  methods: {
    // Vérifier si le pseudo est valide
    isPseudoValid(pseudo) {
      return !this.blacklistList.includes(pseudo);
    },
    resetForm() {
      // Réinitialiser les champs du formulaire principal
      this.character = {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: '',
        isArchived: false,
        userId: null,
      };

      // Réinitialiser les champs du formulaire mule (si nécessaire)
      this.muleCharacter = {
        pseudo: '',
        linkedUser: '',
        class: null,
      };

      // Réinitialiser les messages d'erreur
      this.errorMessage = '';
    },

    async submitCharacter() {
      console.log('submit', this.character);
      console.log('Selected recruiterId:', this.character.recruiterId);

      // Close modal after successful submission
      this.closeModal();
      if (!this.character.class) {
        this.errorMessage = 'Veuillez sélectionner une classe avant de soumettre.';
        return;
      }
      if (!this.isPseudoValid(this.character.pseudo)) {
        this.errorMessage = `"${this.character.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      const payload = { ...this.character };
      console.log('Données Personnage Principal:', payload);
      try {
        // API POST request
        const response = await axios.post('http://localhost:8000/characters', {
          pseudo: this.character.pseudo,
          ankamaPseudo: this.character.ankamaPseudo,
          class: this.character.class,
          recruitedAt: this.character.recruitedAt,
          isArchived: this.character.isArchived,
          recruiterId: this.character.recruiterId,
        });

        alert('Character created successfully!');
        console.log(response.data);

        // Réinitialiser le formulaire après succès
        this.resetForm();
        this.closeModal(); // Optionnel : Fermer la modal si nécessaire
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        alert('An error occurred while creating the character.');
      }
    },

    submitMuleCharacter() {
      if (!this.muleCharacter.class) {
        this.errorMessage = 'Veuillez sélectionner une classe avant de soumettre.';
        return;
      }
      if (!this.isPseudoValid(this.muleCharacter.pseudo)) {
        this.errorMessage = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      // Envoyer les données du personnage mule
      const payload = { ...this.muleCharacter };
      console.log('Données Personnage Mule:', payload);
      // Ajoutez ici une requête POST vers Symfony avec fetch ou axios
    },
    selectClass(icon) {
      this.selectedClass = icon;
    },
    selectClassMule(icon) {
      this.selectedClass = icon;
    },
    closeModal() {
      this.$emit('close');
    },
    async fetchRecruiters() {
      try {
        const response = await axios.get('http://localhost:8000/characters/recruiters'); // Replace with your actual API endpoint
        console.log('recruiters', response.data);
        this.recruiters = response.data; // Assign the response to the characters array
      } catch (error) {
        console.error(
          'Error fetching characters with recruiters:',
          error.response?.data || error.message
        );
        alert('An error occurred while fetching characters with recruiters.');
      }
    },
    async fetchNotArchivedCharacters() {
      try {
        // Make the API call to fetch all characters
        const response = await axios.get('http://localhost:8000/characters');

        // Filter characters where isArchived is false
        const notArchivedCharacters = response.data.filter(character => !character.isArchived);
        console.log('Not archived characters:', notArchivedCharacters);

        // Assign the filtered characters to a local property (if needed)
        this.notArchivedCharacters = notArchivedCharacters; // or another property
      } catch (error) {
        console.error(
          'Error fetching not archived characters:',
          error.response?.data || error.message
        );
        alert('An error occurred while fetching not archived characters.');
      }
    },
    selectNotArchivedCharacter(character) {
      this.muleCharacter.linkedCharacterId = character.id;
      this.selectedCharacterName = character.pseudo;
      this.selectedCharacterIcon = this.classes[character.class];
      this.showCharacterSelection = false; // Hide the list after selection
      this.searchQuery = ''; // Clear the search bar
    },
    toggleCharacterSelection() {
      this.showCharacterSelection = !this.showCharacterSelection;
    },
    clearSelectedCharacter() {
      // Clear the selected character
      this.muleCharacter.linkedCharacterId = null;
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
      this.searchQuery = ''; // Clear the search query
      this.showCharacterSelection = true; // Reopen the selection list
    },
    selectRecruiter(recruiter) {
      console.log('Selected recruiter:', recruiter);
      this.character.recruiterId = recruiter.id; // Set recruiter ID
      this.selectedRecruiterName = recruiter.pseudo; // Display recruiter name
      this.selectedRecruiterIcon = this.classes[recruiter.class]; // Display recruiter icon
      this.recruiterSearchQuery = ''; // Clear the search bar
    },

    clearSelectedRecruiter() {
      this.character.recruiterId = null; // Clear recruiter ID
      this.selectedRecruiterName = ''; // Clear recruiter name
      this.selectedRecruiterIcon = ''; // Clear recruiter icon
    },
  },

  computed: {
    // Filter characters based on search query
    filteredNotArchivedCharacters() {
      if (!this.searchQuery) return this.notArchivedCharacters;
      const query = this.searchQuery.toLowerCase();
      return this.notArchivedCharacters.filter(character =>
        character.pseudo.toLowerCase().includes(query)
      );
    },
    filteredRecruiters() {
      // Default to an empty array if `recruiters` is undefined
      if (!this.recruiters) return [];
      if (!this.recruiterSearchQuery) return this.recruiters;
      const query = this.recruiterSearchQuery.toLowerCase();
      return this.recruiters.filter(recruiter => recruiter.pseudo.toLowerCase().includes(query));
    },
  },

  mounted() {
    this.fetchRecruiters(); // Fetch characters when the component is mounted
    this.fetchNotArchivedCharacters();
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'character') {
        this.resetForm();
      } else if (newTab === 'muleCharacter') {
        this.muleCharacter = {
          pseudo: '',
          linkedUser: '',
          class: null,
        };
      }
    },
  },
};
</script>

<style>
/* Aucun style additionnel nécessaire grâce à Tailwind CSS */
/* Adjust dropdown styling to show icons alongside text */
option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

option img {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
}
</style>
