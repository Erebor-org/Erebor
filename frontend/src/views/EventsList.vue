<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-4xl font-bold text-theme-text">Événements</h1>
          <router-link
            v-if="canCreateEvent"
            to="/events/create"
            class="px-6 py-3 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Créer un événement</span>
          </router-link>
        </div>
        <p class="text-theme-text-muted">Découvrez tous les événements de la guilde</p>
      </div>

      <!-- Tabs -->
      <div class="flex space-x-4 mb-6 border-b border-theme-border">
        <button
          @click="activeTab = 'all'"
          :class="[
            'px-6 py-3 font-medium transition-colors border-b-2',
            activeTab === 'all'
              ? 'border-theme-primary text-theme-primary'
              : 'border-transparent text-theme-text-muted hover:text-theme-text'
          ]"
        >
          Tous
        </button>
        <button
          @click="activeTab = 'upcoming'"
          :class="[
            'px-6 py-3 font-medium transition-colors border-b-2',
            activeTab === 'upcoming'
              ? 'border-theme-primary text-theme-primary'
              : 'border-transparent text-theme-text-muted hover:text-theme-text'
          ]"
        >
          À venir
        </button>
        <button
          @click="activeTab = 'past'"
          :class="[
            'px-6 py-3 font-medium transition-colors border-b-2',
            activeTab === 'past'
              ? 'border-theme-primary text-theme-primary'
              : 'border-transparent text-theme-text-muted hover:text-theme-text'
          ]"
        >
          Passés
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-theme-primary"></div>
      </div>

      <!-- Events Grid -->
      <div v-else-if="filteredEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link
          v-for="event in filteredEvents"
          :key="event.id"
          :to="`/events/${event.id}`"
          class="group bg-theme-card border border-theme-border rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
        >
          <!-- Event Image -->
          <div class="relative h-48 bg-gradient-to-br from-theme-primary/20 to-theme-primary/5 overflow-hidden">
            <img
              v-if="event.image"
              :src="getImageUrl(event.image)"
              :alt="event.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-20 h-20 text-theme-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div
              class="absolute top-4 right-4 px-3 py-1 rounded-full text-sm font-medium"
              :class="event.isFinished ? 'bg-theme-error/20 text-theme-error' : 'bg-theme-success/20 text-theme-success'"
            >
              {{ event.isFinished ? 'Terminé' : 'À venir' }}
            </div>
          </div>

          <!-- Event Content -->
          <div class="p-6">
            <h3 class="text-xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">
              {{ event.title }}
            </h3>
            <p class="text-theme-text-muted text-sm mb-4 line-clamp-2">
              {{ event.description }}
            </p>
            
            <!-- Event Details -->
            <div class="flex flex-wrap gap-4 text-sm text-theme-text-muted mb-4">
              <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ formatDate(event.date) }}</span>
              </div>
              <div v-if="event.cashPrize" class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ event.cashPrize }} kamas</span>
              </div>
            </div>

            <!-- Arrow -->
            <div class="flex items-center text-theme-primary group-hover:translate-x-2 transition-transform">
              <span class="text-sm font-medium">Voir plus</span>
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-theme-text-muted/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-theme-text-muted text-lg">Aucun événement trouvé</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import eventService from '@/services/eventService';

const authStore = useAuthStore();
const loading = ref(true);
const events = ref([]);
const activeTab = ref('all');

const canCreateEvent = computed(() => {
  const user = authStore.user;
  if (!user || !user.roles) return false;
  // Can create if user has roles other than just ROLE_USER
  return user.roles.some(role => role !== 'ROLE_USER');
});

const filteredEvents = computed(() => {
  if (activeTab.value === 'upcoming') {
    return events.value.filter(e => !e.isFinished && new Date(e.date) >= new Date());
  } else if (activeTab.value === 'past') {
    return events.value.filter(e => e.isFinished || new Date(e.date) < new Date());
  }
  return events.value;
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  // If it's already a full URL, return it
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  // Otherwise, prepend the API URL
  const API_URL = import.meta.env.VITE_API_URL;
  return `${API_URL}${imagePath}`;
};

const fetchEvents = async () => {
  loading.value = true;
  const result = await eventService.getAllEvents();
  if (result.success) {
    events.value = result.data;
  }
  loading.value = false;
};

onMounted(() => {
  fetchEvents();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

