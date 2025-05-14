<template>
  <div 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="cancel"
  >
    <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 max-w-md w-full relative">
      <button 
        type="button"
        @click="cancel"
        class="absolute top-2 right-2 bg-[#b02e2e] hover:bg-[#942828] text-white p-1 rounded-full"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <h3 class="text-xl font-bold text-[#b02e2e] mb-4">Modifier l'événement</h3>
      
      <!-- Error notification -->
      <div v-if="error" class="bg-red-500 text-white p-4 rounded-md mb-4">
        {{ error }}
      </div>
      <form @submit.prevent="handleSubmit">
        <div>
          <label for="event-title" class="block text-[#b07d46] font-bold mb-2">Titre</label>
          <input 
            id="event-title"
            v-model="updatedEvent.title"
            type="text"
            class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            placeholder="Titre de l'événement"
          />
        </div>

        <div>
          <label for="event-description" class="block text-[#b07d46] font-bold mb-2">Description</label>
          <textarea 
            id="event-description"
            v-model="updatedEvent.description"
            class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 min-h-[150px] focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            placeholder="Description de l'événement"
          ></textarea>
        </div>

        <div>
          <label for="event-date" class="block text-[#b07d46] font-bold mb-2">Date et heure</label>
          <input 
            id="event-date"
            v-model="updatedEvent.eventDate"
            type="datetime-local"
            class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
          />
        </div>
        
        <!-- Image -->
        <div class="mt-4">
          <label for="event-image" class="block text-[#b07d46] font-bold mb-2">Image</label>
          
          <div v-if="imagePreview" class="mb-4">
            <div class="relative w-full h-48 rounded-lg overflow-hidden border-2 border-[#b07d46]">
              <img :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
              <button 
                type="button"
                @click="removeImage"
                class="absolute top-2 right-2 bg-[#b02e2e] hover:bg-[#942828] text-white p-1 rounded-full"
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
            class="w-full bg-white border-2 border-[#b07d46] rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            :disabled="isLoading"
          />
          <p class="text-[#b07d46] text-sm mt-1">Format recommandé: JPG, PNG. Taille max: 2MB</p>
        </div>

        <div class="flex justify-end gap-4 mt-6">
          <button 
            type="button"
            @click="cancel"
            class="bg-[#b07d46] hover:bg-[#9c682e] text-[#fff5e6] px-4 py-2 rounded-full font-medium transition-colors duration-300"
          >
            Annuler
          </button>
          <button 
            type="submit"
            class="bg-[#b02e2e] hover:bg-[#942828] text-[#f3d9b1] px-6 py-2 rounded-full font-medium transition-colors duration-300"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Chargement...' : 'Mettre à jour' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const props = defineProps({
  event: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update', 'close']);

const API_URL = import.meta.env.VITE_API_URL;
const isLoading = ref(false);
const error = ref(null);
const updatedEvent = ref({ ...props.event });
const imagePreview = ref(null);
const newImage = ref(null);

// Initialize with proper date format

onMounted(() => {
  // Check permissions first
  const authStore = useAuthStore();
  const user = authStore.user;
  const isAdminOrAnim = user?.roles?.includes('ROLE_ADMIN') || user?.roles?.includes('ROLE_ANIM');
  
  if (!isAdminOrAnim) {
    error.value = 'Vous n\'avez pas les permissions nécessaires pour modifier cet événement.';
    setTimeout(() => {
      emit('close');
    }, 2000);
    return;
  }
  
  if (props.event.imageFilename) {
    imagePreview.value = `${API_URL}/uploads/events/${props.event.imageFilename}`;
  }
  updatedEvent.value.eventDate = new Date(props.event.eventDate).toISOString().slice(0, 16);
});

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  newImage.value = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  newImage.value = null;
  imagePreview.value = null;

  const fileInput = document.getElementById('event-image');
  if (fileInput) fileInput.value = '';
};

const handleSubmit = async () => {
  // Check user permissions
  const authStore = useAuthStore();
  const user = authStore.user;
  const isAdminOrAnim = user?.roles?.includes('ROLE_ADMIN') || user?.roles?.includes('ROLE_ANIM');
  
  if (!isAdminOrAnim) {
    error.value = 'Vous n\'avez pas les permissions nécessaires pour modifier cet événement.';
    return;
  }
  
  try {
    isLoading.value = true;
    error.value = null;

    const updatedData = {
      title: updatedEvent.value.title,
      description: updatedEvent.value.description,
    };

    if (updatedEvent.value.eventDate !== props.event.eventDate) {
      updatedData.eventDate = updatedEvent.value.eventDate;
    }

    let response;

    if (newImage.value) {
      const formData = new FormData();
      formData.append('data', JSON.stringify(updatedData, null, 2));
      formData.append('image', newImage.value);
      response = await axios.patch(`${API_URL}/events/${props.event.id}`, formData);
    } else {
      response = await axios.patch(`${API_URL}/events/${props.event.id}`, updatedData);
    }

    emit('update', response.data);
    emit('close');
    isLoading.value = false;
  } catch (err) {
    console.error('Error updating event:', err);
    error.value = 'Erreur lors de la mise à jour de l\'événement';
    isLoading.value = false;
  }
};

const cancel = () => {
  // Check if user has permission before allowing the modal to even be displayed
  const authStore = useAuthStore();
  const user = authStore.user;
  const isAdminOrAnim = user?.roles?.includes('ROLE_ADMIN') || user?.roles?.includes('ROLE_ANIM');
  
  if (!isAdminOrAnim) {
    error.value = 'Vous n\'avez pas les permissions nécessaires pour modifier cet événement.';
    return;
  }

  emit('close');
};
</script>
