<template>
  <div
    v-if="showModalMember"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4 md:p-0"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="w-full max-w-[800px] bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg relative max-h-[90vh] overflow-hidden flex flex-col"
    >
      <!-- Close Button -->
      <button
        class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-xl h-8 w-8 flex items-center justify-center rounded-full hover:bg-[#f3d9b1] transition-colors z-20"
        @click="closeModal"
        aria-label="Fermer"
      >
        &times;
      </button>

      <!-- Tabs -->
      <div class="flex sticky top-0 bg-[#fff5e6] z-10 border-b-2 border-[#b07d46]">
        <button
          :class="[
            'flex-1 py-3 text-base md:text-lg font-bold transition-colors',
            activeTab === 'character'
              ? 'bg-[#b02e2e] text-[#f3d9b1]'
              : 'bg-[#f3d9b1] text-[#b07d46] hover:bg-[#e3c9a1]',
          ]"
          @click="activeTab = 'character'"
        >
          Personnage principal
        </button>
        <button
          :class="[
            'flex-1 py-3 text-base md:text-lg font-bold transition-colors',
            activeTab === 'muleCharacter'
              ? 'bg-[#b02e2e] text-[#f3d9b1]'
              : 'bg-[#f3d9b1] text-[#b07d46] hover:bg-[#e3c9a1]',
          ]"
          @click="activeTab = 'muleCharacter'"
        >
          Mule
        </button>
      </div>

      <!-- Scrollable Content Area -->
      <div class="p-4 md:p-6 overflow-y-auto">
        <!-- Character Form -->
        <form v-if="activeTab === 'character'" @submit.prevent="submitCharacter()">
          <h2 class="text-xl md:text-2xl font-bold text-[#b02e2e] mb-4 md:mb-6">
            Personnage Principal
          </h2>

          <div class="space-y-4 md:space-y-6">
            <!-- Pseudo & Ankama Pseudo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Pseudo -->
              <div>
                <label
                  for="mainPseudo"
                  class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
                >
                  Pseudo <span class="text-[#b02e2e]">*</span>
                </label>
                <input
                  type="text"
                  id="mainPseudo"
                  v-model="character.pseudo"
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
                  required
                  :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isPseudoInvalid }"
                />
                <div
                  v-if="isPseudoInvalid"
                  class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
                >
                  <span class="mr-1">⚠️</span> "{{ character.pseudo }}" est blacklist d'Erebor.
                </div>
              </div>

              <!-- Pseudo Ankama -->
              <div>
                <label
                  for="ankamaPseudo"
                  class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
                >
                  Pseudo Ankama <span class="text-[#b02e2e]">*</span>
                </label>
                <input
                  type="text"
                  id="ankamaPseudo"
                  v-model="character.ankamaPseudo"
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
                  required
                  :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isAnkamaPseudoInvalid }"
                />
                <div
                  v-if="isAnkamaPseudoInvalid"
                  class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
                >
                  <span class="mr-1">⚠️</span> "{{ character.ankamaPseudo }}" est blacklist
                  d'Erebor.
                </div>
              </div>
            </div>

            <!-- Date & Recruteur -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Date -->
              <div>
                <label
                  for="recruitedAt"
                  class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
                >
                  Date de recrutement <span class="text-[#b02e2e]">*</span>
                </label>
                <input
                  type="date"
                  id="recruitedAt"
                  name="recruitedAt"
                  v-model="character.recruitedAt"
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
                  required
                />
              </div>

              <!-- Recruteur -->
              <div>
                <label for="recruiter" class="block text-base font-medium text-[#b07d46] mb-1">
                  Recruteur :
                </label>

                <!-- Si sélectionné -->
                <div v-if="character.recruiterId" class="flex items-center gap-3">
                  <img :src="selectedRecruiterIcon" alt="Classe" class="w-10 h-10 rounded-full" />
                  <span class="text-base font-semibold text-[#b07d46]">{{
                    selectedRecruiterName
                  }}</span>
                  <button
                    type="button"
                    @click="clearSelectedRecruiter"
                    class="text-red-500 text-lg font-bold focus:outline-none"
                  >
                    &times;
                  </button>
                </div>

                <!-- Si non sélectionné -->
                <div v-else>
                  <input
                    type="text"
                    v-model="recruiterSearchQuery"
                    placeholder="Rechercher un recruteur..."
                    class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
                  />
                  <ul
                    class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
                  >
                    <li
                      v-for="recruiter in filteredRecruiters"
                      :key="recruiter.id"
                      @click="selectRecruiter(recruiter)"
                      class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
                    >
                      <img :src="classes[recruiter.class]" alt="Classe" class="w-7 h-7" />
                      <span class="text-base text-[#b07d46]">{{ recruiter.pseudo }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- Classe (responsive grid) -->
            <div>
              <label class="block text-sm md:text-base font-medium text-[#b07d46] mb-2">
                Classe du personnage <span class="text-[#b02e2e]">*</span>
              </label>
              <div
                class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 gap-2 md:gap-3"
              >
                <div
                  v-for="(icon, index) in classes"
                  :key="index"
                  :class="[
                    'cursor-pointer border-2 rounded-md flex items-center justify-center aspect-square transition-all',
                    character.class === index
                      ? 'border-[#b02e2e] bg-[#f3d9b1] scale-110 shadow-md'
                      : 'border-[#b07d46] bg-[#fffaf0] hover:bg-[#f3d9b1] hover:border-[#b02e2e]',
                  ]"
                  @click="((character.class = index), selectClass(index))"
                >
                  <img :src="icon" alt="Classe" class="w-full h-full p-1 md:p-2 object-contain" />
                </div>
              </div>
            </div>
          </div>

          <!-- Erreur globale -->
          <div
            v-if="errorMessage"
            class="mt-4 p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
          >
            <div class="flex items-center">
              <span class="mr-2 text-xl">⚠️</span>
              <span class="font-medium">{{ errorMessage }}</span>
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-6">
            <button
              type="submit"
              class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1] transition-colors flex items-center justify-center"
              :disabled="isSubmitting || !character.class"
              :class="{ 'opacity-70 cursor-not-allowed': isSubmitting || !character.class }"
            >
              <span v-if="isSubmitting" class="mr-2">
                <svg
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
              </span>
              Importer un personnage principal
            </button>
          </div>
        </form>

        <!-- Mule Form -->
        <form v-else @submit.prevent="submitMuleCharacter">
          <h2 class="text-xl md:text-2xl font-bold text-[#b02e2e] mb-4 md:mb-6">Mule</h2>

          <div class="space-y-4 md:space-y-6">
            <!-- Pseudo & Ankama Pseudo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Pseudo -->
              <div>
                <label
                  for="mulePseudo"
                  class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
                >
                  Pseudo <span class="text-[#b02e2e]">*</span>
                </label>
                <input
                  type="text"
                  id="mulePseudo"
                  v-model="muleCharacter.pseudo"
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
                  required
                  :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isMulePseudoInvalid }"
                />
                <div
                  v-if="isMulePseudoInvalid"
                  class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
                >
                  <span class="mr-1">⚠️</span> "{{ muleCharacter.pseudo }}" est blacklist d'Erebor.
                </div>
              </div>

              <!-- Pseudo Ankama -->
              <div>
                <label
                  for="muleAnkamaPseudo"
                  class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
                >
                  Pseudo Ankama <span class="text-[#b02e2e]">*</span>
                </label>
                <input
                  type="text"
                  id="muleAnkamaPseudo"
                  v-model="muleCharacter.ankamaPseudo"
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
                  required
                  :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isMuleAnkamaPseudoInvalid }"
                />
                <div
                  v-if="isMuleAnkamaPseudoInvalid"
                  class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
                >
                  <span class="mr-1">⚠️</span> "{{ muleCharacter.ankamaPseudo }}" est blacklist
                  d'Erebor.
                </div>
              </div>
            </div>

            <!-- Classe (responsive grid) -->
            <div>
              <label class="block text-sm md:text-base font-medium text-[#b07d46] mb-2">
                Classe du personnage <span class="text-[#b02e2e]">*</span>
              </label>
              <div
                class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 gap-2 md:gap-3"
              >
                <div
                  v-for="(icon, index) in classes"
                  :key="index"
                  :class="[
                    'cursor-pointer border-2 rounded-md flex items-center justify-center aspect-square transition-all',
                    muleCharacter.class === index
                      ? 'border-[#b02e2e] bg-[#f3d9b1] scale-110 shadow-md'
                      : 'border-[#b07d46] bg-[#fffaf0] hover:bg-[#f3d9b1] hover:border-[#b02e2e]',
                  ]"
                  @click="((muleCharacter.class = index), selectClass(index))"
                >
                  <img :src="icon" alt="Classe" class="w-full h-full p-1 md:p-2 object-contain" />
                </div>
              </div>
            </div>

            <!-- Sélection du personnage principal -->
            <div class="md:col-span-2">
              <label class="block text-base font-medium text-[#b07d46] mb-2">
                Personnage Principal :
              </label>

              <!-- Si non sélectionné -->
              <div v-if="!muleCharacter.linkedCharacterId">
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Rechercher un personnage..."
                  class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
                />
                <ul
                  class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
                >
                  <li
                    v-for="character in filteredNotArchivedCharacters"
                    :key="character.id"
                    @click="selectNotArchivedCharacter(character)"
                    class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
                  >
                    <img :src="classes[character.class]" alt="Classe" class="w-7 h-7" />
                    <span class="text-base text-[#b07d46]">{{ character.pseudo }}</span>
                  </li>
                </ul>
              </div>

              <!-- Si sélectionné -->
              <div v-else class="flex items-center gap-3">
                <img :src="selectedCharacterIcon" alt="Classe" class="w-10 h-10 rounded-full" />
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
            </div>
          </div>

          <!-- Erreur -->
          <div
            v-if="errorMessageMule"
            class="mt-4 p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
          >
            <div class="flex items-center">
              <span class="mr-2 text-xl">⚠️</span>
              <span class="font-medium">{{ errorMessageMule }}</span>
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-6">
            <button
              type="submit"
              class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1] transition-colors flex items-center justify-center"
              :disabled="
                isSubmittingMule || !muleCharacter.class || !muleCharacter.linkedCharacterId
              "
              :class="{
                'opacity-70 cursor-not-allowed':
                  isSubmittingMule || !muleCharacter.class || !muleCharacter.linkedCharacterId,
              }"
            >
              <span v-if="isSubmittingMule" class="mr-2">
                <svg
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
              </span>
              Importer une mule
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import register_bg from '@/assets/register_bg.webp';

