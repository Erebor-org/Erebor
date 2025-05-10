<template>
  <div class="bg-white border-2 border-[#b07d46] rounded-lg p-6 shadow-lg">
    <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">Résultats</h2>

    <div v-if="error" class="bg-red-500 text-white p-3 rounded-md mb-4">
      {{ error }}
    </div>

    <div v-if="isLoading" class="text-center py-8">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-[#b02e2e]"
      ></div>
      <p class="mt-2 text-[#b07d46]">Chargement des résultats...</p>
    </div>

    <div v-else-if="participations.length === 0" class="text-center py-8">
      <p class="text-[#b07d46]">Aucun résultat disponible pour cet événement</p>
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg overflow-hidden">
          <thead>
            <tr class="bg-[#b02e2e]">
              <th
                class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider"
              >
                Position
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider"
              >
                Personnage
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider"
              >
                Classe
              </th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-[#f3d9b1] uppercase tracking-wider"
              >
                Points
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#b07d46]/20">
            <tr
              v-for="participation in participations"
              :key="participation.id"
              class="hover:bg-[#f3d9b1]/30"
            >
              <td class="px-4 py-4 whitespace-nowrap">
                <div
                  :class="[
                    'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
                    getPositionColor(participation.position),
                  ]"
                >
                  {{ participation.position }}
                </div>
              </td>
              <td class="p-4 text-[#b07d46] font-bold text-center align-middle">
                <div class="flex items-center justify-center gap-2">
                  {{ participation.characterName || `Position ${participation.position}` }}
                </div>
              </td>
              <td class="p-4 relative text-center align-middle">
                <div v-if="participation.characterClass" class="relative inline-block">
                  <img
                    :src="getClassIcon(participation.characterClass)"
                    alt="Character Class"
                    class="w-14 h-14 cursor-pointer mx-auto"
                  />
                </div>
                <div v-else class="text-[#b07d46]">Position {{ participation.position }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-[#93a402] font-bold">{{ participation.points }} pts</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    eventId: {
      type: [Number, String],
      required: true,
    },
    refreshKey: {
      type: Number,
      required: true,
    },
  },
  data() {
    const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });

    return {
      API_URL: import.meta.env.VITE_API_URL,
      participations: [],
      isLoading: false,
      error: null,
      classes: {
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
      },
    };
  },
  methods: {
    async loadParticipations() {
      if (!this.eventId) return;

      try {
        this.isLoading = true;
        this.error = null;

        const response = await axios.get(
          `${this.API_URL}/event-participations/event/${this.eventId}`
        );
        this.participations = response.data;

        // Debug the response
        console.log('Participations loaded:', this.participations);

        this.isLoading = false;
      } catch (err) {
        console.error('Error loading participations:', err);
        this.error = 'Erreur lors du chargement des résultats';
        this.isLoading = false;
      }
    },
    getClassIcon(characterClass) {
      if (!characterClass) return this.classes['sram']; // Default to Sram if class is null/undefined

      const normalizedClass = characterClass.toLowerCase();
      return this.classes[normalizedClass] || this.classes['sram']; // Default to Sram if class not found
    },
    getPositionColor(position) {
      if (position === 1) return 'bg-yellow-500 text-black';
      if (position === 2) return 'bg-gray-400 text-black';
      if (position === 3) return 'bg-amber-700 text-white';
      return 'bg-[#b07d46]/20 text-[#b07d46]';
    },
  },
  mounted() {
    this.loadParticipations();
  },
  watch: {
    eventId() {
      this.loadParticipations();
    },
    refreshKey() {
      this.loadParticipations(); // 👈 call the data reload when key changes
    },
  },
};
</script>
