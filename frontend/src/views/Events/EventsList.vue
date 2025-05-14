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
          v-if="isAdminOrAnim"
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
          :isAdminOrAnim="isAdminOrAnim"
          @edit="handleEdit"
        />
      </div>
    </div>
    
    <!-- Edit Event Modal -->
    <teleport to="body">
      <EditEventModal 
        v-if="showEditModal && selectedEvent"
        :event="selectedEvent"
        @close="showEditModal = false"
        @update="handleEventUpdate"
      />
    </teleport>
  </div>
</template>

<script>
import axios from 'axios';
import EventCard from '@/components/Events/EventCard.vue';
import EditEventModal from '@/components/Events/EditEventModal.vue';
import members_bg from '@/assets/members_bg.webp';
import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL;



export default {
  components: {
    EventCard,
    EditEventModal
  },
  data() {
    return {
      backgroundImage: members_bg,
      events: [],
      upcomingEvents: [],
      pastEvents: [],
      isLoading: false,
      error: null,
      activeTab: 'all', // 'all', 'upcoming', 'past'
      selectedEvent: null,
      isAdminOrAnim: false,
      showEditModal: false
    };
  },
  methods: {
    async loadEvents() {
      try {
        this.isLoading = true;
        this.error = null;
        
        const [allEvents, upcoming, past] = await Promise.all([
          axios.get(`${API_URL}/events`),
          axios.get(`${API_URL}/events/upcoming`),
          axios.get(`${API_URL}/events/past`)
        ]);
        
        this.events = allEvents.data;
        this.upcomingEvents = upcoming.data;
        this.pastEvents = past.data;
        
        this.isLoading = false;
      } catch (err) {
        console.error('Error loading events:', err);
        this.error = 'Erreur lors du chargement des événements';
        this.isLoading = false;
      }
    },
    
    createEvent() {
      this.$router.push('/events/create');
    },
    setAdminOrAnim() {
      const authStore = useAuthStore();
      const user = authStore.user;
      console.log("user", user)
      // Check if user has either ROLE_ADMIN or ROLE_ANIM
      return user?.roles?.includes('ROLE_ADMIN') || user?.roles?.includes('ROLE_ANIM');
    },
    displayedEvents() {
      switch (this.activeTab) {
        case 'upcoming':
          return this.upcomingEvents;
        case 'past':
          return this.pastEvents;
        default:
          return this.events;
      }
    },
    
    setTab(tab) {
      this.activeTab = tab;
    },
    
    handleEdit(event) {
      this.selectedEvent = event;
      this.showEditModal = true;
    },
    
    handleEventUpdate(updatedEvent) {
      // Update the event in the appropriate arrays
      const updateEventInArray = (array) => {
        const index = array.findIndex(e => e.id === updatedEvent.id);
        if (index !== -1) {
          array[index] = updatedEvent;
        }
      };
      
      updateEventInArray(this.events);
      updateEventInArray(this.upcomingEvents);
      updateEventInArray(this.pastEvents);
    }
  },
  mounted() {
    this.loadEvents();
    this.isAdminOrAnim = this.setAdminOrAnim();
  }
};
</script>
