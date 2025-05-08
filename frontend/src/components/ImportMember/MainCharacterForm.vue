<template>
  <div class="bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-4 mb-4">
    <div class="space-y-4">
      <!-- Pseudo & Ankama Pseudo -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pseudo -->
        <div>
          <label
            for="characterPseudo"
            class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
          >
            Pseudo <span class="text-[#b02e2e]">*</span>
          </label>
          <input
            type="text"
            id="characterPseudo"
            v-model="character.pseudo"
            class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
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
            for="characterAnkamaPseudo"
            class="block text-sm md:text-base font-medium text-[#b07d46] mb-1"
          >
            Pseudo Ankama <span class="text-[#b02e2e]">*</span>
          </label>
          <input
            type="text"
            id="characterAnkamaPseudo"
            v-model="character.ankamaPseudo"
            class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isAnkamaPseudoInvalid }"
          />
          <div
            v-if="isAnkamaPseudoInvalid"
            class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
          >
            <span class="mr-1">⚠️</span> "{{ character.ankamaPseudo }}" est blacklist d'Erebor.
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
            :key="'character-class-'+index"
            :class="[
              'cursor-pointer border-2 rounded-md flex items-center justify-center aspect-square transition-all',
              character.class === index
                ? 'border-[#b02e2e] bg-[#f3d9b1] scale-110 shadow-md'
                : 'border-[#b07d46] bg-[#fffaf0] hover:bg-[#f3d9b1] hover:border-[#b02e2e]',
            ]"
            @click="character.class = index"
          >
            <img :src="icon" alt="Classe" class="w-full h-full p-1 md:p-2 object-contain" />
          </div>
        </div>
      </div>

      <!-- Recruiter selector -->
      <RecruiterSelector 
        :characters="characters" 
        v-model="character.recruiterId"
      />

      <!-- Error message for character -->
      <div
        v-if="errorMessage"
        class="p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
      >
        <div class="flex items-center">
          <span class="mr-2 text-xl">⚠️</span>
          <span class="font-medium">{{ errorMessage }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import RecruiterSelector from './RecruiterSelector.vue';

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
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null,
      }
    };
  },
  computed: {
    // Validation computed properties
    isPseudoInvalid() {
      return (
        this.character.pseudo &&
        this.blacklist.some(entry => entry.pseudo.toLowerCase() === this.character.pseudo.toLowerCase())
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
    validateForm() {
      // Validation
      if (!this.character.class) {
        this.errorMessage = 'Veuillez sélectionner une classe pour le personnage.';
        return false;
      }
      
      if (!this.character.pseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo pour le personnage.';
        return false;
      }
      
      if (!this.character.ankamaPseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo Ankama pour le personnage.';
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

      return true;
    },
    
    resetForm() {
      this.character = {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: '',
        recruiterId: null,
        isArchived: false,
        userId: null,
      };
      this.errorMessage = '';
    }
  }
};
</script>
