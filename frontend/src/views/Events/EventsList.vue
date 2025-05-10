<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { fetchEvents, fetchUpcomingEvents, fetchPastEvents } from '@/services/eventServices';
import EventCard from '@/components/Events/EventCard.vue';

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

<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-white">Événements</h1>
      <button 
        @click="createEvent"
        class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-4 py-2 rounded-md font-medium transition-colors duration-300"
      >
        Créer un événement
      </button>
    </div>
    
    <!-- Tabs -->
    <div class="mb-8 border-b border-gray-700">
      <div class="flex space-x-8">
        <button 
          @click="setTab('all')"
          :class="[
            'pb-4 px-1 font-medium text-sm transition-colors duration-300',
            activeTab === 'all' 
              ? 'text-[#93a402] border-b-2 border-[#93a402]' 
              : 'text-gray-400 hover:text-white'
          ]"
        >
          Tous les événements
        </button>
        <button 
          @click="setTab('upcoming')"
          :class="[
            'pb-4 px-1 font-medium text-sm transition-colors duration-300',
            activeTab === 'upcoming' 
              ? 'text-[#93a402] border-b-2 border-[#93a402]' 
              : 'text-gray-400 hover:text-white'
          ]"
        >
          À venir
        </button>
        <button 
          @click="setTab('past')"
          :class="[
            'pb-4 px-1 font-medium text-sm transition-colors duration-300',
            activeTab === 'past' 
              ? 'text-[#93a402] border-b-2 border-[#93a402]' 
              : 'text-gray-400 hover:text-white'
          ]"
        >
          Passés
        </button>
      </div>
    </div>
    
    <div v-if="error" class="bg-red-500 text-white p-4 rounded-md mb-6">
      {{ error }}
    </div>
    
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#93a402]"></div>
      <p class="mt-4 text-gray-400">Chargement des événements...</p>
    </div>
    
    <div v-else-if="displayedEvents().length === 0" class="text-center py-12 bg-gray-800 rounded-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-4 text-xl font-medium text-gray-400">Aucun événement trouvé</h3>
      <p class="mt-2 text-gray-500">
        {{ activeTab === 'upcoming' ? 'Aucun événement à venir' : activeTab === 'past' ? 'Aucun événement passé' : 'Aucun événement disponible' }}
      </p>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <EventCard 
        v-for="event in displayedEvents()" 
        :key="event.id" 
        :event="event"
      />
    </div>
  </div>
</template>
