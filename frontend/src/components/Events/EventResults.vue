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
              <td class="p-4 text-[#b07d46] font-bold text-center align-middle">
                <div class="flex items-center justify-center gap-2">
                  {{ participation.character.pseudo}}
                </div>
              </td>
              <td class="p-4 relative text-center align-middle">
                <div class="relative inline-block">
                  <img
                    :src="getClassIcon(participation.character.class)"
                    alt="Character Class"
                    class="w-14 h-14 cursor-pointer mx-auto"
                  />
                </div>
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

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { fetchEventParticipations } from '@/services/eventServices';

const API_URL = import.meta.env.VITE_API_URL

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
    
    // Fetch character data for each participation
    for (const participation of data) {
      const characterResponse = await axios.get(`${API_URL}/characters/${participation.characterId}`);
      participation.character = characterResponse.data;
    }
    
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

const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });

const classes = {
  sram: images['/src/assets/icon_classe/sram.avif'].default,
  forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
  cra: images['/src/assets/icon_classe/cra.avif'].default,
  ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
  eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
  enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
  feca: images['/src/assets/icon_classe/feca.avif'].default,
  eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
  iop: images['/src/assets/icon_classe/iop.avif'].default,
  osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
  pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
  roublard: images['/src/assets/icon_classe/roublard.avif'].default,
  sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
  sadida: images['/src/assets/icon_classe/sadida.avif'].default,
  steamer: images['/src/assets/icon_classe/steamer.avif'].default,
  xelor: images['/src/assets/icon_classe/xelor.avif'].default,
  zobal: images['/src/assets/icon_classe/zobal.avif'].default,
  huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
  ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
};

const getClassIcon = (characterClass) => {
  if (!characterClass) return classes['sram']; // Default to Sram if class is null/undefined
  
  const normalizedClass = characterClass.toLowerCase();
  return classes[normalizedClass] || classes['sram']; // Default to Sram if class not found
};

const getPositionColor = (position) => {
  if (position === 1) return 'bg-yellow-500 text-black';
  if (position === 2) return 'bg-gray-400 text-black';
  if (position === 3) return 'bg-amber-700 text-white';
  return 'bg-gray-700';
};
</script>