const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
const API_URL = import.meta.env.VITE_API_URL;

export default {
  props: {
    showModalMember: {
      type: Boolean,
      required: true,
    },
    fetchNotArchivedCharacters: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      register_bg,
      errorMessage: '',
      errorMessageMule: '',
      selectedClass: null,
      selectedClassMule: null,
      activeTab: 'character',
      isSubmitting: false,
      isSubmittingMule: false,
      character: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null,
      },
      recruiters: [],
      recruiterSearchQuery: '',
      selectedRecruiterName: '',
      selectedRecruiterIcon: '',
      muleCharacter: {
        pseudo: '',
        ankamaPseudo: '',
        linkedCharacterId: null,
        class: null,
      },
      showNotification: false,
      notArchivedCharacters: [],
      searchQuery: '',
      selectedCharacterName: '',
      selectedCharacterIcon: '',
      showCharacterSelection: false,
      blacklist: [],
      classes: {
        sram: images['/src/assets/icon_classe/sram.avif'].default,
        forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
        cra: images['/src/assets/icon_classe/cra.avif'].default,
        ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
        eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
        enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
        feca: images['/src/assets/icon_classe/feca.avif'].default,
        eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
        iop: images['/src/assets/icon_classe/iop.avif'].default,
        osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
        pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
        roublard: images['/src/assets/icon_classe/roublard.avif'].default,
        sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
        sadida: images['/src/assets/icon_classe/sadida.avif'].default,
        steamer: images['/src/assets/icon_classe/steamer.avif'].default,
        xelor: images['/src/assets/icon_classe/xelor.avif'].default,
        zobal: images['/src/assets/icon_classe/zobal.avif'].default,
        huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
        ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
      },
    };
  },
  methods: {
    isPseudoValid(pseudo) {
      return (
        pseudo && !this.blacklist.some(entry => entry.pseudo.toLowerCase() === pseudo.toLowerCase())
      );
    },
    isAnkamaPseudoValid(ankamaPseudo) {
      return (
        ankamaPseudo &&
        !this.blacklist.some(
          entry => entry.ankamaPseudo.toLowerCase() === ankamaPseudo.toLowerCase()
        )
      );
    },

    resetForm() {
      // Réinitialiser les champs du formulaire principal
      this.character = {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null,
      };

      // Réinitialiser les champs du formulaire mule
      this.muleCharacter = {
        pseudo: '',
        ankamaPseudo: '',
        linkedCharacterId: null,
        class: null,
      };

      // Réinitialiser les messages d'erreur
      this.errorMessage = '';
      this.errorMessageMule = '';

      // Réinitialiser les états de sélection
      this.selectedRecruiterName = '';
      this.selectedRecruiterIcon = '';
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
      this.recruiterSearchQuery = '';
      this.searchQuery = '';
    },

    async submitCharacter() {
      // Validation
      if (!this.character.class) {
        this.errorMessage = 'Veuillez sélectionner une classe avant de soumettre.';
        return;
      }
      if (!this.isPseudoValid(this.character.pseudo)) {
        this.errorMessage = `"${this.character.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      if (!this.isAnkamaPseudoValid(this.character.ankamaPseudo)) {
        this.errorMessage = `"${this.character.ankamaPseudo}" est blacklist d'Erebor.`;
        return;
      }

      try {
        this.isSubmitting = true;
        // API POST request
        const response = await axios.post(`${API_URL}/characters`, {
          pseudo: this.character.pseudo,
          ankamaPseudo: this.character.ankamaPseudo,
          class: this.character.class,
          recruitedAt: this.character.recruitedAt,
          isArchived: this.character.isArchived,
          recruiterId: this.character.recruiterId,
        });

        // Emit the event to inform the parent about the new character
        this.$emit('characterAdded', response.data);

        // Réinitialiser le formulaire après succès
        this.resetForm();
        this.closeModal();
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        this.errorMessage = 'Une erreur est survenue lors de la création du personnage.';
      } finally {
        this.isSubmitting = false;
      }
    },

    async submitCharacter() {
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

      if (!this.isAnkamaPseudoValid(this.character.ankamaPseudo)) {
        this.errorMessage = `"${this.character.ankamaPseudo}" est blacklist d'Erebor.`;
        return;
      }
      const payload = { ...this.character };
      console.log('Données Personnage Principal:', payload);
      try {
        // API POST request
        const response = await axios.post(`${API_URL}/characters`, {
          pseudo: this.character.pseudo,
          ankamaPseudo: this.character.ankamaPseudo,
          class: this.character.class,
          recruitedAt: this.character.recruitedAt,
          isArchived: this.character.isArchived,
          recruiterId: this.character.recruiterId,
        });
        // Emit the event to inform the parent about the new character
        this.$emit('characterAdded', response.data);
        // Réinitialiser le formulaire après succès
        this.resetForm();
        this.closeModal(); // Optionnel : Fermer la modal si nécessaire
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        console.log('An error occurred while creating the character.');
      }
    },

    async submitMuleCharacter() {
      // Validation de base
      if (!this.muleCharacter.class) {
        this.errorMessageMule = 'Veuillez sélectionner une classe avant de soumettre.';
        return;
      }
      if (!this.isPseudoValid(this.muleCharacter.pseudo)) {
        this.errorMessageMule = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
        return;
      }

      if (!this.isAnkamaPseudoValid(this.muleCharacter.ankamaPseudo)) {
        this.errorMessageMule = `"${this.muleCharacter.ankamaPseudo}" est blacklist d'Erebor.`;
        return;
      }
      if (!this.muleCharacter.linkedCharacterId) {
        this.errorMessageMule = 'Veuillez sélectionner un personnage principal.';
        return;
      }

      // Appel à la méthode pour poster la mule
      try {
        const response = await axios.post(`${API_URL}/mules`, {
          pseudo: this.muleCharacter.pseudo,
          ankamaPseudo: this.muleCharacter.ankamaPseudo,
          class: this.muleCharacter.class,
          mainCharacterId: this.muleCharacter.linkedCharacterId,
        });
        // Réinitialiser le formulaire après succès
        this.muleCharacter = {
          pseudo: '',
          ankamaPseudo: '',
          class: null,
          linkedCharacterId: null,
        };
        this.selectedCharacterName = '';
        this.selectedCharacterIcon = '';
        this.$emit('muleAdded', response.data);

        this.resetForm();
        this.closeModal(); // Optionnel : Fermer la modal si nécessaire
        return response.data; // Retour des données en cas de succès
      } catch (error) {
        console.error('Error creating mule character:', error.response?.data || error.message);
        console.log('An error occurred while creating the mule character.');
        throw error; // Propagation de l'erreur pour une gestion supplémentaire
      }
    },
    selectClass(icon) {
      this.selectedClass = icon;
    },
    selectClassMule(icon) {
      this.selectedClassMule = icon;
    },
    closeModal() {
      this.$emit('close');
    },
    async fetchRecruiters() {
      try {
        const response = await axios.get(`${API_URL}/characters/recruiters`); // Replace with your actual API endpoint
        console.log('recruiters', response.data);
        this.recruiters = response.data; // Assign the response to the characters array
      } catch (error) {
        console.error(
          'Error fetching characters with recruiters:',
          error.response?.data || error.message
        );
        console.log('An error occurred while fetching characters with recruiters.');
      }
    },
    // async loadNotArchivedCharacters() {
    //   try {
    //     this.notArchivedCharacters = this.fetchNotArchivedCharacters;
    //   } catch (error) {
    //     console.error('Error loading not archived characters:', error.message);
    //   }
    // },
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
    async fetchBlacklist() {
      try {
        const response = await axios.get(`${API_URL}/blacklist`);
        this.blacklist = response.data;
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
  },

  computed: {
    // Filter characters based on search query
    filteredNotArchivedCharacters() {
      if (!this.searchQuery) return this.fetchNotArchivedCharacters;
      const query = this.searchQuery.toLowerCase();
      return this.fetchNotArchivedCharacters.filter(character =>
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
    isPseudoInvalid() {
      // Ensure blacklist is checked for a specific property (e.g., `pseudo`)
      return (
        this.character.pseudo &&
        this.blacklist.some(
          entry => entry.pseudo.toLowerCase() === this.character.pseudo.toLowerCase()
        )
      );
    },
    isAnkamaPseudoInvalid() {
      return (
        this.character.ankamaPseudo &&
        this.blacklist.some(
          entry => entry.ankamaPseudo.toLowerCase() === this.character.ankamaPseudo.toLowerCase()
        )
      );
    },
    isMulePseudoInvalid() {
      return (
        this.muleCharacter.pseudo &&
        this.blacklist.some(
          entry => entry.pseudo.toLowerCase() === this.muleCharacter.pseudo.toLowerCase()
        )
      );
    },
    isMuleAnkamaPseudoInvalid() {
      return (
        this.muleCharacter.ankamaPseudo &&
        this.blacklist.some(
          entry =>
            entry.ankamaPseudo.toLowerCase() === this.muleCharacter.ankamaPseudo.toLowerCase()
        )
      );
    },
  },

  mounted() {
    this.fetchRecruiters(); // Fetch characters when the component is mounted
    // this.loadNotArchivedCharacters();
    this.fetchBlacklist();
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
