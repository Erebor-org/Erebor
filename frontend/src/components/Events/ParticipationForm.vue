<template>
  <div class="bg-white border-2 border-[#b07d46] rounded-lg p-6 shadow-lg">
    <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">Ajouter des résultats</h2>
    
    <div v-if="errorMessage" class="bg-red-500 text-white p-3 rounded-md mb-4">
      {{ errorMessage }}
    </div>
    
    <div v-if="successMessage" class="bg-green-500 text-white p-3 rounded-md mb-4">
      {{ successMessage }}
    </div>
    
    <div class="mb-6">
      <label class="block text-[#b07d46] font-bold mb-2">Événement</label>
      <select 
        v-model="selectedEventId"
        class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
        :disabled="eventId || isLoading"
        @change="checkExistingParticipations"
      >
        <option value="" disabled>Sélectionner un événement</option>
        <option v-for="event in events" :key="event.id" :value="event.id">
          {{ event.title }} ({{ new Date(event.eventDate).toLocaleDateString() }})
        </option>
      </select>
    </div>
    
    <!-- Existing participations notice -->
    <div v-if="hasExistingParticipations" class="mb-6 p-4 bg-[#f3d9b1]/30 border border-[#b07d46] rounded-lg">
      <p class="text-[#b07d46] font-medium">
        <span class="font-bold">Note:</span> Cet événement a déjà des participants. Les nouveaux participants seront ajoutés aux participants existants.
      </p>
    </div>
    
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-[#b02e2e]">Participants</h3>
        <button 
          @click="addParticipationRow"
          class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-3 py-1 rounded-full text-sm"
          :disabled="isLoading"
        >
          Ajouter un participant
        </button>
      </div>
      
      <div v-if="participations.length === 0" class="text-center py-4 text-[#b07d46]">
        Aucun participant ajouté
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="(participation, index) in participations" 
          :key="index"
          class="flex items-center gap-4 p-3 bg-white border-2 border-[#b07d46]/30 rounded-md"
        >
          <div class="flex-grow">
            <label class="block text-[#b07d46] mb-1 text-sm font-bold">Personnage</label>
            <select 
              v-model="participation.characterId"
              class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              :disabled="isLoading"
              @change="checkDuplicateCharacter(index)"
            >
              <option value="" disabled>Sélectionner un personnage</option>
              <option 
                v-for="character in availableCharacters(index)" 
                :key="character.id" 
                :value="character.id"
                :disabled="isCharacterUsed(character.id, index)"
              >
                {{ character.pseudo }} ({{ character.class }})
              </option>
            </select>
            <div v-if="participation.error" class="text-red-500 text-xs mt-1">
              {{ participation.error }}
            </div>
          </div>
          
          <div class="w-24">
            <label class="block text-[#b07d46] mb-1 text-sm font-bold">Position</label>
            <select 
              v-model="participation.position"
              class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              :disabled="isLoading"
              @change="updatePoints(participation)"
            >
              <option value="" disabled>Position</option>
              <option v-for="position in maxPosition" :key="position" :value="position">
                {{ position }}
              </option>
            </select>
          </div>
          
          <div class="w-24">
            <label class="block text-[#b07d46] mb-1 text-sm font-bold">Points</label>
            <div class="bg-white border-2 border-[#b07d46]/30 rounded-md p-2 text-center text-[#93a402] font-bold">
              {{ participation.points }}
            </div>
          </div>
          
          <button 
            @click="removeParticipationRow(index)"
            class="bg-[#b02e2e] hover:bg-[#942828] text-white p-2 rounded-full"
            :disabled="isLoading"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end">
      <button 
        @click="submitParticipations"
        class="bg-[#b02e2e] hover:bg-[#942828] text-[#f3d9b1] px-6 py-2 rounded-full font-medium"
        :disabled="isLoading || !canSubmit"
      >
        <span v-if="isLoading">Chargement...</span>
        <span v-else>Enregistrer les résultats</span>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    eventId: {
      type: [Number, String],
      default: null
    },
    isAdminOrAnim: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      API_URL: import.meta.env.VITE_API_URL,
      events: [],
      characters: [],
      selectedEventId: this.eventId || '',
      participations: [],
      isLoading: false,
      errorMessage: '',
      successMessage: '',
      existingParticipations: [],
      hasExistingParticipations: false,
      // Formula 1 scoring system
      scoringSystem: {
        1: 25,
        2: 18,
        3: 15,
        4: 12,
        5: 10,
        6: 8,
        7: 6,
        8: 4,
        9: 2,
        10: 1
      }
    };
  },
  computed: {
    maxPosition() {
      return Math.max(...Object.keys(this.scoringSystem).map(Number));
    },
    canSubmit() {
      if (this.participations.length === 0) return false;
      
      // Check if all participations are valid
      for (const participation of this.participations) {
        if (!participation.characterId || !participation.position || participation.error) {
          return false;
        }
      }
      
      return true;
    }
  },
  methods: {
    async fetchEvents() {
      try {
        const response = await axios.get(`${this.API_URL}/events`);
        return response.data;
      } catch (error) {
        console.error('Error fetching events:', error);
        throw error;
      }
    },
    async fetchExistingParticipations(eventId) {
      try {
        const response = await axios.get(`${this.API_URL}/event-participations/event/${eventId}`);
        this.existingParticipations = response.data;
        this.hasExistingParticipations = this.existingParticipations.length > 0;
        return response.data;
      } catch (error) {
        console.error('Error fetching existing participations:', error);
        this.existingParticipations = [];
        this.hasExistingParticipations = false;
        return [];
      }
    },
    async addBatchParticipations(eventId, participations) {
      try {
        const response = await axios.post(`${this.API_URL}/event-participations/batch`, {
          eventId,
          participations
        });
        
        return response.data;
      } catch (error) {
        console.error('Error adding batch participations:', error);
        throw error;
      }
    },
    addParticipationRow() {
      this.participations.push({
        characterId: '',
        position: '',
        points: 0,
        error: null
      });
    },
    removeParticipationRow(index) {
      this.participations.splice(index, 1);
    },
    updatePoints(participation) {
      const position = parseInt(participation.position);
      participation.points = this.scoringSystem[position] || 0;
    },
    async checkExistingParticipations() {
      if (!this.selectedEventId) return;
      
      this.isLoading = true;
      await this.fetchExistingParticipations(this.selectedEventId);
      this.isLoading = false;
    },
    isCharacterUsed(characterId, currentIndex) {
      // Check if character is already used in another participation row
      return this.participations.some((p, index) => 
        index !== currentIndex && p.characterId === characterId
      ) || this.existingParticipations.some(p => 
        p.character && p.character.id === characterId
      );
    },
    availableCharacters(currentIndex) {
      // Filter out characters that are already used in other rows
      return this.characters.filter(character => 
        !this.isCharacterUsed(character.id, currentIndex) || 
        character.id === this.participations[currentIndex].characterId
      );
    },
    checkDuplicateCharacter(index) {
      const participation = this.participations[index];
      const characterId = participation.characterId;
      
      // Clear any previous error
      participation.error = null;
      
      // Check if character is already used in another participation row
      if (this.participations.some((p, i) => i !== index && p.characterId === characterId)) {
        participation.error = "Ce personnage est déjà sélectionné";
      }
      
      // Check if character already has a participation for this event
      if (this.existingParticipations.some(p => p.character && p.character.id === characterId)) {
        participation.error = "Ce personnage participe déjà à cet événement";
      }
    },
    async submitParticipations() {
      if (!this.selectedEventId) {
        this.errorMessage = 'Veuillez sélectionner un événement';
        return;
      }
      
      if (this.participations.length === 0) {
        this.errorMessage = 'Veuillez ajouter au moins une participation';
        return;
      }
      
      // Validate participations
      let hasError = false;
      for (const participation of this.participations) {
        if (!participation.characterId || !participation.position) {
          this.errorMessage = 'Veuillez remplir tous les champs';
          hasError = true;
          break;
        }
        
        if (participation.error) {
          this.errorMessage = 'Veuillez corriger les erreurs avant de soumettre';
          hasError = true;
          break;
        }
      }
      
      if (hasError) return;
      
      try {
        this.isLoading = true;
        this.errorMessage = '';
        
        await this.addBatchParticipations(this.selectedEventId, this.participations);
        
        this.successMessage = 'Participations ajoutées avec succès';
        this.$emit('participationsAdded');
        
        // Reset form
        this.participations = [];
        this.addParticipationRow();
        
        // Refresh existing participations
        await this.fetchExistingParticipations(this.selectedEventId);
        
        this.isLoading = false;
      } catch (error) {
        console.error('Error submitting participations:', error);
        this.errorMessage = 'Erreur lors de l\'ajout des participations';
        this.isLoading = false;
      }
    }
  },
  async mounted() {
    // Check if user has permission to add participations
    if (!this.isAdminOrAnim) {
      this.errorMessage = 'Vous n\'avez pas les permissions nécessaires pour ajouter des résultats.';
      return;
    }
    
    try {
      this.isLoading = true;
      
      // Fetch events
      const eventsData = await this.fetchEvents();
      this.events = eventsData.filter(event => !event.isCompleted);
      
      // Fetch characters
      const response = await axios.get(`${this.API_URL}/characters`);
      this.characters = response.data.filter(char => !char.isArchived);
      
      // If eventId is provided, fetch existing participations
      if (this.eventId) {
        this.selectedEventId = this.eventId;
        await this.fetchExistingParticipations(this.eventId);
      }
      
      this.isLoading = false;
    } catch (error) {
      console.error('Error loading data:', error);
      this.errorMessage = 'Erreur lors du chargement des données';
      this.isLoading = false;
    }
    
    // Initialize with one empty row
    this.addParticipationRow();
  }
};
</script>
