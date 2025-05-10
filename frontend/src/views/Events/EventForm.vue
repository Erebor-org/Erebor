<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
      <div class="mb-6">
        <button 
          @click="$router.push('/events')"
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
          <!-- Organizer -->
          <EventOrganizerSelector 
            v-model="event.organizerId"
            :characters="characters"
            :is-loading="isLoading"
            label="Sélectionner l'organisateur"
            :classes="classes"
          />

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

<script>
import axios from 'axios';
import EventOrganizerSelector from '@/components/Events/EventOrganizerSelector.vue';

export default {
  components: {
    EventOrganizerSelector
  },
  data() {
    const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
    
    return {
      API_URL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
      event: {
        organizerId: '',
        title: '',
        description: '',
        eventDate: new Date().toISOString().slice(0, 16),
        image: null
      },
      characters: [],
      imagePreview: null,
      isLoading: false,
      error: null,
      success: null,
      classes: {
        sram: images['/src/assets/icon_classe/sram.avif'].default,
        forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
        cra: images['/src/assets/icon_classe/cra.avif'].default,
        ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
        eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
        enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
        feca: images['/src/assets/icon_classe/feca.avif'].default,
        eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
        iop: images['/src/assets/icon_classe/iop.avif'].default,
        osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
        pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
        roublard: images['/src/assets/icon_classe/roublard.avif'].default,
        sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
        sadida: images['/src/assets/icon_classe/sadida.avif'].default,
        steamer: images['/src/assets/icon_classe/steamer.avif'].default,
        xelor: images['/src/assets/icon_classe/xelor.avif'].default,
        zobal: images['/src/assets/icon_classe/zobal.avif'].default,
        huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
        ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
      }
    };
  },
  computed: {
    isEditMode() {
      return this.$route.name === 'EventEdit';
    },
    eventId() {
      return this.$route.params.id;
    }
  },
  methods: {
    async fetchEventById(id) {
      try {
        const response = await axios.get(`${this.API_URL}/events/${id}`);
        return response.data;
      } catch (error) {
        console.error(`Error fetching event with id ${id}:`, error);
        throw error;
      }
    },
    async createEvent(eventData) {
      try {
        const formData = new FormData();
        formData.append('data', JSON.stringify({
          title: eventData.title,
          description: eventData.description,
          eventDate: eventData.eventDate,
          organizerId: eventData.organizerId
        }));
        
        if (eventData.image) {
          formData.append('image', eventData.image);
        }
        
        const response = await axios.post(`${this.API_URL}/events`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        return response.data;
      } catch (error) {
        console.error('Error creating event:', error);
        throw error;
      }
    },
    async updateEvent(id, eventData) {
      try {
        const formData = new FormData();
        formData.append('data', JSON.stringify({
          title: eventData.title,
          description: eventData.description,
          eventDate: eventData.eventDate,
          organizerId: eventData.organizerId
        }));
        
        if (eventData.image) {
          formData.append('image', eventData.image);
        }
        
        const response = await axios.put(`${this.API_URL}/events/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        return response.data;
      } catch (error) {
        console.error(`Error updating event with id ${id}:`, error);
        throw error;
      }
    },
    handleImageChange(e) {
      const file = e.target.files[0];
      if (!file) return;
      
      this.event.image = file;
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagePreview = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    removeImage() {
      this.event.image = null;
      this.imagePreview = null;
      
      // Reset file input
      const fileInput = document.getElementById('event-image');
      if (fileInput) {
        fileInput.value = '';
      }
    },
    validateForm() {
      if (!this.event.organizerId) {
        this.error = 'L\'organisateur est requis';
        return false;
      }

      if (!this.event.title.trim()) {
        this.error = 'Le titre est requis';
        return false;
      }
      
      if (!this.event.description.trim()) {
        this.error = 'La description est requise';
        return false;
      }
      
      if (!this.event.eventDate) {
        this.error = 'La date est requise';
        return false;
      }
      
      return true;
    },
    async handleSubmit() {
      this.error = null;
      this.success = null;
      
      if (!this.validateForm()) return;
      
      try {
        this.isLoading = true;
        
        if (this.isEditMode) {
          await this.updateEvent(this.eventId, this.event);
          this.success = 'Événement mis à jour avec succès';
        } else {
          const newEvent = await this.createEvent(this.event);
          this.success = 'Événement créé avec succès';
          
          // Redirect to event details after a short delay
          setTimeout(() => {
            this.$router.push(`/events/${newEvent.id}`);
          }, 1500);
        }
        
        this.isLoading = false;
      } catch (err) {
        console.error('Error saving event:', err);
        this.error = 'Erreur lors de l\'enregistrement de l\'événement';
        this.isLoading = false;
      }
    },
    cancelForm() {
      if (this.isEditMode) {
        this.$router.push(`/events/${this.eventId}`);
      } else {
        this.$router.push('/events');
      }
    }
  },
  async mounted() {
    try {
      this.isLoading = true;

      // Fetch characters for organizer selector
      const response = await axios.get(`${this.API_URL}/characters`);
      this.characters = response.data.filter(char => !char.isArchived);

      if (this.isEditMode) {
        const data = await this.fetchEventById(this.eventId);
        this.event = {
          organizerId: data.organizer.id,
          title: data.title,
          description: data.description,
          eventDate: new Date(data.eventDate).toISOString().slice(0, 16),
          image: null
        };

        if (data.imageFilename) {
          this.imagePreview = `${this.API_URL}/uploads/events/${data.imageFilename}`;
        }
      }

      this.isLoading = false;
    } catch (err) {
      console.error('Error loading data:', err);
      this.error = 'Erreur lors du chargement des données';
      this.isLoading = false;
    }
  }
};
</script>
