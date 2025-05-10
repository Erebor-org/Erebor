<template>
  <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 flex flex-col h-full">
    <!-- Event Image -->
    <div class="relative h-48 overflow-hidden">
      <img 
        v-if="imageUrl" 
        :src="imageUrl" 
        :alt="event.title" 
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full bg-gray-700 flex items-center justify-center">
        <span class="text-gray-400 text-lg">No Image</span>
      </div>
      
      <!-- Status Badge -->
      <div 
        v-if="event.isCompleted" 
        class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold"
      >
        Terminé
      </div>
      <div 
        v-else-if="isUpcoming" 
        class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-md text-xs font-bold"
      >
        À venir
      </div>
      <div 
        v-else 
        class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded-md text-xs font-bold"
      >
        En cours
      </div>
    </div>
    
    <!-- Event Content -->
    <div class="p-4 flex-grow">
      <h3 class="text-xl font-bold text-white mb-2">{{ event.title }}</h3>
      <p class="text-gray-300 text-sm mb-4">{{ formattedDate }}</p>
      <p class="text-gray-400 line-clamp-3">{{ event.description }}</p>
    </div>
    
    <!-- Actions -->
    <div v-if="showActions" class="p-4 border-t border-gray-700">
      <div class="flex justify-between">
        <button 
          @click="viewEvent" 
          class="bg-[#93a402] hover:bg-[#7a8a02] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-300"
        >
          Voir
        </button>
        <button 
          v-if="!event.isCompleted" 
          @click="editEvent" 
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-300"
        >
          Modifier
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const props = defineProps({
  event: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['edit']);

const router = useRouter();

const formattedDate = computed(() => {
  if (!props.event.eventDate) return '';
  const date = new Date(props.event.eventDate);
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const isUpcoming = computed(() => {
  if (!props.event.eventDate) return false;
  const eventDate = new Date(props.event.eventDate);
  return eventDate > new Date();
});

const viewEvent = () => {
  router.push(`/events/${props.event.id}`);
};

const editEvent = () => {
  emit('edit', props.event);
};

const imageUrl = computed(() => {
  if (!props.event.imageFilename) return null;
  return `${API_URL}/uploads/events/${props.event.imageFilename}`;
});
</script>
