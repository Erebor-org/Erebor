<template>
  <div
    v-if="showModal"
    class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="w-full max-w-7xl bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col"
    >
      <!-- Header with gradient -->
      <div class="bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700 p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-7 h-7 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <div>
              <h2 class="text-3xl font-bold text-amber-400">Import de Personnage</h2>
              <p class="text-gray-400 mt-1">Gérez vos nouveaux membres et mules</p>
            </div>
          </div>
          
          <!-- Close Button -->
          <button
            class="w-10 h-10 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-200 hover:scale-105"
            @click="closeModal"
            aria-label="Fermer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Tabs with modern design -->
      <div class="bg-gray-800 border-b border-gray-700 px-6">
        <div class="flex space-x-1">
          <button 
            @click="activeTab = 'newCharacter'"
            :class="[
              'px-6 py-4 text-sm font-medium rounded-t-xl transition-all duration-200 flex items-center space-x-2',
              activeTab === 'newCharacter' 
                ? 'bg-gray-900 text-amber-400 border-b-2 border-amber-400' 
                : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Nouveau Personnage</span>
          </button>
          <button 
            @click="activeTab = 'addMule'"
            :class="[
              'px-6 py-4 text-sm font-medium rounded-t-xl transition-all duration-200 flex items-center space-x-2',
              activeTab === 'addMule' 
                ? 'bg-gray-900 text-amber-400 border-b-2 border-amber-400' 
                : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span>Ajouter une Mule</span>
          </button>
        </div>
      </div>

      <!-- Scrollable Content Area -->
      <div class="flex-1 overflow-y-auto bg-gray-900">
        <!-- New Character Tab -->
        <div v-if="activeTab === 'newCharacter'" class="p-8">
          <form @submit.prevent="submitForm" class="space-y-8">
            <!-- Main Character Form -->
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
              <MainCharacterForm 
                ref="mainCharacterForm"
                :classes="classes" 
                :blacklist="blacklist"
                @update:character="updateCharacter"
              />
            </div>

            <!-- Mules Section -->
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
              <MulesManager 
                ref="mulesManager"
                :classes="classes" 
                :blacklist="blacklist"
                @update:mules="updateMules"
              />
            </div>

            <!-- Global error message -->
            <div
              v-if="errorMessage"
              class="bg-red-900/50 border border-red-700 rounded-xl p-4 text-red-300"
            >
              <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="font-medium">{{ errorMessage }}</span>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
              <button
                type="button"
                @click="submitForm"
                class="px-8 py-4 bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 hover:to-yellow-700 text-black font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                :disabled="isSubmitting || !character.class"
              >
                <div class="flex items-center space-x-3">
                  <span v-if="isSubmitting" class="animate-spin">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  <span v-else>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </span>
                  <span>{{ isSubmitting ? 'Import en cours...' : `Importer ${mules.length > 0 ? 'le personnage et ses mules' : 'le personnage'}` }}</span>
                </div>
              </button>
            </div>
          </form>
        </div>
        
        <!-- Add Mule to Existing Character Tab -->
        <div v-if="activeTab === 'addMule'" class="p-8">
          <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <ExistingCharacterMuleForm
              ref="existingCharacterMuleForm"
              :classes="classes"
              :blacklist="blacklist"
              :fetchNotArchivedCharacters="fetchNotArchivedCharacters"
              :selectedCharacterForMule="selectedCharacterForMule"
              @mule-added="handleMuleAdded"
              @close="closeModal"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import MainCharacterForm from './MainCharacterForm.vue';
import MulesManager from './MulesManager.vue';
import ExistingCharacterMuleForm from './ExistingCharacterMuleForm.vue';

const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    MainCharacterForm,
    MulesManager,
    ExistingCharacterMuleForm
  },
  props: {
    showModal: {
      type: Boolean,
      required: true,
    },
    fetchNotArchivedCharacters: {
      type: Array,
      required: true,
    },
    selectedCharacterForMule: {
      type: Object,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      activeTab: 'newCharacter',
      errorMessage: '',
      isSubmitting: false,
      character: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null,
      },
      mules: [],
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
  watch: {
    selectedCharacterForMule(newVal) {
      if (newVal) {
        // Si un personnage est pré-sélectionné, ouvrir directement l'onglet ajout mule
        this.activeTab = 'addMule';
      }
    },
    showModal(newVal) {
      if (!newVal) {
        // Quand le modal se ferme, réinitialiser l'onglet actif
        this.activeTab = 'newCharacter';
      }
    },
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    
    handleMuleAdded(muleData) {
      this.$emit('mule-added', muleData);
    },
    
    updateCharacter(characterData) {
      this.character = characterData;
    },
    
    updateMules(mulesData) {
      this.mules = mulesData;
    },
    
    async submitForm() {
      // Validate main character
      if (!this.character.class) {
        this.errorMessage = 'Veuillez sélectionner une classe pour le personnage principal.';
        return;
      }
      
      // Validate main character via ref
      if (!this.$refs.mainCharacterForm.validateForm()) {
        return;
      }

      try {
        this.isSubmitting = true;
        
        // Prepare main character data with mules
        const characterData = { ...this.character };

        // Add mules if there are any
        if (this.mules.length > 0) {
          characterData.mules = this.mules;
        }
        
        // API POST request with character and its mules
        const response = await axios.post(`${API_URL}/characters`, characterData);
        
        console.log("Réponse de l'API après création:", response.data);

        // Emit the event to inform the parent about the new character with all data including mules
        this.$emit('character-added', response.data);
        
        // We don't emit mule-added here anymore to avoid multiple notifications
        // when importing a character with multiple mules
        // The mule-added event is only emitted from the ExistingCharacterMuleForm component

        // Reset form data
        this.$refs.mainCharacterForm.resetForm();
        this.$refs.mulesManager.resetForm();
        this.errorMessage = '';
        
        // Close modal
        this.closeModal();
      } catch (error) {
        console.error('Error creating character:', error.response?.data || error.message);
        this.errorMessage = 'Une erreur est survenue lors de la création du personnage.';
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async fetchBlacklist() {
      try {
        const response = await axios.get(`${API_URL}/blacklist`);
        this.blacklist = response.data;
      } catch (error) {
        console.error('Error fetching blacklist:', error);
      }
    },
  },
  mounted() {
    this.fetchBlacklist();
  }
};
</script>
