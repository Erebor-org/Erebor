<template>
  <div>
    <div class="flex items-center space-x-3 mb-6">
      <div class="w-6 h-6 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </div>
      <h4 class="text-lg font-bold text-amber-400">Nouvelle Mule</h4>
    </div>

    <div class="space-y-6">
      <!-- Pseudo & Ankama Pseudo -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pseudo -->
        <div class="space-y-2">
          <label
            for="mulePseudo"
            class="block text-sm font-medium text-gray-300"
          >
            Pseudo <span class="text-red-400">*</span>
          </label>
          <input
            type="text"
            id="mulePseudo"
            v-model="mule.pseudo"
            class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
            :class="{ 'border-red-500 bg-red-900/20': isPseudoInvalid }"
            placeholder="Entrez le pseudo de la mule"
          />
          <div
            v-if="isPseudoInvalid"
            class="flex items-center space-x-2 text-red-400 text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ mule.pseudo }}" est blacklist d'Erebor.</span>
          </div>
        </div>

        <!-- Pseudo Ankama -->
        <div class="space-y-2">
          <label
            for="muleAnkamaPseudo"
            class="block text-sm font-medium text-gray-300"
          >
            Pseudo Ankama <span class="text-red-400">*</span>
          </label>
          <input
            type="text"
            id="muleAnkamaPseudo"
            v-model="mule.ankamaPseudo"
            class="w-full bg-gray-700 border-2 border-gray-600 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all duration-200 placeholder-gray-400"
            :class="{ 'border-red-500 bg-red-900/20': isAnkamaPseudoInvalid }"
            placeholder="Entrez le pseudo Ankama"
          />
          <div
            v-if="isAnkamaPseudoInvalid"
            class="flex items-center space-x-2 text-red-400 text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ mule.ankamaPseudo }}" est blacklist d'Erebor.</span>
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
            :key="'mule-class-'+className"
            type="button"
            @click="mule.class = className"
            class="group relative p-3 rounded-xl border-2 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-800"
            :class="[
              mule.class === className
                ? 'border-amber-500 bg-amber-500/20 shadow-lg shadow-amber-500/25'
                : 'border-gray-600 hover:border-amber-400 bg-gray-700 hover:bg-gray-600'
            ]"
          >
            <img
              :src="icon"
              :alt="className"
              class="w-10 h-10 rounded-lg mx-auto mb-2 group-hover:scale-110 transition-transform duration-200"
            />
            <span class="block text-xs font-medium text-center capitalize transition-colors duration-200"
                  :class="[
                    mule.class === className
                      ? 'text-amber-400'
                      : 'text-gray-400 group-hover:text-gray-300'
                  ]">
              {{ className }}
            </span>
            
            <!-- Selection indicator -->
            <div v-if="mule.class === className"
                 class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-lg">
              <svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </button>
        </div>
      </div>
      
      <!-- Error message for mule -->
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
      
      <!-- Add mule button -->
      <div class="flex justify-end">
        <button
          type="button"
          @click="submitMule"
          class="px-6 py-3 bg-gradient-to-r from-amber-600 to-yellow-600 hover:from-amber-700 hover:to-yellow-700 text-black font-bold rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          :disabled="!canAddMule"
        >
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Ajouter cette mule</span>
          </div>
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
