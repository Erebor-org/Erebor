<template>
  <div>
    <div class="flex items-center space-x-3 mb-6">
      <div class="w-8 h-8 bg-theme-primary rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-theme-primary">Personnage Principal</h2>
    </div>

    <div class="space-y-6">
      <!-- Pseudo & Ankama Pseudo -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pseudo -->
        <div class="space-y-2">
          <label
            for="mainPseudo"
            class="block text-sm font-medium text-theme-text"
          >
            Pseudo <span class="text-theme-error">*</span>
          </label>
          <input
            type="text"
            id="mainPseudo"
            v-model="character.pseudo"
            class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
            :class="{ 'border-theme-error bg-theme-error/20': isPseudoInvalid }"
            placeholder="Entrez le pseudo du personnage"
            required
          />
          <div
            v-if="isPseudoInvalid"
            class="flex items-center space-x-2 text-theme-error text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ character.pseudo }}" est blacklist d'Erebor.</span>
          </div>
        </div>

        <!-- Pseudo Ankama -->
        <div class="space-y-2">
          <label
            for="ankamaPseudo"
            class="block text-sm font-medium text-theme-text"
          >
            Pseudo Ankama <span class="text-theme-error">*</span>
          </label>
          <input
            type="text"
            id="ankamaPseudo"
            v-model="character.ankamaPseudo"
            class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
            :class="{ 'border-theme-error bg-theme-error/20': isAnkamaPseudoInvalid }"
            placeholder="Entrez le pseudo Ankama"
            required
          />
          <div
            v-if="isAnkamaPseudoInvalid"
            class="flex items-center space-x-2 text-theme-error text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>"{{ character.ankamaPseudo }}" est blacklist d'Erebor.</span>
          </div>
        </div>
      </div>

      <!-- Date & Recruteur -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Date -->
        <div class="space-y-2">
          <label
            for="recruitedAt"
            class="block text-sm font-medium text-theme-text cursor-pointer hover:text-theme-primary transition-colors duration-200 group focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg-muted rounded-lg px-2 py-1 -ml-2"
            @click="openDatePicker"
            role="button"
            tabindex="0"
            @keydown.enter="openDatePicker"
            @keydown.space="openDatePicker"
          >
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Date de recrutement <span class="text-theme-error">*</span></span>
            </div>
          </label>
          <div class="relative">
            <input
              type="date"
              id="recruitedAt"
              name="recruitedAt"
              v-model="character.recruitedAt"
              class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 pr-12 cursor-pointer"
              required
              @change="validateDate"
              @click="openDatePicker"
              :max="new Date().toISOString().slice(0, 10)"
              style="color-scheme: light dark;"
              ref="dateInput"
            />
            <button
              type="button"
              @click="openDatePicker"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-theme-primary hover:bg-theme-primary-hover text-theme-bg rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg-muted"
              title="Ouvrir le sélecteur de date"
              aria-label="Ouvrir le sélecteur de date"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </button>
          </div>
          <div
            v-if="dateError"
            class="flex items-center space-x-2 text-theme-error text-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span>{{ dateError }}</span>
          </div>
        </div>

        <!-- Recruteur -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-theme-text">
            Recruteur
          </label>
          <RecruiterSelector 
            :recruiters="recruiters"
            :selected-recruiter-id="character.recruiterId"
            :classes="classes" 
            @select-recruiter="selectRecruiter"
            @clear-recruiter="clearRecruiter"
          />
        </div>
      </div>
      
      <!-- Classe Selection -->
      <div class="space-y-4">
        <label class="block text-sm font-medium text-theme-text">
          Classe du personnage <span class="text-theme-error">*</span>
        </label>
        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 gap-3">
          <button
            v-for="(icon, className) in classes"
            :key="className"
            type="button"
            @click="selectClass(className)"
            class="group relative p-3 rounded-xl border-2 transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-primary focus:ring-offset-2 focus:ring-offset-theme-bg-muted"
            :class="[
              character.class === className
                ? 'border-theme-primary bg-theme-primary/20 shadow-lg shadow-theme-primary/25'
                : 'border-theme-border hover:border-theme-primary bg-theme-bg-muted hover:bg-theme-border'
            ]"
          >
            <img
              :src="icon"
              :alt="className"
              class="w-12 h-12 rounded-lg mx-auto mb-2 group-hover:scale-110 transition-transform duration-200"
            />
            <span class="block text-xs font-medium text-center capitalize transition-colors duration-200"
                  :class="[
                    character.class === className
                      ? 'text-theme-primary'
                      : 'text-theme-text-muted group-hover:text-theme-text'
                  ]">
              {{ className }}
            </span>
            
            <!-- Selection indicator -->
            <div v-if="character.class === className"
                 class="absolute -top-1 -right-1 w-6 h-6 bg-theme-primary rounded-full flex items-center justify-center shadow-lg">
              <svg class="w-3 h-3 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </button>
        </div>
      </div>
      <!-- Notes Field -->
      <div class="space-y-2">
        <label for="notes" class="block text-sm font-medium text-theme-text">
          Notes (facultatif)
        </label>
        <textarea
          id="notes"
          v-model="character.notes"
          class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
          rows="3"
          placeholder="Contexte de recrutement ou information(s) pertinente(s) sur le membre..."
        ></textarea>
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
      dateError: '',
      character: {
        pseudo: '',
        ankamaPseudo: '',
        class: '',
        recruitedAt: new Date().toISOString().slice(0, 10),
        recruiterId: null,
        isArchived: false,
        userId: null,
        notes: '',
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
      
      // Validate date
      if (!this.validateDate()) {
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
        recruitedAt: new Date().toISOString().slice(0, 10),
        recruiterId: null,
        isArchived: false,
        userId: null,
        notes: '',
      };
      this.errorMessage = '';
      this.dateError = '';
      this.emitUpdate();
    },
    
    async fetchRecruiters() {
      try {
        const response = await axios.get(`${API_URL}/characters/recruiters`);
        this.recruiters = response.data;
      } catch (error) {
        console.error('Error fetching recruiters:', error);
      }
    },
    
    validateDate() {
      this.dateError = '';
      
      console.log('Validating date:', this.character.recruitedAt);
      
      if (!this.character.recruitedAt) {
        this.dateError = 'La date de recrutement est requise.';
        return false;
      }
      
      // Check if the date string is valid
      if (!/^\d{4}-\d{2}-\d{2}$/.test(this.character.recruitedAt)) {
        this.dateError = 'Format de date invalide.';
        console.error('Invalid date format:', this.character.recruitedAt);
        return false;
      }
      
      const selectedDate = new Date(this.character.recruitedAt + 'T00:00:00');
      const today = new Date();
      
      console.log('Selected date:', selectedDate);
      console.log('Today:', today);
      
      // Check if the date is valid
      if (isNaN(selectedDate.getTime())) {
        this.dateError = 'Date invalide.';
        console.error('Invalid date object:', selectedDate);
        return false;
      }
      
      if (selectedDate > today) {
        this.dateError = 'La date de recrutement ne peut pas être dans le futur.';
        return false;
      }
      
      if (selectedDate < new Date('2000-01-01')) {
        this.dateError = 'La date de recrutement semble invalide.';
        return false;
      }
      
      console.log('Date validation passed');
      return true;
    },
    
    setupDateFallback() {
      // For browsers that don't support date input, we could implement a custom date picker
      // For now, just log a warning
      console.warn('Consider implementing a custom date picker for better browser compatibility');
    },
    
    openDatePicker() {
      // Focus and click the date input to open the date picker
      if (this.$refs.dateInput) {
        // Add a subtle visual feedback
        this.$refs.dateInput.classList.add('ring-2', 'ring-theme-primary');
        
        // Remove the ring after a short delay
        setTimeout(() => {
          this.$refs.dateInput.classList.remove('ring-2', 'ring-theme-primary');
        }, 300);
        
        this.$refs.dateInput.focus();
        
        // Try to use showPicker() method (modern browsers)
        if (typeof this.$refs.dateInput.showPicker === 'function') {
          this.$refs.dateInput.showPicker();
        } else {
          // Fallback: trigger a click event to open the date picker
          this.$refs.dateInput.click();
        }
      }
    }
  },
  mounted() {
    this.fetchRecruiters();
    this.emitUpdate();
    
    // Check if the browser supports the date input
    const dateInput = document.getElementById('recruitedAt');
    if (dateInput && dateInput.type === 'date') {
      // Browser supports date input
      console.log('Date input supported');
    } else {
      // Fallback for browsers that don't support date input
      console.warn('Date input not supported, using fallback');
      this.setupDateFallback();
    }
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
    },
    'character.notes'() {
      this.emitUpdate();
    }
  }
};
</script>
