<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
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
      
      <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
        <h1 class="text-2xl font-bold text-white mb-6">
          {{ isEditMode ? 'Modifier l\'événement' : 'Créer un nouvel événement' }}
        </h1>
        
        <div v-if="error" class="bg-red-500 text-white p-3 rounded-md mb-6">
          {{ error }}
        </div>
        
        <div v-if="success" class="bg-green-500 text-white p-3 rounded-md mb-6">
          {{ success }}
        </div>
        
        <div v-if="isLoading && isEditMode" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-[#93a402]"></div>
          <p class="mt-2 text-gray-400">Chargement de l'événement...</p>
        </div>
        
        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Title -->
          <div>
            <label for="event-title" class="block text-gray-300 mb-2">Titre</label>
            <input 
              id="event-title"
              v-model="event.title"
              type="text"
              class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
              placeholder="Titre de l'événement"
              :disabled="isLoading"
            />
          </div>
          
          <!-- Description -->
          <div>
            <label for="event-description" class="block text-gray-300 mb-2">Description</label>
            <textarea 
              id="event-description"
              v-model="event.description"
              class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2 min-h-[150px]"
              placeholder="Description de l'événement"
              :disabled="isLoading"
            ></textarea>
          </div>
          
          <!-- Date -->
          <div>
            <label for="event-date" class="block text-gray-300 mb-2">Date et heure</label>
            <input 
              id="event-date"
              v-model="event.eventDate"
              type="datetime-local"
              class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
              :disabled="isLoading"
            />
          </div>
          
          <!-- Image -->
          <div>
            <label for="event-image" class="block text-gray-300 mb-2">Image</label>
            
            <div v-if="imagePreview" class="mb-4">
              <div class="relative w-full h-48 rounded-lg overflow-hidden">
                <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                <button 
                  type="button"
                  @click="removeImage"
                  class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-1 rounded-full"
                  :disabled="isLoading"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            
            <input 
              id="event-image"
              type="file"
              accept="image/*"
              @change="handleImageChange"
              class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
              :disabled="isLoading"
            />
            <p class="text-gray-400 text-sm mt-1">Format recommandé: JPG, PNG. Taille max: 2MB</p>
          </div>
          
          <!-- Buttons -->
          <div class="flex justify-end gap-4 pt-4">
            <button 
              type="button"
              @click="cancelForm"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
              :disabled="isLoading"
            >
              Annuler
            </button>
            <button 
              type="submit"
              class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-6 py-2 rounded-md font-medium transition-colors duration-300"
              :disabled="isLoading"
            >
              <span v-if="isLoading">Chargement...</span>
              <span v-else>{{ isEditMode ? 'Mettre à jour' : 'Créer' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchEventById, createEvent, updateEvent } from '@/services/eventServices';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';
const route = useRoute();
const router = useRouter();

const isEditMode = computed(() => route.name === 'EventEdit');
const eventId = computed(() => route.params.id);

const event = ref({
  title: '',
  description: '',
  eventDate: new Date().toISOString().slice(0, 16),
  image: null
});

const imagePreview = ref(null);
const isLoading = ref(false);
const error = ref(null);
const success = ref(null);

onMounted(async () => {
  if (isEditMode.value) {
    try {
      isLoading.value = true;
      
      const data = await fetchEventById(eventId.value);
      
      event.value = {
        title: data.title,
        description: data.description,
        eventDate: new Date(data.eventDate).toISOString().slice(0, 16),
        image: null
      };
      
      if (data.imageFilename) {
        imagePreview.value = `${API_URL}/uploads/events/${data.imageFilename}`;
      }
      
      isLoading.value = false;
    } catch (err) {
      console.error('Error loading event:', err);
      error.value = 'Erreur lors du chargement de l\'événement';
      isLoading.value = false;
    }
  }
});

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  event.value.image = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  event.value.image = null;
  imagePreview.value = null;
  
  // Reset file input
  const fileInput = document.getElementById('event-image');
  if (fileInput) {
    fileInput.value = '';
  }
};

const validateForm = () => {
  if (!event.value.title.trim()) {
    error.value = 'Le titre est requis';
    return false;
  }
  
  if (!event.value.description.trim()) {
    error.value = 'La description est requise';
    return false;
  }
  
  if (!event.value.eventDate) {
    error.value = 'La date est requise';
    return false;
  }
  
  return true;
};

const handleSubmit = async () => {
  error.value = null;
  success.value = null;
  
  if (!validateForm()) return;
  
  try {
    isLoading.value = true;
    
    if (isEditMode.value) {
      await updateEvent(eventId.value, event.value);
      success.value = 'Événement mis à jour avec succès';
    } else {
      const newEvent = await createEvent(event.value);
      success.value = 'Événement créé avec succès';
      
      // Redirect to event details after a short delay
      setTimeout(() => {
        router.push(`/events/${newEvent.id}`);
      }, 1500);
    }
    
    isLoading.value = false;
  } catch (err) {
    console.error('Error saving event:', err);
    error.value = 'Erreur lors de l\'enregistrement de l\'événement';
    isLoading.value = false;
  }
};

const cancelForm = () => {
  if (isEditMode.value) {
    router.push(`/events/${eventId.value}`);
  } else {
    router.push('/events');
  }
};
</script>
