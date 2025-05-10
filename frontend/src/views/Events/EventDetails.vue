<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchEventById, deleteEvent, completeEvent } from '@/services/eventServices';
import EventResults from '@/components/Events/EventResults.vue';
import ParticipationForm from '@/components/Events/ParticipationForm.vue';

const route = useRoute();
const router = useRouter();
const event = ref(null);
const isLoading = ref(false);
const error = ref(null);
const showDeleteConfirm = ref(false);
const showCompleteConfirm = ref(false);
const showParticipationForm = ref(false);

const eventId = computed(() => route.params.id);

const loadEvent = async () => {
  try {
    isLoading.value = true;
    error.value = null;
    
    const data = await fetchEventById(eventId.value);
    event.value = data;
    
    isLoading.value = false;
  } catch (err) {
    console.error('Error loading event:', err);
    error.value = 'Erreur lors du chargement de l\'événement';
    isLoading.value = false;
  }
};

onMounted(() => {
  loadEvent();
});

const formattedDate = computed(() => {
  if (!event.value?.eventDate) return '';
  const date = new Date(event.value.eventDate);
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const isUpcoming = computed(() => {
  if (!event.value?.eventDate) return false;
  const eventDate = new Date(event.value.eventDate);
  return eventDate > new Date();
});

const imageUrl = computed(() => {
  if (!event.value?.imageFilename) return null;
  return `http://localhost:8000/uploads/events/${event.value.imageFilename}`;
});

const editEvent = () => {
  router.push(`/events/${eventId.value}/edit`);
};

const confirmDelete = () => {
  showDeleteConfirm.value = true;
};

const cancelDelete = () => {
  showDeleteConfirm.value = false;
};

const confirmComplete = () => {
  showCompleteConfirm.value = true;
};

const cancelComplete = () => {
  showCompleteConfirm.value = false;
};

const handleDelete = async () => {
  try {
    isLoading.value = true;
    
    await deleteEvent(eventId.value);
    
    router.push('/events');
  } catch (err) {
    console.error('Error deleting event:', err);
    error.value = 'Erreur lors de la suppression de l\'événement';
    isLoading.value = false;
  }
};

const handleComplete = async () => {
  try {
    isLoading.value = true;
    showCompleteConfirm.value = false;
    
    const updatedEvent = await completeEvent(eventId.value);
    event.value = updatedEvent;
    
    isLoading.value = false;
  } catch (err) {
    console.error('Error completing event:', err);
    error.value = 'Erreur lors de la complétion de l\'événement';
    isLoading.value = false;
  }
};

const toggleParticipationForm = () => {
  showParticipationForm.value = !showParticipationForm.value;
};

const handleParticipationsAdded = () => {
  loadEvent();
  showParticipationForm.value = false;
};
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="error" class="bg-red-500 text-white p-4 rounded-md mb-6">
      {{ error }}
    </div>
    
    <div v-if="isLoading && !event" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#93a402]"></div>
      <p class="mt-4 text-gray-400">Chargement de l'événement...</p>
    </div>
    
    <template v-else-if="event">
      <!-- Back button -->
      <div class="mb-6">
        <button 
          @click="router.push('/events')"
          class="flex items-center text-gray-400 hover:text-white transition-colors duration-300"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Retour aux événements
        </button>
      </div>
      
      <!-- Event header -->
      <div class="flex flex-col md:flex-row gap-8 mb-8">
        <!-- Event image -->
        <div class="w-full md:w-1/3 h-64 rounded-lg overflow-hidden">
          <img 
            v-if="imageUrl" 
            :src="imageUrl" 
            :alt="event.title" 
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full bg-gray-700 flex items-center justify-center">
            <span class="text-gray-400 text-lg">No Image</span>
          </div>
        </div>
        
        <!-- Event details -->
        <div class="w-full md:w-2/3">
          <div class="flex justify-between items-start">
            <div>
              <h1 class="text-3xl font-bold text-white mb-2">{{ event.title }}</h1>
              <p class="text-gray-400 mb-4">{{ formattedDate }}</p>
            </div>
            
            <!-- Status badge -->
            <div 
              v-if="event.isCompleted" 
              class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-bold"
            >
              Terminé
            </div>
            <div 
              v-else-if="isUpcoming" 
              class="bg-green-500 text-white px-3 py-1 rounded-md text-sm font-bold"
            >
              À venir
            </div>
            <div 
              v-else 
              class="bg-yellow-500 text-white px-3 py-1 rounded-md text-sm font-bold"
            >
              En cours
            </div>
          </div>
          
          <div class="mt-6 prose prose-invert max-w-none">
            <p>{{ event.description }}</p>
          </div>
          
          <!-- Action buttons -->
          <div class="mt-8 flex flex-wrap gap-4">
            <button 
              v-if="!event.isCompleted"
              @click="editEvent"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              Modifier
            </button>
            
            <button 
              v-if="!event.isCompleted"
              @click="confirmComplete"
              class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              Marquer comme terminé
            </button>
            
            <button 
              v-if="!event.isCompleted"
              @click="toggleParticipationForm"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              {{ showParticipationForm ? 'Masquer le formulaire' : 'Ajouter des résultats' }}
            </button>
            
            <button 
              @click="confirmDelete"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              Supprimer
            </button>
          </div>
        </div>
      </div>
      
      <!-- Participation form -->
      <div v-if="showParticipationForm" class="mb-8">
        <ParticipationForm 
          :eventId="eventId" 
          @participations-added="handleParticipationsAdded"
        />
      </div>
      
      <!-- Event results -->
      <EventResults :eventId="eventId" />
      
      <!-- Delete confirmation modal -->
      <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
          <h3 class="text-xl font-bold text-white mb-4">Confirmer la suppression</h3>
          <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.</p>
          <div class="flex justify-end gap-4">
            <button 
              @click="cancelDelete"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              Annuler
            </button>
            <button 
              @click="handleDelete"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Suppression...' : 'Supprimer' }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Complete confirmation modal -->
      <div v-if="showCompleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
          <h3 class="text-xl font-bold text-white mb-4">Confirmer la complétion</h3>
          <p class="text-gray-300 mb-6">Êtes-vous sûr de vouloir marquer cet événement comme terminé ?</p>
          <div class="flex justify-end gap-4">
            <button 
              @click="cancelComplete"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
            >
              Annuler
            </button>
            <button 
              @click="handleComplete"
              class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Chargement...' : 'Confirmer' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
