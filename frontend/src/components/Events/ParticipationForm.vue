<script setup>
import { ref, onMounted, computed } from 'vue';
import { fetchEvents } from '@/services/eventServices';
import { addBatchParticipations } from '@/services/eventServices';
const API_URL = import.meta.env.VITE_API_URL;

const props = defineProps({
  eventId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['participationsAdded']);

const events = ref([]);
const characters = ref([]);
const selectedEventId = ref(props.eventId || '');
const participations = ref([]);
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Formula 1 scoring system
const scoringSystem = {
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
};

const maxPosition = computed(() => {
  return Math.max(...Object.keys(scoringSystem).map(Number));
});

onMounted(async () => {
  try {
    isLoading.value = true;
    
    // Fetch events
    const eventsData = await fetchEvents();
    events.value = eventsData.filter(event => !event.isCompleted);
    
    // Fetch characters
    const response = await fetch(`${API_URL}/characters`);
    const data = await response.json();
    characters.value = data.filter(char => !char.isArchived);
    
    isLoading.value = false;
  } catch (error) {
    console.error('Error loading data:', error);
    errorMessage.value = 'Erreur lors du chargement des données';
    isLoading.value = false;
  }
});

const addParticipationRow = () => {
  participations.value.push({
    characterId: '',
    position: '',
    points: 0
  });
};

const removeParticipationRow = (index) => {
  participations.value.splice(index, 1);
};

const updatePoints = (participation) => {
  const position = parseInt(participation.position);
  participation.points = scoringSystem[position] || 0;
};

const submitParticipations = async () => {
  if (!selectedEventId.value) {
    errorMessage.value = 'Veuillez sélectionner un événement';
    return;
  }
  
  if (participations.value.length === 0) {
    errorMessage.value = 'Veuillez ajouter au moins une participation';
    return;
  }
  
  // Validate participations
  for (const participation of participations.value) {
    if (!participation.characterId || !participation.position) {
      errorMessage.value = 'Veuillez remplir tous les champs';
      return;
    }
  }
  
  try {
    isLoading.value = true;
    errorMessage.value = '';
    
    await addBatchParticipations(selectedEventId.value, participations.value);
    
    successMessage.value = 'Participations ajoutées avec succès';
    emit('participationsAdded');
    
    // Reset form
    participations.value = [];
    
    isLoading.value = false;
  } catch (error) {
    console.error('Error submitting participations:', error);
    errorMessage.value = 'Erreur lors de l\'ajout des participations';
    isLoading.value = false;
  }
};

// Initialize with one empty row
addParticipationRow();
</script>

<template>
  <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
    <h2 class="text-2xl font-bold text-white mb-6">Ajouter des résultats</h2>
    
    <div v-if="errorMessage" class="bg-red-500 text-white p-3 rounded-md mb-4">
      {{ errorMessage }}
    </div>
    
    <div v-if="successMessage" class="bg-green-500 text-white p-3 rounded-md mb-4">
      {{ successMessage }}
    </div>
    
    <div class="mb-6">
      <label class="block text-gray-300 mb-2">Événement</label>
      <select 
        v-model="selectedEventId"
        class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
        :disabled="props.eventId || isLoading"
      >
        <option value="" disabled>Sélectionner un événement</option>
        <option v-for="event in events" :key="event.id" :value="event.id">
          {{ event.title }} ({{ new Date(event.eventDate).toLocaleDateString() }})
        </option>
      </select>
    </div>
    
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold text-white">Participants</h3>
        <button 
          @click="addParticipationRow"
          class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-3 py-1 rounded-md text-sm"
          :disabled="isLoading"
        >
          Ajouter un participant
        </button>
      </div>
      
      <div v-if="participations.length === 0" class="text-gray-400 text-center py-4">
        Aucun participant ajouté
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="(participation, index) in participations" 
          :key="index"
          class="flex items-center gap-4 p-3 bg-gray-700 rounded-md"
        >
          <div class="flex-grow">
            <label class="block text-gray-300 mb-1 text-sm">Personnage</label>
            <select 
              v-model="participation.characterId"
              class="w-full bg-gray-600 text-white border border-gray-500 rounded-md p-2"
              :disabled="isLoading"
            >
              <option value="" disabled>Sélectionner un personnage</option>
              <option v-for="character in characters" :key="character.id" :value="character.id">
                {{ character.pseudo }} ({{ character.class }})
              </option>
            </select>
          </div>
          
          <div class="w-24">
            <label class="block text-gray-300 mb-1 text-sm">Position</label>
            <select 
              v-model="participation.position"
              class="w-full bg-gray-600 text-white border border-gray-500 rounded-md p-2"
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
            <label class="block text-gray-300 mb-1 text-sm">Points</label>
            <div class="bg-gray-600 text-white border border-gray-500 rounded-md p-2 text-center">
              {{ participation.points }}
            </div>
          </div>
          
          <button 
            @click="removeParticipationRow(index)"
            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md"
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
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium"
        :disabled="isLoading"
      >
        <span v-if="isLoading">Chargement...</span>
        <span v-else>Enregistrer les résultats</span>
      </button>
    </div>
  </div>
</template>
