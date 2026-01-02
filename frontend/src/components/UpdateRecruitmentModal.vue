<template>
  <div v-if="show" class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModal">
    <div class="bg-theme-card border border-theme-border rounded-2xl shadow-2xl max-w-lg w-full p-6 relative">
      <h3 class="text-xl font-bold text-theme-primary mb-4">Modifier le recrutement</h3>
      
      <!-- Character Info -->
      <div class="mb-6 p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
        <p class="text-sm text-theme-text-muted mb-1">Personnage</p>
        <p class="text-lg font-semibold text-theme-text">{{ character?.pseudo || '' }}</p>
      </div>

      <!-- Recruiter Selection -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-theme-text mb-2">
          Recruteur
        </label>
        <div class="relative">
          <button
            @click="showRecruiterDropdown = !showRecruiterDropdown"
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary min-w-full text-left flex items-center space-x-2"
          >
            <img 
              v-if="selectedRecruiter"
              :src="getClassIcon(selectedRecruiter.class)" 
              :alt="`Classe ${selectedRecruiter.class}`" 
              class="w-5 h-5 rounded border border-theme-border"
            />
            <div v-else class="w-5 h-5"></div>
            <span class="flex-1">
              {{ selectedRecruiter ? selectedRecruiter.pseudo : 'Aucun recruteur' }}
            </span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <!-- Dropdown -->
          <div 
            v-if="showRecruiterDropdown"
            class="absolute z-50 mt-1 w-full bg-theme-card border border-theme-border rounded-lg shadow-lg max-h-64 overflow-auto"
          >
            <div
              @click="selectRecruiter(null)"
              class="px-3 py-2 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer flex items-center space-x-2"
              :class="{ 'bg-theme-primary/10': !selectedRecruiterId }"
            >
              <div class="w-5 h-5"></div>
              <span>Aucun recruteur</span>
            </div>
            <div
              v-for="char in availableRecruiters"
              :key="char.id"
              @click="selectRecruiter(char)"
              class="px-3 py-2 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer flex items-center space-x-2"
              :class="{ 'bg-theme-primary/10': selectedRecruiterId === char.id }"
            >
              <img 
                :src="getClassIcon(char.class)" 
                :alt="`Classe ${char.class}`" 
                class="w-5 h-5 rounded border border-theme-border"
              />
              <span>{{ char.pseudo }}</span>
            </div>
          </div>
        </div>
        <p class="text-xs text-theme-text-muted mt-1">
          Sélectionnez le personnage qui a recruté ce membre
        </p>
      </div>

      <!-- Recruitment Date -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-theme-text mb-2">
          Date de recrutement
        </label>
        <ThemedDatePicker
          v-model="recruitmentDate"
          :max-date="maxDate"
          :format="'dd/MM/yyyy'"
          :placeholder="'Sélectionner une date'"
        />
        <p class="text-xs text-theme-text-muted mt-1">
          La date ne peut pas être dans le futur
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mb-4 p-3 bg-theme-error/10 border border-theme-error rounded-lg">
        <p class="text-sm text-theme-error">{{ errorMessage }}</p>
      </div>

      <!-- Actions -->
      <div class="flex justify-end mt-6 space-x-2">
        <button
          @click="closeModal"
          class="px-4 py-2 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border text-sm font-medium transition-colors"
        >
          Annuler
        </button>
        <button
          @click="saveRecruitment"
          :disabled="loading"
          class="px-4 py-2 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Enregistrement...</span>
          <span v-else>Sauvegarder</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ThemedDatePicker from '@/components/ThemedDatePicker.vue';
import { getClassIcon } from '@/config/classIcons';
import { useThemeStore } from '@/stores/themeStore';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'UpdateRecruitmentModal',
  components: {
    ThemedDatePicker,
  },
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    character: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      selectedRecruiterId: null,
      selectedRecruiter: null,
      recruitmentDate: null,
      availableRecruiters: [],
      loading: false,
      errorMessage: '',
      showRecruiterDropdown: false,
      maxDate: new Date(), // Today's date
    };
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.initializeData();
        this.fetchRecruiters();
        // Add click outside listener when modal opens
        this.$nextTick(() => {
          document.addEventListener('click', this.handleClickOutside);
        });
      } else {
        // Remove click outside listener when modal closes
        document.removeEventListener('click', this.handleClickOutside);
        this.showRecruiterDropdown = false;
      }
    },
    character: {
      handler(newVal) {
        if (newVal && this.show) {
          this.initializeData();
        }
      },
      deep: true,
    },
  },
  beforeUnmount() {
    // Clean up event listener
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    handleClickOutside(event) {
      if (!this.showRecruiterDropdown) return;
      
      const dropdownContainer = this.$el?.querySelector('.relative');
      if (dropdownContainer && !dropdownContainer.contains(event.target)) {
        this.showRecruiterDropdown = false;
      }
    },
    getClassIcon,
    initializeData() {
      if (this.character) {
        // Set recruiter
        this.selectedRecruiterId = this.character.recruiter?.id || null;
        this.selectedRecruiter = this.character.recruiter || null;
        
        // Set recruitment date
        if (this.character.createdAt) {
          this.recruitmentDate = new Date(this.character.createdAt);
        } else {
          this.recruitmentDate = null;
        }
      }
      this.errorMessage = '';
      this.showRecruiterDropdown = false;
    },
    selectRecruiter(recruiter) {
      this.selectedRecruiterId = recruiter?.id || null;
      this.selectedRecruiter = recruiter || null;
      this.showRecruiterDropdown = false;
    },
    async fetchRecruiters() {
      try {
        const response = await axios.get(`${API_URL}/characters/`);
        // Filter out the current character and archived characters
        this.availableRecruiters = response.data.filter(
          (char) => !char.isArchived && char.id !== this.character?.id
        );
      } catch (error) {
        console.error('Error fetching recruiters:', error);
        this.errorMessage = 'Erreur lors du chargement des recruteurs';
      }
    },
    closeModal() {
      this.errorMessage = '';
      this.showRecruiterDropdown = false;
      this.$emit('close');
    },
    async saveRecruitment() {
      this.loading = true;
      this.errorMessage = '';

      try {
        const payload = {};
        
        // Only include fields that should be updated
        if (this.selectedRecruiterId !== (this.character?.recruiter?.id || null)) {
          payload.recruiterId = this.selectedRecruiterId;
        }
        
        if (this.recruitmentDate) {
          const dateStr = this.recruitmentDate instanceof Date 
            ? this.recruitmentDate.toISOString().split('T')[0]
            : this.recruitmentDate;
          
          const currentDate = this.character?.createdAt 
            ? new Date(this.character.createdAt).toISOString().split('T')[0]
            : null;
          
          if (dateStr !== currentDate) {
            // Validate date is not in the future
            const selectedDate = new Date(dateStr);
            const today = new Date();
            today.setHours(23, 59, 59, 999); // End of today
            
            if (selectedDate > today) {
              this.errorMessage = 'La date ne peut pas être dans le futur';
              this.loading = false;
              return;
            }
            
            payload.recruitedAt = dateStr;
          }
        }

        // Only make the request if there are changes
        if (Object.keys(payload).length === 0) {
          this.closeModal();
          return;
        }

        const response = await axios.put(
          `${API_URL}/characters/${this.character.id}/update-recruitment`,
          payload
        );

        if (response.data) {
          this.$emit('saved', response.data.character);
          this.closeModal();
        }
      } catch (error) {
        console.error('Error updating recruitment:', error);
        this.errorMessage = error.response?.data?.error || 'Erreur lors de la mise à jour';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

