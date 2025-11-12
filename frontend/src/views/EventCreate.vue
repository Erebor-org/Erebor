<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
      <!-- Back Button -->
      <router-link
        to="/events"
        class="inline-flex items-center space-x-2 text-theme-text-muted hover:text-theme-primary mb-6 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span>Retour aux événements</span>
      </router-link>

      <!-- Form -->
      <div class="bg-theme-card border border-theme-border rounded-xl p-8 shadow-lg">
        <h1 class="text-4xl font-bold text-theme-text mb-8">Créer un événement</h1>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-theme-text mb-2">Titre *</label>
            <input
              v-model="formData.title"
              type="text"
              required
              class="w-full px-4 py-3 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary"
              placeholder="Ex: Quiz Dofus"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-theme-text mb-2">Description *</label>
            <textarea
              v-model="formData.description"
              rows="6"
              required
              class="w-full px-4 py-3 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary resize-none"
              placeholder="Décrivez votre événement..."
            ></textarea>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-theme-text mb-2">Date et heure *</label>
            <DatePicker
              v-model="formData.date"
              :include-time="true"
              placeholder="Sélectionner la date et l'heure de l'événement"
            />
          </div>

          <!-- Cash Prize -->
          <div>
            <label class="block text-sm font-medium text-theme-text mb-2">Prix (kamas)</label>
            <input
              v-model.number="formData.cashPrize"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-3 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary"
              placeholder="0"
            />
          </div>

          <!-- Image Upload -->
          <div>
            <label class="block text-sm font-medium text-theme-text mb-2">Image</label>
            <div class="space-y-4">
              <!-- File Input -->
              <div
                @click="triggerFileInput"
                class="border-2 border-dashed border-theme-border rounded-lg p-8 text-center cursor-pointer hover:border-theme-primary transition-colors"
                :class="{ 'border-theme-primary': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
              >
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                  @change="handleFileSelect"
                  class="hidden"
                />
                <svg class="w-12 h-12 mx-auto text-theme-text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-theme-text mb-2">
                  <span class="text-theme-primary font-medium">Cliquez pour téléverser</span>
                  <span class="text-theme-text-muted"> ou glissez-déposez</span>
                </p>
                <p class="text-sm text-theme-text-muted">PNG, JPG, GIF ou WEBP (max 5MB)</p>
              </div>

              <!-- Image Preview -->
              <div v-if="imagePreview" class="relative">
                <img :src="imagePreview" alt="Preview" class="w-full h-64 object-cover rounded-lg border border-theme-border" />
                <button
                  @click="removeImage"
                  class="absolute top-2 right-2 p-2 bg-theme-error text-white rounded-full hover:bg-theme-error/90 transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Upload Progress -->
              <div v-if="uploading" class="space-y-2">
                <div class="flex items-center justify-between text-sm text-theme-text-muted">
                  <span>Upload en cours...</span>
                  <span>{{ uploadProgress }}%</span>
                </div>
                <div class="w-full bg-theme-bg-muted rounded-full h-2">
                  <div
                    class="bg-theme-primary h-2 rounded-full transition-all duration-300"
                    :style="{ width: uploadProgress + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-4 pt-4">
            <button
              type="submit"
              :disabled="creating"
              class="flex-1 px-6 py-3 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ creating ? 'Création...' : 'Créer l\'événement' }}
            </button>
            <router-link
              to="/events"
              class="px-6 py-3 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border transition-colors"
            >
              Annuler
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/config/axios';
import eventService from '@/services/eventService';
import DatePicker from '@/components/DatePicker.vue';

const API_URL = import.meta.env.VITE_API_URL;
const router = useRouter();
const fileInput = ref(null);

const creating = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);
const imagePreview = ref(null);
const selectedFile = ref(null);

const formData = ref({
  title: '',
  description: '',
  date: '',
  cashPrize: null,
  image: ''
});

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    processFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    processFile(file);
  }
};

const processFile = (file) => {
  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    alert('Le fichier est trop volumineux. Taille maximale: 5MB');
    return;
  }

  selectedFile.value = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  selectedFile.value = null;
  imagePreview.value = null;
  formData.value.image = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const uploadImage = async () => {
  if (!selectedFile.value) return null;

  uploading.value = true;
  uploadProgress.value = 0;

  try {
    const formData = new FormData();
    formData.append('image', selectedFile.value);

    const response = await axios.post(`${API_URL}/api/upload/event-image`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
      },
    });

    uploading.value = false;
    uploadProgress.value = 0;
    return response.data.url;
  } catch (error) {
    uploading.value = false;
    uploadProgress.value = 0;
    throw new Error(error.response?.data?.error || 'Erreur lors de l\'upload de l\'image');
  }
};

const handleSubmit = async () => {
  if (!formData.value.date) {
    alert('Veuillez sélectionner une date');
    return;
  }

  creating.value = true;

  try {
    // Upload image first if selected
    if (selectedFile.value) {
      try {
        const imageUrl = await uploadImage();
        formData.value.image = imageUrl;
      } catch (error) {
        alert(error.message);
        creating.value = false;
        return;
      }
    }

    // Format date for API
    const submitData = {
      ...formData.value,
      date: new Date(formData.value.date).toISOString()
    };

    const result = await eventService.createEvent(submitData);

    if (result.success) {
      router.push(`/events/${result.data.id}`);
    } else {
      alert(result.error || 'Erreur lors de la création de l\'événement');
    }
  } catch (error) {
    alert('Erreur lors de la création de l\'événement');
  } finally {
    creating.value = false;
  }
};
</script>

