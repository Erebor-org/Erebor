<template>
  <form @submit.prevent="submitMuleCharacter">
    <h2 class="text-xl md:text-2xl font-bold text-[#b02e2e] mb-4 md:mb-6">Mule</h2>
    <div class="space-y-4 md:space-y-6">
      <!-- Pseudo & Ankama Pseudo -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pseudo -->
        <div>
          <label for="mulePseudo" class="block text-sm md:text-base font-medium text-[#b07d46] mb-1">
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
          <label for="muleAnkamaPseudo" class="block text-sm md:text-base font-medium text-[#b07d46] mb-1">
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
            <span class="mr-1">⚠️</span> "{{ muleCharacter.ankamaPseudo }}" est blacklist d'Erebor.
          </div>
        </div>
      </div>

      <!-- Classe (responsive grid) -->
      <div>
        <label class="block text-sm md:text-base font-medium text-[#b07d46] mb-2">
          Classe du personnage <span class="text-[#b02e2e]">*</span>
        </label>
        <div class="grid grid-cols-7 sm:grid-cols-9 md:grid-cols-10 lg:grid-cols-12 gap-1">
          <div
            v-for="(icon, index) in classes"
            :key="index"
            :class="[
              'cursor-pointer border-2 rounded-md flex items-center justify-center aspect-square transition-all',
              muleCharacter.class === index
                ? 'border-[#b02e2e] bg-[#f3d9b1] scale-100 shadow-md'
                : 'border-[#b07d46] bg-[#fffaf0] hover:bg-[#f3d9b1] hover:border-[#b02e2e]',
            ]"
            @click="((muleCharacter.class = index), selectClass(index))"
          >
            <img :src="icon" alt="Classe" class="w-20 h-20 object-contain" />
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
          <span class="text-base font-semibold text-[#b07d46]">{{ selectedCharacterName }}</span>
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
        :disabled="isSubmittingMule || !muleCharacter.class || !muleCharacter.linkedCharacterId"
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
</template>

<script>
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  props: {
    classes: {
      type: Object,
      required: true
    },
    blacklist: {
      type: Array,
      required: true
    },
    fetchNotArchivedCharacters: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      errorMessageMule: '',
      isSubmittingMule: false,
      muleCharacter: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        linkedCharacterId: null
      },
      searchQuery: '',
      selectedCharacterName: '',
      selectedCharacterIcon: ''
    };
  },
  computed: {
    filteredNotArchivedCharacters() {
      if (!this.searchQuery) return this.fetchNotArchivedCharacters;
      
      const query = this.searchQuery.toLowerCase();
      return this.fetchNotArchivedCharacters.filter(character => 
        character.pseudo.toLowerCase().includes(query)
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
          entry => entry.ankamaPseudo.toLowerCase() === this.muleCharacter.ankamaPseudo.toLowerCase()
        )
      );
    }
  },
  methods: {
    selectClass(classIndex) {
      this.muleCharacter.class = classIndex;
    },
    
    selectNotArchivedCharacter(character) {
      this.muleCharacter.linkedCharacterId = character.id;
      this.selectedCharacterName = character.pseudo;
      this.selectedCharacterIcon = this.classes[character.class];
      this.searchQuery = '';
    },
    
    clearSelectedCharacter() {
      this.muleCharacter.linkedCharacterId = null;
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
    },
    
    validateForm() {
      if (!this.muleCharacter.class) {
        this.errorMessageMule = 'Veuillez sélectionner une classe pour la mule.';
        return false;
      }
      
      if (!this.muleCharacter.pseudo) {
        this.errorMessageMule = 'Veuillez entrer un pseudo pour la mule.';
        return false;
      }
      
      if (!this.muleCharacter.ankamaPseudo) {
        this.errorMessageMule = 'Veuillez entrer un pseudo Ankama pour la mule.';
        return false;
      }
      
      if (this.isMulePseudoInvalid) {
        this.errorMessageMule = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      if (this.isMuleAnkamaPseudoInvalid) {
        this.errorMessageMule = `"${this.muleCharacter.ankamaPseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      if (!this.muleCharacter.linkedCharacterId) {
        this.errorMessageMule = 'Veuillez sélectionner un personnage principal.';
        return false;
      }
      
      this.errorMessageMule = '';
      return true;
    },
    
    async submitMuleCharacter() {
      if (!this.validateForm()) {
        return;
      }
      
      try {
        this.isSubmittingMule = true;
        
        // Prepare mule data
        const muleData = {
          pseudo: this.muleCharacter.pseudo,
          ankamaPseudo: this.muleCharacter.ankamaPseudo,
          class: this.muleCharacter.class,
          characterId: this.muleCharacter.linkedCharacterId
        };
        
        // API POST request to add mule to existing character
        const response = await axios.post(`${API_URL}/mules`, muleData);
        
        console.log("Réponse de l'API après création de mule:", response.data);
        
        // Emit event for mule added
        this.$emit('mule-added', response.data);
        
        // Reset form
        this.resetForm();
        
        // Close modal
        this.$emit('close');
      } catch (error) {
        console.error('Error creating mule:', error.response?.data || error.message);
        this.errorMessageMule = 'Une erreur est survenue lors de la création de la mule.';
      } finally {
        this.isSubmittingMule = false;
      }
    },
    
    resetForm() {
      this.muleCharacter = {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        linkedCharacterId: null
      };
      this.searchQuery = '';
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
      this.errorMessageMule = '';
    }
  }
};
</script>
