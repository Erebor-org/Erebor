<template>
  <div class="bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-3 mb-3">
    <div class="space-y-3">
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
            v-model="mule.pseudo"
            class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isPseudoInvalid }"
          />
          <div
            v-if="isPseudoInvalid"
            class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
          >
            <span class="mr-1">⚠️</span> "{{ mule.pseudo }}" est blacklist d'Erebor.
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
            v-model="mule.ankamaPseudo"
            class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 md:p-3 text-base focus:outline-none focus:ring-2 focus:ring-[#b02e2e]"
            :class="{ 'border-[#b02e2e] bg-[#fff0f0]': isAnkamaPseudoInvalid }"
          />
          <div
            v-if="isAnkamaPseudoInvalid"
            class="text-[#b02e2e] text-sm font-medium mt-1 flex items-center"
          >
            <span class="mr-1">⚠️</span> "{{ mule.ankamaPseudo }}" est blacklist d'Erebor.
          </div>
        </div>
      </div>

      <!-- Classe (responsive grid) -->
      <div>
        <label class="block text-sm md:text-base font-medium text-[#b07d46] mb-2">
          Classe du personnage <span class="text-[#b02e2e]">*</span>
        </label>
        <div
          class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 gap-1 md:gap-2"
        >
          <div
            v-for="(icon, index) in classes"
            :key="'mule-class-'+index"
            :class="[
              'cursor-pointer border-2 rounded-md flex items-center justify-center aspect-square transition-all',
              mule.class === index
                ? 'border-[#b02e2e] bg-[#f3d9b1] scale-100 shadow-md'
                : 'border-[#b07d46] bg-[#fffaf0] hover:bg-[#f3d9b1] hover:border-[#b02e2e]',
            ]"
            @click="mule.class = index"
          >
            <img :src="icon" alt="Classe" class="w-16 h-16 object-contain" />
          </div>
        </div>
      </div>
      
      <!-- Error message for mule -->
      <div
        v-if="errorMessage"
        class="p-3 bg-[#ffeeee] border-l-4 border-[#b02e2e] text-[#b02e2e] rounded"
      >
        <div class="flex items-center">
          <span class="mr-2 text-xl">⚠️</span>
          <span class="font-medium">{{ errorMessage }}</span>
        </div>
      </div>
      
      <!-- Add mule button -->
      <div class="flex justify-end">
        <button
          type="button"
          @click="submitMule"
          class="bg-[#b07d46] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#966a3b] focus:ring-2 focus:ring-[#b07d46] transition-colors"
          :disabled="!canAddMule"
          :class="{ 'opacity-70 cursor-not-allowed': !canAddMule }"
        >
          Ajouter cette mule
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
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
      mule: {
        pseudo: '',
        ankamaPseudo: '',
        class: null
      }
    };
  },
  computed: {
    // Check if mule can be added
    canAddMule() {
      return this.mule.pseudo && 
             this.mule.ankamaPseudo && 
             this.mule.class !== null &&
             !this.isPseudoInvalid &&
             !this.isAnkamaPseudoInvalid;
    },
    
    // Validation computed properties
    isPseudoInvalid() {
      return (
        this.mule.pseudo &&
        this.blacklist.some(entry => entry.pseudo.toLowerCase() === this.mule.pseudo.toLowerCase())
      );
    },
    
    isAnkamaPseudoInvalid() {
      return (
        this.mule.ankamaPseudo &&
        this.blacklist.some(
          entry => entry.ankamaPseudo.toLowerCase() === this.mule.ankamaPseudo.toLowerCase()
        )
      );
    }
  },
  methods: {
    submitMule() {
      // Validation
      if (!this.mule.class) {
        this.errorMessage = 'Veuillez sélectionner une classe pour la mule.';
        return;
      }
      
      if (!this.mule.pseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo pour la mule.';
        return;
      }
      
      if (!this.mule.ankamaPseudo) {
        this.errorMessage = 'Veuillez entrer un pseudo Ankama pour la mule.';
        return;
      }
      
      if (this.isPseudoInvalid) {
        this.errorMessage = `"${this.mule.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      
      if (this.isAnkamaPseudoInvalid) {
        this.errorMessage = `"${this.mule.ankamaPseudo}" est blacklist d'Erebor.`;
        return;
      }

      // Emit the event to add mule
      this.$emit('add-mule', { ...this.mule });

      // Reset form
      this.mule = {
        pseudo: '',
        ankamaPseudo: '',
        class: null
      };
      this.errorMessage = '';
    }
  }
};
</script>
