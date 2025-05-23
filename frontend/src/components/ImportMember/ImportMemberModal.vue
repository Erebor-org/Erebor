<template>
  <div
    v-if="showModal"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4 md:p-0"
    @click.self="closeModal"
  >
    <!-- Modal Content -->
    <div
      class="w-full lg:max-w-[90vw] xl:max-w-[80vw] bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg relative max-h-[90vh] overflow-hidden flex flex-col"
    >
      <!-- Close Button -->
      <button
        class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-xl h-8 w-8 flex items-center justify-center rounded-full hover:bg-[#f3d9b1] transition-colors z-20"
        @click="closeModal"
        aria-label="Fermer"
      >
        &times;
      </button>
      
      <!-- Title at the top -->
      <div class="sticky top-0 bg-[#fff5e6] z-10 border-b-2 border-[#b07d46] py-3">
        <h2 class="text-xl md:text-2xl font-bold text-[#b02e2e] text-center">
          Import de personnage
        </h2>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-[#b07d46]">
        <button 
          @click="activeTab = 'newCharacter'"
          :class="[
            'flex-1 py-2 px-4 text-center font-medium transition-colors',
            activeTab === 'newCharacter' 
              ? 'text-[#b02e2e] border-b-2 border-[#b02e2e]' 
              : 'text-[#b07d46] hover:text-[#b02e2e]'
          ]"
        >
          Nouveau Personnage
        </button>
        <button 
          @click="activeTab = 'addMule'"
          :class="[
            'flex-1 py-2 px-4 text-center font-medium transition-colors',
            activeTab === 'addMule' 
              ? 'text-[#b02e2e] border-b-2 border-[#b02e2e]' 
              : 'text-[#b07d46] hover:text-[#b02e2e]'
          ]"
        >
          Ajouter une Mule
        </button>
      </div>

      <!-- Scrollable Content Area -->
      <div class="p-4 md:p-6 overflow-y-auto text-lg">
        <!-- New Character Tab -->
        <div v-if="activeTab === 'newCharacter'">
          <form @submit.prevent="submitForm" class="flex flex-col lg:flex-row gap-6">
            <!-- Main Character Form -->
            <div class="lg:w-1/2">
              <MainCharacterForm 
                ref="mainCharacterForm"
                :classes="classes" 
                :blacklist="blacklist"
                @update:character="updateCharacter"
              />
            </div>

            <!-- Mules Section -->
            <div class="lg:w-1/2">
              <MulesManager 
                ref="mulesManager"
                :classes="classes" 
                :blacklist="blacklist"
                @update:mules="updateMules"
              />
            </div>
          </form>

          <!-- Global error message -->
          <div
            v-if="errorMessage"
            class="mt-4 p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
          >
            <div class="flex items-center">
              <span class="mr-2 text-xl">⚠️</span>
              <span class="font-medium">{{ errorMessage }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="mt-6">
            <button
              type="button"
              @click="submitForm"
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
              Importer {{ mules.length > 0 ? 'le personnage et ses mules' : 'le personnage' }}
            </button>
          </div>
        </div>
        
        <!-- Add Mule to Existing Character Tab -->
        <div v-if="activeTab === 'addMule'">
          <ExistingCharacterMuleForm
            ref="existingCharacterMuleForm"
            :classes="classes"
            :blacklist="blacklist"
            :fetchNotArchivedCharacters="fetchNotArchivedCharacters"
            @mule-added="handleMuleAdded"
            @close="closeModal"
          />
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
