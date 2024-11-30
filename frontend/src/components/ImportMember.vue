<template>
    <div
      v-if="showModalMember"
      class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <!-- Modal Content -->
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-10/12 max-w-3xl p-4 relative">
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
                : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]'
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
                : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]'
            ]"
            @click="activeTab = 'muleCharacter'"
          >
            Mule
          </button>
        </div>
  
        <!-- Scrollable Content Area -->
        <div class="px-4">
          <!-- Main Character Form -->
          <form v-if="activeTab === 'character'" 
          @submit.prevent="submitCharacter()">
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
            <!-- Pseudo -->
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
              <label class="block text-lg font-medium text-[#b07d46] mb-2">Classe du personnage:</label>
              <div class="grid grid-cols-7 gap-4">
                <div
                  v-for="(icon, index) in classes"
                  :key="index"
                  :class="[
                    'cursor-pointer border-2 rounded-lg p-2',
                    character.class === index
                      ? 'border-[#b02e2e] bg-[#f3d9b1]'
                      : 'border-[#b07d46] bg-[#fffaf0]'
                  ]"
                  @click="character.class = index, selectClass(index)"
                >
                  <img :src="icon" alt="Classe" class="w-full h-auto" />
                </div>
              </div>
            </div>
            <div class="mb-4">
            <label for="mainLevel" class="block text-lg font-medium text-[#b07d46] mb-2">
              Niveau (1-200):
            </label>
            <input
              type="number"
              id="mainLevel"
              v-model.number="character.level"
              min="1"
              max="200"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
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
            <div class="relative">
              <select
                id="recruiter"
                v-model="character.recruiterId"
                class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] appearance-none"
                required
              >
                <option v-for="recruiter in recruiters" :key="recruiter.id" :value="recruiter.id">
                  {{ recruiter.pseudo }}
                </option>
              </select>
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
            <!-- Classe -->
            <div class="mb-4">
              <label class="block text-lg font-medium text-[#b07d46] mb-2">Classe du personnage:</label>
              <div class="grid grid-cols-7 gap-4">
                <div
                  v-for="(icon, index) in classes"
                  :key="index"
                  :class="[
                    'cursor-pointer border-2 rounded-lg p-2',
                    muleCharacter.class === icon
                      ? 'border-[#b02e2e] bg-[#f3d9b1]'
                      : 'border-[#b07d46] bg-[#fffaf0]'
                  ]"
                  @click="muleCharacter.class = icon"
                >
                  <img :src="icon" alt="Classe" class="w-full h-auto" />
                </div>
              </div>
            </div>
            <div class="mb-4">
          <label for="muleLevel" class="block text-lg font-medium text-[#b07d46] mb-2">
            Niveau (1-200):
          </label>
          <input
            type="number"
            id="muleLevel"
            v-model.number="muleCharacter.level"
            min="1"
            max="200"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            required
          />
        </div>
            <!-- Submit -->
            <button
              type="submit"
              @click="closeModal"
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
      required: true
    }
  },
    data() {
      return {
        register_bg,
        errorMessage: "", // Message d'erreur à afficher
        selectedClass: null, // Classe actuellement sélectionnée
        activeTab: "character", // Onglet actif
        users: ["User1", "User2", "User3"], // Liste des utilisateurs pour le menu déroulant
        character: {
          pseudo: '',
          ankamaPseudo: '',
          class: '',
          level: 1,
          recruitedAt: '',
          recruiterId: null,
          isArchived: false,
          userId: null, // Optional user ID
          },
        recruiters: [],
        muleCharacter: {
          pseudo: "",
          linkedUser: "",
          class: null,
          level: 1,
        },
        blacklistList: [
          "toto", "Bard", "Siisko", "Lae", "Shira"
        ],
        classes: {
          sram : "/src/assets/icon_classe/sram.avif",
          forgelance : "/src/assets/icon_classe/forgelance.avif",
          cra : "/src/assets/icon_classe/cra.avif",
          ecaflip : "/src/assets/icon_classe/ecaflip.avif",
          eniripsa : "/src/assets/icon_classe/eniripsa.avif",
          enutrof : "/src/assets/icon_classe/enutrof.avif",
          feca : "/src/assets/icon_classe/feca.avif",
          eliotrope : "/src/assets/icon_classe/eliotrope.avif",
          iop : "/src/assets/icon_classe/iop.avif",
          osamodas : "/src/assets/icon_classe/osamodas.avif",
          pandawa : "/src/assets/icon_classe/pandawa.avif",
          roublard : "/src/assets/icon_classe/roublard.avif",
          sacrieur : "/src/assets/icon_classe/sacrieur.avif",
          sadida : "/src/assets/icon_classe/sadida.avif",
          steamer : "/src/assets/icon_classe/steamer.avif",
          xelor : "/src/assets/icon_classe/xelor.avif",
          zobal : "/src/assets/icon_classe/zobal.avif",
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
          level: 1,
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
          level: 1,
        };

        // Réinitialiser les messages d'erreur
        this.errorMessage = '';
      },

        async submitCharacter() {
          console.log("submit", this.character)
          console.log('Selected recruiterId:', this.character.recruiterId)

          // Close modal after successful submission
          this.closeModal();
          // Envoyer les données du personnage principal
          const selectedDate = new Date(this.recruitedAt); // Get the selected date
          const today = new Date(); // Get today's date

          // Check if the selected date is in the future
          if (selectedDate > today) {
            alert('The creation date cannot be in the future.');
            this.recruitedAt = ''; // Clear the invalid date
            return;
          }
          if (!this.character.class) {
            this.errorMessage = "Veuillez sélectionner une classe avant de soumettre.";
            return;
          }
          if (!this.isPseudoValid(this.character.pseudo)) {
            this.errorMessage = `"${this.character.pseudo}" est blacklist d'Erebor.`;
            return;
          }
          const payload = { ...this.character };
          console.log("Données Personnage Principal:", payload);
          try {
          // API POST request
          const response = await axios.post('http://localhost:8000/characters', {
            pseudo: this.character.pseudo,
            ankamaPseudo: this.character.ankamaPseudo,
            class: this.character.class,
            level: this.character.level,
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
          this.errorMessage = "Veuillez sélectionner une classe avant de soumettre.";
          return;
        }
        if (!this.isPseudoValid(this.muleCharacter.pseudo)) {
          this.errorMessage = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
          return;
        }
        // Envoyer les données du personnage mule
        const payload = { ...this.muleCharacter };
        console.log("Données Personnage Mule:", payload);
        // Ajoutez ici une requête POST vers Symfony avec fetch ou axios
      },
      selectClass(icon) {
        this.selectedClass = icon;
      },
      selectClassMule(icon) {
        this.selectedClass = icon;
      },
      closeModal() {
      this.$emit("close");
    },
    async fetchRecruiters() {
      try {
        const response = await axios.get('http://localhost:8000/characters/recruiters'); // Replace with your actual API endpoint
        console.log("recruiters", response.data);
        this.recruiters = response.data; // Assign the response to the characters array
      } catch (error) {
        console.error('Error fetching characters with recruiters:', error.response?.data || error.message);
        alert('An error occurred while fetching characters with recruiters.');
      }
    },
    },
    mounted() {
      this.fetchRecruiters(); // Fetch characters when the component is mounted
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
            level: 1,
          };
        }
      },
    }
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
