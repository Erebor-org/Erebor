<template>
  <div>
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
        <RecruiterSelector 
          :recruiters="recruiters"
          :selected-recruiter-id="character.recruiterId"
          :classes="classes" 
          @select-recruiter="selectRecruiter"
          @clear-recruiter="clearRecruiter"
        />
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
            @click="selectClass(index)"
          >
            <img :src="icon" alt="Classe" class="w-full h-full p-1 md:p-2 object-contain" />
          </div>
        </div>
      </div>
    </div>

    <!-- Erreur locale -->
    <div
      v-if="errorMessage"
      class="mt-4 p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
    >
      <div class="flex items-center">
        <span class="mr-2 text-xl">⚠️</span>
        <span class="font-medium">{{ errorMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import RecruiterSelector from './RecruiterSelector.vue';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    RecruiterSelector
  },
  props: {
    classes: {
      type: Object,
      required: true
    },
    blacklist: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      errorMessage: '',
      character: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: new Date().toISOString().substr(0, 10),
        recruiterId: null,
        isArchived: false,
        userId: null,
      },
      recruiters: [],
      selectedRecruiterName: '',
      selectedRecruiterIcon: '',
    };
  },
  computed: {
    isPseudoInvalid() {
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
    }
  },
  methods: {
    selectClass(classIndex) {
      this.character.class = classIndex;
      this.emitUpdate();
    },
    
    selectRecruiter(recruiter) {
      this.character.recruiterId = recruiter.id;
      this.selectedRecruiterName = recruiter.pseudo;
      this.selectedRecruiterIcon = this.classes[recruiter.class];
      this.emitUpdate();
    },
    
    clearRecruiter() {
      this.character.recruiterId = null;
      this.selectedRecruiterName = '';
      this.selectedRecruiterIcon = '';
      this.emitUpdate();
    },
    
    emitUpdate() {
      this.$emit('update:character', { ...this.character });
    },
    
    validateForm() {
      // Validate main character
      if (!this.character.class) {
        this.errorMessage = 'Veuillez sélectionner une classe pour le personnage principal.';
        return false;
      }
      
      if (!this.character.pseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo pour le personnage principal.';
        return false;
      }
      
      if (!this.character.ankamaPseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo Ankama pour le personnage principal.';
        return false;
      }
      
      if (this.isPseudoInvalid) {
        this.errorMessage = `"${this.character.pseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      if (this.isAnkamaPseudoInvalid) {
        this.errorMessage = `"${this.character.ankamaPseudo}" est blacklist d'Erebor.`;
        return false;
      }
      
      this.errorMessage = '';
      return true;
    },
    
    resetForm() {
      this.character = {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: new Date().toISOString().substr(0, 10),
        recruiterId: null,
        isArchived: false,
        userId: null,
      };
      this.errorMessage = '';
      this.emitUpdate();
    },
    
    async fetchRecruiters() {
      try {
        const response = await axios.get(`${API_URL}/characters/recruiters`);
        this.recruiters = response.data;
      } catch (error) {
        console.error('Error fetching recruiters:', error);
      }
    }
  },
  mounted() {
    this.fetchRecruiters();
    this.emitUpdate();
  },
  watch: {
    'character.pseudo'() {
      this.emitUpdate();
    },
    'character.ankamaPseudo'() {
      this.emitUpdate();
    },
    'character.recruitedAt'() {
      this.emitUpdate();
    }
  }
};
</script>
