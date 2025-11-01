<template>
  <div class="min-h-screen bg-theme-bg text-theme-text p-6">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-theme-primary mb-4">👤 Mon Profil</h1>
        <p class="text-theme-text-muted text-lg">Gérez votre personnage et vos mules</p>
      </div>

      <!-- Character Card -->
      <div v-if="user?.character" class="bg-theme-card border border-theme-border rounded-2xl p-8 shadow-2xl hover:border-theme-primary/50 transition-all duration-300">
        <!-- Character Header -->
        <div class="flex items-start justify-between mb-8">
          <div class="flex items-center space-x-5">
            <!-- Class Icon with Dropdown -->
            <ClassDropdown
              :class-name="user.character.class"
              :classes="classes"
              :entity-id="user.character.id"
              :entity-type="'character'"
              @update-class="updateCharacterClass"
            />
            <div>
              <h3 class="text-3xl font-bold text-theme-primary mb-2 drop-shadow-lg">
                {{ user.character.pseudo }}
              </h3>
              <p class="text-theme-text-muted">
                {{ user.character.ankamaPseudo }}
              </p>
            </div>
          </div>
        </div>

        <!-- Character Details -->
        <div class="grid grid-cols-2 gap-6 mb-8">
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Classe</p>
            <p class="text-sm text-theme-text font-medium capitalize">{{ user.character.class }}</p>
          </div>
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Rang</p>
            <p class="text-sm text-theme-text font-medium">{{ user.character.rank?.name || 'Aucun' }}</p>
          </div>
        </div>

        <!-- New Rank Unlocked Banner -->
        <div v-if="nextRankProgress?.justUnlockedRank" class="mb-8 relative overflow-hidden rounded-2xl p-8 bg-gradient-to-r from-theme-success/20 via-theme-warning/20 to-theme-success/20 border-2 border-theme-success animate-pulse">
          <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer"></div>
          <div class="relative z-10 text-center">
            <div class="text-6xl mb-4 animate-bounce">🎉</div>
            <h3 class="text-3xl font-bold text-theme-success mb-2 animate-pulse">
              Nouveau Rang Débloqué !
            </h3>
            <p class="text-2xl font-bold text-theme-primary mb-4">
              {{ nextRankProgress.achievedRankName }}
            </p>
            <p class="text-theme-text-muted">
              Félicitations ! Vous avez atteint ce rang après {{ nextRankProgress.totalDays }} jours dans la guilde !
            </p>
          </div>
        </div>

        <!-- Next Rank Countdown -->
        <div v-if="nextRankProgress" class="mb-8 p-6 bg-gradient-to-r from-theme-primary/10 to-theme-warning/10 rounded-xl border-2 border-theme-primary/30">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <span class="text-2xl">⏰</span>
              <div>
                <p class="text-sm font-semibold text-theme-text uppercase tracking-wider">Prochain Rang</p>
                <p class="text-lg font-bold text-theme-primary">{{ nextRankProgress.nextRankName }}</p>
              </div>
            </div>
            <div class="text-right">
              <div v-if="nextRankProgress.daysRemaining > 0" class="text-3xl font-bold text-theme-primary">
                {{ nextRankProgress.daysRemaining }}
              </div>
              <div v-else class="text-2xl font-bold text-theme-success">
                ✓ Dispo
              </div>
              <p class="text-xs text-theme-text-muted mt-1">jour<span v-if="nextRankProgress.daysRemaining !== 1">s</span></p>
            </div>
          </div>
          
          <!-- Progress Bar -->
          <div v-if="nextRankProgress.daysRemaining > 0" class="relative h-3 bg-theme-bg-muted rounded-full overflow-hidden">
            <div 
              :class="[
                'absolute top-0 left-0 h-full bg-gradient-to-r from-theme-primary to-theme-warning',
                nextRankProgress.showAnimation ? 'transition-all duration-1000 ease-out' : ''
              ]"
              :style="{ width: `${nextRankProgress.progressPercentage}%` }"
            ></div>
          </div>
          
          <!-- Detailed Time -->
          <div v-if="nextRankProgress.daysRemaining > 0" class="mt-4 flex items-center justify-center gap-4 text-xs text-theme-text-muted">
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ nextRankProgress.totalDays }} jour{{ nextRankProgress.totalDays !== 1 ? 's' : '' }} dans la guilde</span>
            </div>
            <span>•</span>
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <span>{{ nextRankProgress.requiredDays }} jour{{ nextRankProgress.requiredDays !== 1 ? 's' : '' }} requis</span>
            </div>
          </div>
        </div>

        <!-- Mules Section -->
        <div v-if="user.character.mules && user.character.mules.length > 0" class="mt-8 pt-8 border-t border-theme-border">
          <h3 class="text-xl font-bold text-theme-primary mb-4 flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Mes Mules ({{ user.character.mules.length }})
          </h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div
              v-for="mule in user.character.mules"
              :key="mule.id"
              class="bg-theme-bg-muted rounded-xl p-4 border border-theme-border hover:border-theme-primary transition-all duration-300"
            >
              <div class="flex flex-col items-center text-center">
                <ClassDropdown
                  :class-name="mule.class"
                  :classes="classes"
                  :entity-id="mule.id"
                  :entity-type="'mule'"
                  @update-class="updateMuleClass"
                />
                <p class="text-sm font-semibold text-theme-text mt-2">{{ mule.pseudo }}</p>
                <p class="text-xs text-theme-text-muted">{{ mule.ankamaPseudo }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Character Message -->
      <div v-else class="bg-theme-card rounded-2xl p-12 shadow-2xl border border-theme-border text-center">
        <div class="text-6xl mb-4">👤</div>
        <h2 class="text-2xl font-bold text-theme-primary mb-2">Aucun personnage</h2>
        <p class="text-theme-text-muted">Aucun personnage principal n'est associé à votre compte.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import axios from '@/config/axios';
import ClassDropdown from '@/components/ClassDropdown.vue';

const API_URL = import.meta.env.VITE_API_URL;

// Import class icons
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

const authStore = useAuthStore();
const user = computed(() => authStore.user);
const allRanks = ref([]);

// Compute next rank progress
const nextRankProgress = computed(() => {
  if (!user.value?.character || !allRanks.value.length) return null;
  
  const character = user.value.character;
  const currentRank = character.rank;
  
  if (!currentRank || !character.recruitedAt) return null;
  
  // Only show countdown for ranks that need updates
  if (!currentRank.needUpdate) return null;
  
  // Calculate days in guild
  const recruitedDate = new Date(character.recruitedAt);
  const today = new Date();
  const totalDays = Math.floor((today - recruitedDate) / (1000 * 60 * 60 * 24));
  
  // Sort ranks by requiredDays in ascending order (lower ranks to higher ranks)
  const sortedRanks = [...allRanks.value].sort((a, b) => (a.requiredDays || 0) - (b.requiredDays || 0));
  
  // Find the highest rank the user has achieved based on their total days
  const ranksWithDays = sortedRanks.filter(r => r.requiredDays !== null);
  let achievedRank = ranksWithDays[0]; // Start with the lowest rank
  for (const rank of ranksWithDays) {
    if (totalDays >= rank.requiredDays && rank.requiredDays > achievedRank.requiredDays) {
      achievedRank = rank;
    }
  }
  
  // Find next rank (one with higher requiredDays than achieved rank)
  const nextRank = ranksWithDays.find(rank => rank.requiredDays > achievedRank.requiredDays);
  
  if (!nextRank || !nextRank.requiredDays) return null;
  
  const requiredDays = nextRank.requiredDays;
  const daysRemaining = Math.max(0, requiredDays - totalDays);
  const progressPercentage = Math.min(100, (totalDays / requiredDays) * 100);
  
  // Calculate days since reaching achieved rank
  const daysInCurrentRank = Math.max(0, totalDays - (achievedRank.requiredDays || 0));
  const showAnimation = daysInCurrentRank <= 2; // Show animation for 2 days after rank change
  
  // Check if the user has exactly reached the achieved rank
  const justUnlockedRank = totalDays >= achievedRank.requiredDays && daysInCurrentRank <= 2;
  
  return {
    nextRankName: nextRank.name,
    achievedRankName: achievedRank.name,
    totalDays,
    requiredDays,
    daysRemaining,
    progressPercentage,
    showAnimation,
    justUnlockedRank
  };
});

// Fetch all ranks
const fetchAllRanks = async () => {
  try {
    const { data } = await axios.get(`${API_URL}/ranks`);
    allRanks.value = data;
  } catch (error) {
    console.error('Error fetching ranks:', error);
  }
};

// Update character class
const updateCharacterClass = async (characterId, newClass) => {
  try {
    await axios.put(`${API_URL}/characters/${characterId}/update-class`, {
      class: newClass,
    });

    // Update the character's class locally
    if (user.value?.character) {
      user.value.character.class = newClass;
    }

    // Show success notification
    console.log('Classe mise à jour avec succès !');
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la classe:', error.message);
  }
};

// Update mule class
const updateMuleClass = async (muleId, newClass) => {
  try {
    await axios.put(`${API_URL}/mules/${muleId}/update-class`, {
      class: newClass,
    });
    
    // Update the mule's class locally for instant feedback
    if (user.value?.character?.mules) {
      const mule = user.value.character.mules.find(m => m.id === muleId);
      if (mule) {
        mule.class = newClass;
      }
    }
    
    console.log('Mule class updated successfully!');
  } catch (error) {
    console.error('Error updating mule class:', error.message);
  }
};

// Fetch user profile on mount to ensure we have the latest rank data
onMounted(async () => {
  await Promise.all([
    authStore.fetchUserProfile(),
    fetchAllRanks()
  ]);
});
</script>

<style scoped>
/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>

