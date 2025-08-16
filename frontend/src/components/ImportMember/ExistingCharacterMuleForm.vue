<template>
  <form @submit.prevent="submitMuleCharacter">
    <div class="flex items-center space-x-3 mb-6">
      <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-amber-400">Ajouter une Mule</h2>
    </div>

    <div class="space-y-6">
      <!-- Pseudo & Ankama Pseudo -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pseudo -->
        <div class="space-y-2">
          <label for="mulePseudo" class="block text-sm font-medium text-gray-300">
            Pseudo <span class="text-red-400">*</span>
          </label>
          <input
            type="text"
            id="mulePseudo"
            v-model="muleCharacter.pseudo"
            class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
            placeholder="Entrez le pseudo de la mule"
            required
            :class="{ 'border-red-500 bg-red-900/20': isMulePseudoInvalid }"
          />
          <div
            v-if="isMulePseudoInvalid"
            class="flex items-center space-x-2 text-red-400 text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ muleCharacter.pseudo }}" est blacklist d'Erebor.</span>
          </div>
        </div>

        <!-- Pseudo Ankama -->
        <div class="space-y-2">
          <label for="muleAnkamaPseudo" class="block text-sm font-medium text-gray-300">
            Pseudo Ankama <span class="text-red-400">*</span>
          </label>
          <input
            type="text"
            id="muleAnkamaPseudo"
            v-model="muleCharacter.ankamaPseudo"
            class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
            placeholder="Entrez le pseudo Ankama"
            required
            :class="{ 'border-red-500 bg-red-900/20': isMuleAnkamaPseudoInvalid }"
          />
          <div
            v-if="isMuleAnkamaPseudoInvalid"
            class="flex items-center space-x-2 text-red-400 text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ muleCharacter.ankamaPseudo }}" est blacklist d'Erebor.</span>
          </div>
        </div>
      </div>

      <!-- Classe Selection -->
      <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-300">
          Classe de la mule <span class="text-red-400">*</span>
        </label>
        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 gap-3">
          <button
            v-for="(icon, className) in classes"
            :key="className"
            type="button"
            @click="selectClass(className)"
            class="group relative p-3 rounded-xl border-2 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-800"
            :class="[
              muleCharacter.class === className
                ? 'border-amber-500 bg-amber-500/20 shadow-lg shadow-amber-500/25'
                : 'border-gray-600 hover:border-amber-400 bg-gray-700 hover:bg-gray-600'
            ]"
          >
            <img
              :src="icon"
              :alt="className"
              class="w-12 h-12 rounded-lg mx-auto mb-2 group-hover:scale-110 transition-transform duration-200"
            />
            <span class="block text-xs font-medium text-center capitalize transition-colors duration-200"
                  :class="[
                    muleCharacter.class === className
                      ? 'text-amber-400'
                      : 'text-gray-400 group-hover:text-gray-300'
                  ]">
              {{ className }}
            </span>
            
            <!-- Selection indicator -->
            <div v-if="muleCharacter.class === className"
                 class="absolute -top-1 -right-1 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center shadow-lg">
              <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </button>
        </div>
      </div>

      <!-- Sélection du personnage principal -->
      <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-300">
          Personnage Principal <span class="text-red-400">*</span>
        </label>
        
        <!-- Si non sélectionné -->
        <div v-if="!muleCharacter.linkedCharacterId" class="space-y-3">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Rechercher un personnage..."
            class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
          />
          <div class="max-h-48 overflow-y-auto bg-gray-800 border-2 border-gray-600 rounded-lg p-3">
            <div
              v-for="character in filteredNotArchivedCharacters"
              :key="character.id"
              @click="selectNotArchivedCharacter(character)"
              class="flex items-center space-x-3 p-3 cursor-pointer hover:bg-gray-700 rounded-lg transition-colors duration-200 group"
            >
              <img :src="classes[character.class]" :alt="`Classe ${character.class}`" class="w-8 h-8 rounded-lg border-2 border-gray-600 group-hover:border-amber-500 transition-colors duration-200" />
              <span class="text-base text-gray-300 group-hover:text-amber-400 transition-colors duration-200">{{ character.pseudo }}</span>
            </div>
          </div>
        </div>
        
        <!-- Si sélectionné -->
        <div v-else class="flex items-center space-x-4 p-4 bg-gray-800 rounded-lg border border-gray-600">
          <img :src="classes[selectedCharacter?.class]" :alt="`Classe ${selectedCharacter?.class}`" class="w-10 h-10 rounded-lg border-2 border-gray-600" />
          <div class="flex-1">
            <span class="text-lg font-semibold text-amber-400">{{ selectedCharacter?.pseudo }}</span>
            <span class="block text-sm text-gray-400">({{ selectedCharacter?.ankamaPseudo }})</span>
          </div>
          <button
            type="button"
            @click="clearLinkedCharacter"
            class="w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-105"
            title="Changer de personnage"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Error message -->
      <div
        v-if="errorMessage"
        class="bg-red-900/50 border border-red-700 rounded-xl p-4 text-red-300"
      >
        <div class="flex items-center space-x-3">
          <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="font-medium">{{ errorMessage }}</span>
        </div>
      </div>
      
      <!-- Submit Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          class="px-6 py-3 bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 hover:to-yellow-700 text-black font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          :disabled="!canSubmitMule"
        >
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Ajouter la mule</span>
          </div>
        </button>
      </div>
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
    },
    selectedCharacterForMule: {
      type: Object,
      required: false,
      default: null
    }
  },
  data() {
    return {
      errorMessage: '',
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
    // Dans ExistingCharacterMuleForm.vue
  mounted() {
    if (this.selectedCharacterForMule) {
      this.selectNotArchivedCharacter(this.selectedCharacterForMule);
    }
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
    },
    canSubmitMule() {
      return this.muleCharacter.class && this.muleCharacter.linkedCharacterId;
    },
    selectedCharacter() {
      return this.fetchNotArchivedCharacters.find(
        char => char.id === this.muleCharacter.linkedCharacterId
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
    
    clearLinkedCharacter() {
      this.muleCharacter.linkedCharacterId = null;
      this.selectedCharacterName = '';
      this.selectedCharacterIcon = '';
    },
    
    validateForm() {
      if (!this.muleCharacter.class) {
        this.errorMessage = 'Veuillez sélectionner une classe pour la mule.';
        return false;
      }
      
      if (!this.muleCharacter.pseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo pour la mule.';
        return false;
      }
      
      if (!this.muleCharacter.ankamaPseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo Ankama pour la mule.';
        return false;
      }
      
      if (this.isMulePseudoInvalid) {
        this.errorMessage = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      if (this.isMuleAnkamaPseudoInvalid) {
        this.errorMessage = `"${this.muleCharacter.ankamaPseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      if (!this.muleCharacter.linkedCharacterId) {
        this.errorMessage = 'Veuillez sélectionner un personnage principal.';
        return false;
      }
      
      this.errorMessage = '';
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
        this.errorMessage = 'Une erreur est survenue lors de la création de la mule.';
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
      this.errorMessage = '';
    }
  }
};
</script>
