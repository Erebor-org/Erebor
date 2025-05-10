<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Header Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6"
    >
      <h1 class="text-2xl font-bold text-[#b02e2e] text-center">Classement des Événements</h1>
    </div>
      
    <div v-if="error" class="w-11/12 max-w-7xl bg-red-500 text-white p-4 rounded-md mb-6">
      {{ error }}
    </div>
    
    <div v-if="isLoading" class="w-11/12 max-w-7xl text-center py-12 bg-white border-2 border-[#b07d46] rounded-lg shadow-lg">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#b02e2e]"></div>
      <p class="mt-4 text-[#b07d46]">Chargement du classement...</p>
    </div>
    
    <div v-else-if="standings.length === 0" class="w-11/12 max-w-7xl text-center py-12 bg-white border-2 border-[#b07d46] rounded-lg shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-[#b07d46]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-4 text-xl font-medium text-[#b02e2e]">Aucun résultat disponible</h3>
      <p class="mt-2 text-[#b07d46]">
        Les résultats apparaîtront ici une fois que des événements auront été complétés.
      </p>
    </div>
    
    <div v-else class="w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-6">
      <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">Classement des participants</h2>
      <!-- Podium for top 3 -->
      <div class="flex justify-center items-end mb-12 gap-4">
        <!-- 2nd place -->
        <div v-if="standings.length >= 2" class="w-1/4 flex flex-col items-center">
          <div class="relative mb-2">
            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-400">
              <div class="w-full h-full flex items-center justify-center" :class="getClassColor(standings[1].character.class)">
                <span class="text-white text-xl font-bold">{{ standings[1].character.class.substring(0, 1) }}</span>
              </div>
            </div>
            <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-gray-400 text-black flex items-center justify-center font-bold text-lg">
              2
            </div>
          </div>
          <div class="text-center">
            <h3 class="text-[#b07d46] font-bold text-lg">{{ standings[1].character.pseudo }}</h3>
            <p class="text-[#b02e2e] font-bold text-xl">{{ standings[1].totalPoints }} pts</p>
            <p class="text-[#b07d46] text-sm">{{ standings[1].participationCount }} participations</p>
          </div>
        </div>
        
        <!-- 1st place -->
        <div v-if="standings.length >= 1" class="w-1/3 flex flex-col items-center">
          <div class="relative mb-2">
            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-yellow-500">
              <div class="w-full h-full flex items-center justify-center" :class="getClassColor(standings[0].character.class)">
                <span class="text-white text-2xl font-bold">{{ standings[0].character.class.substring(0, 1) }}</span>
              </div>
            </div>
            <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-full bg-yellow-500 text-black flex items-center justify-center font-bold text-xl">
              1
            </div>
          </div>
          <div class="text-center">
            <h3 class="text-[#b07d46] font-bold text-xl">{{ standings[0].character.pseudo }}</h3>
            <p class="text-[#b02e2e] font-bold text-2xl">{{ standings[0].totalPoints }} pts</p>
            <p class="text-[#b07d46] text-sm">{{ standings[0].participationCount }} participations</p>
          </div>
        </div>
        
        <!-- 3rd place -->
        <div v-if="standings.length >= 3" class="w-1/4 flex flex-col items-center">
          <div class="relative mb-2">
            <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-amber-700">
              <div class="w-full h-full flex items-center justify-center" :class="getClassColor(standings[2].character.class)">
                <span class="text-white text-lg font-bold">{{ standings[2].character.class.substring(0, 1) }}</span>
              </div>
            </div>
            <div class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-amber-700 text-white flex items-center justify-center font-bold text-lg">
              3
            </div>
          </div>
          <div class="text-center">
            <h3 class="text-[#b07d46] font-bold text-lg">{{ standings[2].character.pseudo }}</h3>
            <p class="text-[#b02e2e] font-bold text-xl">{{ standings[2].totalPoints }} pts</p>
            <p class="text-[#b07d46] text-sm">{{ standings[2].participationCount }} participations</p>
          </div>
        </div>
      </div>
      
      <!-- Full standings table -->
      <div class="bg-[#fffaf0] rounded-lg overflow-hidden shadow-lg border-2 border-[#b07d46]">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-[#b02e2e]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Rang</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Personnage</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Classe</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Points</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Participations</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider">Meilleur résultat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#b07d46]">
              <tr v-for="(standing, index) in standings" :key="standing.character.id" class="hover:bg-[#f3d9b1]/30">
                <td class="px-4 py-4 whitespace-nowrap">
                  <div :class="['inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold', getRankColor(index + 1)]">
                    {{ index + 1 }}
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-[#b07d46] font-bold">
                  {{ standing.character.pseudo }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 rounded-md text-xs font-medium text-white', getClassColor(standing.character.class)]">
                    {{ standing.character.class }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="text-[#b02e2e] font-bold">
                    {{ standing.totalPoints }} pts
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-[#b07d46]">
                  {{ standing.participationCount }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div :class="['inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold', getRankColor(standing.bestPosition)]">
                    {{ standing.bestPosition }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  data() {
    return {
      backgroundImage: members_bg,
      standings: [],
      isLoading: false,
      error: null
    };
  },
  methods: {
    async fetchLadderStandings() {
      try {
        this.isLoading = true;
        this.error = null;
        
        const response = await axios.get(`${API_URL}/event-participations/ladder`);
        this.standings = response.data;
        
        this.isLoading = false;
      } catch (err) {
        console.error('Error loading ladder standings:', err);
        this.error = 'Erreur lors du chargement du classement';
        this.isLoading = false;
      }
    },
    getClassColor(characterClass) {
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
    },
    getRankColor(rank) {
      if (rank === 1) return 'bg-yellow-500 text-black';
      if (rank === 2) return 'bg-gray-400 text-black';
      if (rank === 3) return 'bg-amber-700 text-white';
      if (rank <= 10) return 'bg-blue-700 text-white';
      return 'bg-gray-700';
    }
  },
  mounted() {
    this.fetchLadderStandings();
  }
};
</script>
