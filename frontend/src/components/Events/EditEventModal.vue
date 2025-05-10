<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
      <h3 class="text-xl font-bold text-white mb-4">Modifier l'événement</h3>
      <form @submit.prevent="handleSubmit">
        <div>
          <label for="event-title" class="block text-gray-300 mb-2">Titre</label>
          <input 
            id="event-title"
            v-model="updatedEvent.title"
            type="text"
            class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
            placeholder="Titre de l'événement"
          />
        </div>

        <div>
          <label for="event-description" class="block text-gray-300 mb-2">Description</label>
          <textarea 
            id="event-description"
            v-model="updatedEvent.description"
            class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2 min-h-[150px]"
            placeholder="Description de l'événement"
          ></textarea>
        </div>

        <div>
          <label for="event-date" class="block text-gray-300 mb-2">Date et heure</label>
          <input 
            id="event-date"
            v-model="updatedEvent.eventDate"
            type="datetime-local"
            class="w-full bg-gray-700 text-white border border-gray-600 rounded-md p-2"
          />
        </div>
        
        <!-- Image -->
        <div class="mt-4">
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

        <div class="flex justify-end gap-4 mt-6">
          <button 
            type="button"
            @click="cancel"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
          >
            Annuler
          </button>
          <button 
            type="submit"
            class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-6 py-2 rounded-md font-medium transition-colors duration-300"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Chargement...' : 'Mettre à jour' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    event: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      API_URL: import.meta.env.VITE_API_URL,
      isLoading: false,
      error: null,
      updatedEvent: { ...this.event },
      imagePreview: null,
      newImage: null
    };
  },
  computed: {
    currentEventDate: function() {
      return new Date(this.event.eventDate).toISOString().slice(0, 16);
    }
  },
  mounted() {
    // Set image preview if event has an image
    if (this.event.imageFilename) {
      this.imagePreview = `${this.API_URL}/uploads/events/${this.event.imageFilename}`;
    }
    // Format event date
    this.updatedEvent.eventDate = new Date(this.event.eventDate).toISOString().slice(0, 16);
  },
  methods: {
    handleImageChange(e) {
      const file = e.target.files[0];
      if (!file) return;
      
      this.newImage = file;
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagePreview = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    removeImage() {
      this.newImage = null;
      this.imagePreview = null;
      
      // Reset file input
      const fileInput = document.getElementById('event-image');
      if (fileInput) {
        fileInput.value = '';
      }
    },
    async handleSubmit() {
      try {
        this.isLoading = true;
        this.error = null;
        
          console.log('Submitting event update...');
        
        const updatedData = {
          title: this.updatedEvent.title,
          description: this.updatedEvent.description,
        };

        // Only add eventDate if it has changed
        if (this.updatedEvent.eventDate !== this.event.eventDate) {
          updatedData.eventDate = this.updatedEvent.eventDate;
        }

        let response;

        if (this.newImage) {
          // Use FormData for image upload
          const formData = new FormData();
          formData.append('data', JSON.stringify(updatedData, null, 2));
          
          formData.append('image', this.newImage);
          
          console.log('Using FormData for image upload');
          
          response = await axios.patch(`${this.API_URL}/events/${this.event.id}`, formData);
        } else {
          // Use JSON for regular updates
          console.log('Using JSON for regular updates');
          
          response = await axios.patch(`${this.API_URL}/events/${this.event.id}`, updatedData);
        }
        
        console.log('Response:', response.data);
        
        // Emit the updated event to the parent component
        this.$emit('update', response.data);
        this.$emit('close');
        this.isLoading = false;
      } catch (err) {
        console.error('Error updating event:', err);
        this.error = 'Erreur lors de la mise à jour de l\'événement';
        this.isLoading = false;
      }
    },
    cancel() {
      this.$emit('close');
    }
  }
};
</script>
