<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
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

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-theme-primary"></div>
      </div>

      <!-- Event Content -->
      <div v-else-if="event" class="bg-theme-card border border-theme-border rounded-xl overflow-hidden shadow-lg">
        <!-- Event Image -->
        <div class="relative h-64 md:h-80 bg-gradient-to-br from-theme-primary/20 to-theme-primary/5">
          <img
            v-if="event.image"
            :src="getImageUrl(event.image)"
            :alt="event.title"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="w-32 h-32 text-theme-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div
            class="absolute top-4 right-4 px-4 py-2 rounded-full text-sm font-medium"
            :class="event.isFinished ? 'bg-theme-error/90 text-white' : 'bg-theme-success/90 text-white'"
          >
            {{ event.isFinished ? 'Terminé' : 'À venir' }}
          </div>
        </div>

        <!-- Event Info -->
        <div class="p-8">
          <h1 class="text-4xl font-bold text-theme-text mb-4">{{ event.title }}</h1>

          <!-- Event Meta -->
          <div class="flex flex-wrap gap-6 mb-6 text-theme-text-muted">
            <div class="flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ formatDate(event.date) }}</span>
            </div>
            <div v-if="event.cashPrize" class="flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="font-semibold">{{ event.cashPrize }} kamas</span>
            </div>
            <div class="flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>{{ event.participantsCount || 0 }} participant(s)</span>
            </div>
          </div>

          <!-- Description -->
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-theme-text mb-4">Description</h2>
            <p class="text-theme-text-muted whitespace-pre-wrap">{{ event.description }}</p>
          </div>

          <!-- Upcoming Event Actions -->
          <div v-if="!event.isFinished">
            <div class="flex items-center space-x-4 mb-6">
              <button
                v-if="!event.isSubscribed"
                @click="handleSubscribe"
                :disabled="subscribing"
                class="px-6 py-3 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ subscribing ? 'Inscription...' : 'S\'inscrire' }}</span>
              </button>
              <button
                v-else
                @click="handleUnsubscribe"
                :disabled="unsubscribing"
                class="px-6 py-3 bg-theme-error text-white rounded-lg hover:bg-theme-error/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ unsubscribing ? 'Désinscription...' : 'Se désinscrire' }}</span>
              </button>

              <!-- Owner Actions -->
              <router-link
                v-if="isOwner"
                :to="`/events/${event.id}/manage`"
                class="px-6 py-3 bg-theme-warning text-white rounded-lg hover:bg-theme-warning/90 transition-colors flex items-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Gérer l'événement</span>
              </router-link>
            </div>

            <!-- Participants List -->
            <div v-if="event.participants && event.participants.length > 0">
              <h3 class="text-xl font-bold text-theme-text mb-4">Participants</h3>
              <div class="bg-theme-bg-muted rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div
                    v-for="participant in event.participants"
                    :key="participant.id"
                    class="flex items-center justify-between p-3 bg-theme-card rounded-lg border border-theme-border"
                  >
                    <span class="text-theme-text font-medium">{{ participant.username }}</span>
                    <span class="text-theme-text-muted text-sm">Inscrit le {{ formatDate(participant.subscribedAt) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Finished Event Results -->
          <div v-else>
            <!-- Owner Note -->
            <div v-if="event.note" class="mb-8 p-6 bg-theme-primary/10 border border-theme-primary/20 rounded-lg">
              <h3 class="text-xl font-bold text-theme-text mb-2">Note de l'organisateur</h3>
              <p class="text-theme-text-muted whitespace-pre-wrap">{{ event.note }}</p>
            </div>

            <!-- Results Leaderboard -->
            <div v-if="event.participants && event.participants.length > 0">
              <h3 class="text-2xl font-bold text-theme-text mb-4">Résultats</h3>
              <div class="bg-theme-bg-muted rounded-lg overflow-hidden">
                <div
                  v-for="(participant, index) in sortedParticipants"
                  :key="participant.id"
                  class="p-4 border-b border-theme-border last:border-b-0 flex items-center justify-between"
                  :class="{
                    'bg-gradient-to-r from-yellow-500/20 to-transparent': participant.rank === 1,
                    'bg-gradient-to-r from-gray-400/20 to-transparent': participant.rank === 2,
                    'bg-gradient-to-r from-amber-600/20 to-transparent': participant.rank === 3
                  }"
                >
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-bold"
                         :class="{
                           'bg-yellow-500 text-white': participant.rank === 1,
                           'bg-gray-400 text-white': participant.rank === 2,
                           'bg-amber-600 text-white': participant.rank === 3,
                           'bg-theme-primary/20 text-theme-primary': participant.rank > 3
                         }">
                      {{ participant.rank }}
                    </div>
                    <div>
                      <div class="font-semibold text-theme-text">{{ participant.username }}</div>
                      <div class="text-sm text-theme-text-muted">
                        {{ participant.pointsEarned ? `${participant.pointsEarned} pts` : 'N/A' }}
                      </div>
                    </div>
                  </div>
                  <div v-if="participant.prizeReceived" class="text-right">
                    <div class="font-semibold text-theme-primary">{{ participant.prizeReceived }} kamas</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Owner Actions -->
            <div v-if="isOwner" class="mt-6">
              <router-link
                :to="`/events/${event.id}/manage`"
                class="inline-flex items-center space-x-2 px-6 py-3 bg-theme-warning text-white rounded-lg hover:bg-theme-warning/90 transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Gérer les résultats</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else class="text-center py-12">
        <p class="text-theme-error text-lg">Événement introuvable</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import eventService from '@/services/eventService';

const route = useRoute();
const authStore = useAuthStore();

const loading = ref(true);
const event = ref(null);
const subscribing = ref(false);
const unsubscribing = ref(false);

const isOwner = computed(() => {
  if (!event.value || !authStore.user) return false;
  return event.value.ownerId === authStore.user.id || 
         (authStore.user.roles && authStore.user.roles.includes('ROLE_OWNERS'));
});

const sortedParticipants = computed(() => {
  if (!event.value || !event.value.participants) return [];
  return [...event.value.participants].sort((a, b) => {
    if (a.rank === null && b.rank === null) return 0;
    if (a.rank === null) return 1;
    if (b.rank === null) return -1;
    return a.rank - b.rank;
  });
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

const fetchEvent = async () => {
  loading.value = true;
  const result = await eventService.getEvent(route.params.id);
  if (result.success) {
    event.value = result.data;
  }
  loading.value = false;
};

const handleSubscribe = async () => {
  subscribing.value = true;
  const result = await eventService.subscribeToEvent(route.params.id);
  if (result.success) {
    await fetchEvent(); // Refresh event data
  } else {
    alert(result.error || 'Erreur lors de l\'inscription');
  }
  subscribing.value = false;
};

const handleUnsubscribe = async () => {
  unsubscribing.value = true;
  const result = await eventService.unsubscribeFromEvent(route.params.id);
  if (result.success) {
    await fetchEvent(); // Refresh event data
  } else {
    alert(result.error || 'Erreur lors de la désinscription');
  }
  unsubscribing.value = false;
};

onMounted(() => {
  fetchEvent();
});
</script>

