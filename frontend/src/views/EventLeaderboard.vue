<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-theme-text mb-4">Classement des événements</h1>
        <p class="text-theme-text-muted">Découvrez les meilleurs participants aux événements de la guilde</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-theme-primary"></div>
      </div>

      <!-- Leaderboard -->
      <div v-else-if="leaderboard.length > 0" class="bg-theme-card border border-theme-border rounded-xl overflow-hidden shadow-lg">
        <div class="bg-theme-bg-muted border-b border-theme-border px-6 py-4">
          <h2 class="text-2xl font-bold text-theme-text">Top Participants</h2>
        </div>

        <div class="divide-y divide-theme-border">
          <div
            v-for="(entry, index) in leaderboard"
            :key="entry.userId"
            class="p-6 flex items-center justify-between hover:bg-theme-bg-muted transition-colors"
            :class="{
              'bg-gradient-to-r from-yellow-500/20 to-transparent': index === 0,
              'bg-gradient-to-r from-gray-400/20 to-transparent': index === 1,
              'bg-gradient-to-r from-amber-600/20 to-transparent': index === 2
            }"
          >
            <div class="flex items-center space-x-4">
              <!-- Rank Badge -->
              <div class="flex items-center justify-center w-12 h-12 rounded-full font-bold text-lg"
                   :class="{
                     'bg-yellow-500 text-white': index === 0,
                     'bg-gray-400 text-white': index === 1,
                     'bg-amber-600 text-white': index === 2,
                     'bg-theme-primary/20 text-theme-primary': index > 2
                   }">
                {{ index + 1 }}
              </div>

              <!-- User Info -->
              <div>
                <div class="font-semibold text-lg text-theme-text">{{ entry.username }}</div>
                <div class="text-sm text-theme-text-muted">
                  {{ entry.eventsCount }} événement(s) participé(s)
                </div>
              </div>
            </div>

            <!-- Points -->
            <div class="text-right">
              <div class="text-2xl font-bold text-theme-primary">
                {{ Math.round(entry.totalPoints) }} pts
              </div>
              <div class="text-xs text-theme-text-muted">Points totaux</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="w-24 h-24 mx-auto text-theme-text-muted/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
        </svg>
        <p class="text-theme-text-muted text-lg">Aucun classement disponible</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import eventService from '@/services/eventService';

const loading = ref(true);
const leaderboard = ref([]);

const fetchLeaderboard = async () => {
  loading.value = true;
  const result = await eventService.getLeaderboard();
  if (result.success) {
    leaderboard.value = result.data;
  }
  loading.value = false;
};

onMounted(() => {
  fetchLeaderboard();
});
</script>




