<script setup>
import { ref, onMounted, watch } from 'vue';
import { fetchEventParticipations } from '@/services/eventServices';

const props = defineProps({
  eventId: {
    type: [Number, String],
    required: true
  }
});

const participations = ref([]);
const isLoading = ref(false);
const error = ref(null);

const loadParticipations = async () => {
  if (!props.eventId) return;
  
  try {
    isLoading.value = true;
    error.value = null;
    
    const data = await fetchEventParticipations(props.eventId);
    participations.value = data;
    
    isLoading.value = false;
  } catch (err) {
    console.error('Error loading participations:', err);
    error.value = 'Erreur lors du chargement des résultats';
    isLoading.value = false;
  }
};

onMounted(() => {
  loadParticipations();
});

watch(() => props.eventId, () => {
  loadParticipations();
});

const getClassColor = (characterClass) => {
  const classColors = {
    'Cra': 'bg-green-700',
    'Ecaflip': 'bg-red-700',
    'Eliotrope': 'bg-purple-700',
    'Eniripsa': 'bg-pink-700',
    'Enutrof': 'bg-yellow-700',
    'Feca': 'bg-blue-700',
    'Forgelance': 'bg-orange-700',
    'Huppermage': 'bg-indigo-700',
    'Iop': 'bg-red-800',
    'Osamodas': 'bg-green-800',
    'Pandawa': 'bg-blue-800',
    'Roublard': 'bg-yellow-800',
    'Sacrieur': 'bg-red-900',
    'Sadida': 'bg-green-900',
    'Sram': 'bg-gray-700',
    'Steamer': 'bg-orange-800',
    'Xelor': 'bg-purple-800',
    'Zobal': 'bg-indigo-800'
  };
  
  return classColors[characterClass] || 'bg-gray-700';
};

const getPositionColor = (position) => {
  if (position === 1) return 'bg-yellow-500 text-black';
  if (position === 2) return 'bg-gray-400 text-black';
  if (position === 3) return 'bg-amber-700 text-white';
  return 'bg-gray-700';
};
</script>

<template>
  <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
    <h2 class="text-2xl font-bold text-white mb-6">Résultats</h2>
    
    <div v-if="error" class="bg-red-500 text-white p-3 rounded-md mb-4">
      {{ error }}
    </div>
    
    <div v-if="isLoading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-[#93a402]"></div>
      <p class="mt-2 text-gray-400">Chargement des résultats...</p>
    </div>
    
    <div v-else-if="participations.length === 0" class="text-center py-8">
      <p class="text-gray-400">Aucun résultat disponible pour cet événement</p>
    </div>
    
    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-gray-900 rounded-lg overflow-hidden">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Position</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Personnage</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Classe</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Points</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="participation in participations" :key="participation.id">
              <td class="px-4 py-4 whitespace-nowrap">
                <div :class="['inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold', getPositionColor(participation.position)]">
                  {{ participation.position }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-white">
                {{ participation.character.pseudo }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span :class="['px-2 py-1 rounded-md text-xs font-medium text-white', getClassColor(participation.character.class)]">
                  {{ participation.character.class }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-[#93a402] font-bold">
                  {{ participation.points }} pts
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
