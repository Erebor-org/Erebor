<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Header Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6"
    >
      <!-- Tabs (Top of the Header) -->
      <div class="flex justify-center mb-4">
        <div class="flex space-x-4">
          <button
            @click="setTab('all')"
            :class="{
              'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'all',
              'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'all',
            }"
            class="px-6 py-2 rounded-full font-bold transition-all duration-300"
          >
            Tous les événements
          </button>
          <button
            @click="setTab('upcoming')"
            :class="{
              'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'upcoming',
              'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'upcoming',
            }"
            class="px-6 py-2 rounded-full font-bold transition-all duration-300"
          >
            À venir
          </button>
          <button
            @click="setTab('past')"
            :class="{
              'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'past',
              'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'past',
            }"
            class="px-6 py-2 rounded-full font-bold transition-all duration-300"
          >
            Passés
          </button>
        </div>
      </div>

      <!-- Bottom Section: Search Bar (Left) and Button (Right) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <h1 class="text-2xl font-bold text-[#b02e2e] mb-4 md:mb-0">Événements</h1>
        
        <!-- Add Event Button -->
        <button
          @click="createEvent"
          class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
        >
          Créer un événement
        </button>
      </div>
    </div>
    
    <div v-if="error" class="w-11/12 max-w-7xl bg-red-500 text-white p-4 rounded-md mb-6">
      {{ error }}
    </div>
    
    <div v-if="isLoading" class="w-11/12 max-w-7xl text-center py-12 bg-white border-2 border-[#b07d46] rounded-lg shadow-lg">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#b02e2e]"></div>
      <p class="mt-4 text-[#b07d46]">Chargement des événements...</p>
    </div>
    
    <div v-else-if="displayedEvents().length === 0" class="w-11/12 max-w-7xl text-center py-12 bg-white border-2 border-[#b07d46] rounded-lg shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-[#b07d46]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-4 text-xl font-medium text-[#b02e2e]">Aucun événement trouvé</h3>
      <p class="mt-2 text-[#b07d46]">
        {{ activeTab === 'upcoming' ? 'Aucun événement à venir' : activeTab === 'past' ? 'Aucun événement passé' : 'Aucun événement disponible' }}
      </p>
    </div>
    
    <div v-else class="w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6">
      <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">
        {{ activeTab === 'upcoming' ? 'Événements à venir' : activeTab === 'past' ? 'Événements passés' : 'Tous les événements' }}
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <EventCard 
          v-for="event in displayedEvents()" 
          :key="event.id" 
          :event="event"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { fetchEvents, fetchUpcomingEvents, fetchPastEvents } from '@/services/eventServices';
import EventCard from '@/components/Events/EventCard.vue';
import members_bg from '@/assets/members_bg.webp';

const backgroundImage = members_bg;

const router = useRouter();
const events = ref([]);
const upcomingEvents = ref([]);
const pastEvents = ref([]);
const isLoading = ref(false);
const error = ref(null);
const activeTab = ref('all'); // 'all', 'upcoming', 'past'

const loadEvents = async () => {
  try {
    isLoading.value = true;
    error.value = null;
    
    const [allEvents, upcoming, past] = await Promise.all([
      fetchEvents(),
      fetchUpcomingEvents(),
      fetchPastEvents()
    ]);
    
    events.value = allEvents;
    upcomingEvents.value = upcoming;
    pastEvents.value = past;
    
    isLoading.value = false;
  } catch (err) {
    console.error('Error loading events:', err);
    error.value = 'Erreur lors du chargement des événements';
    isLoading.value = false;
  }
};

onMounted(() => {
  loadEvents();
});

const createEvent = () => {
  router.push('/events/create');
};

const displayedEvents = () => {
  switch (activeTab.value) {
    case 'upcoming':
      return upcomingEvents.value;
    case 'past':
      return pastEvents.value;
    default:
      return events.value;
  }
};

const setTab = (tab) => {
  activeTab.value = tab;
};
</script>
