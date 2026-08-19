<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Mon Profil</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Gérez votre personnage et vos mules</p>
      </div>

      <div class="max-w-2xl mx-auto">
        <!-- Character Card -->
        <div v-if="user?.character" class="glass-card rounded-2xl p-8">
          <div class="flex flex-col items-center text-center">
            <div class="profile-portrait-wrap">
              <ClassDropdown
                :class-name="user.character.class"
                :classes="classes"
                :entity-id="user.character.id"
                :entity-type="'character'"
                size="lg"
                @update-class="updateCharacterClass"
              />
              <span class="profile-rank-pill"><RankBadge :rank="user.character.rank" size="sm" /></span>
            </div>

            <div
              v-if="!isEditing('character', user.character.id)"
              class="profile-name-row"
              @click="startEditing('character', user.character.id, user.character.pseudo)"
              title="Modifier le pseudo"
            >
              <h2 class="profile-name">{{ user.character.pseudo }}</h2>
              <svg class="profile-name-edit-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </div>
            <input
              v-else
              :ref="setPseudoInputEl"
              v-model="pseudoDraft"
              class="profile-name-input"
              placeholder="Nouveau pseudo"
              @keydown.enter.prevent="savePseudoEdit"
              @keydown.esc="cancelPseudoEdit"
              @blur="savePseudoEdit"
            />
            <button
              @click="ankamaVisible = !ankamaVisible"
              class="profile-ankama"
              :title="ankamaVisible ? 'Masquer Ankama ID' : 'Afficher Ankama ID'"
            >
              <span>{{ ankamaVisible ? user.character.ankamaPseudo : '••••••••' }}</span>
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            <p class="text-xs text-theme-text-muted mt-1">Le pseudo Ankama ne peut pas être modifié</p>

            <!-- Détails -->
            <div class="grid grid-cols-2 gap-3 w-full mt-6">
              <div class="profile-tile">
                <span class="l">Classe</span>
                <span class="v capitalize">{{ user.character.class }}</span>
              </div>
              <div class="profile-tile">
                <span class="l">Rang</span>
                <span class="v">{{ user.character.rank?.name || 'Aucun' }}</span>
              </div>
            </div>
          </div>

          <!-- New Rank Unlocked Banner -->
          <div v-if="nextRankProgress?.justUnlockedRank" class="profile-unlock-banner mt-6">
            <div class="text-3xl mb-2">🎉</div>
            <h3 class="text-xl font-serif font-bold brand-gradient-text mb-1">Nouveau rang débloqué !</h3>
            <p class="text-lg font-bold text-theme-primary mb-1">{{ nextRankProgress.achievedRankName }}</p>
            <p class="text-sm text-theme-text-muted">
              Félicitations, vous avez atteint ce rang après {{ nextRankProgress.achievedRankRequiredDays }} jour{{ nextRankProgress.achievedRankRequiredDays !== 1 ? 's' : '' }} dans la guilde !
            </p>
          </div>

          <!-- Next Rank Countdown -->
          <div v-if="nextRankProgress" class="profile-progress mt-6">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="stat-icon-chip">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-theme-text-muted">Prochain rang</p>
                  <p class="font-serif font-bold text-theme-primary">{{ nextRankProgress.nextRankName }}</p>
                </div>
              </div>
              <div class="text-right">
                <div v-if="nextRankProgress.daysRemaining > 0" class="text-2xl font-bold text-theme-primary">
                  {{ nextRankProgress.daysRemaining }}
                </div>
                <div v-else class="text-lg font-bold text-theme-success">✓ Dispo</div>
                <p class="text-xs text-theme-text-muted">jour<span v-if="nextRankProgress.daysRemaining !== 1">s</span></p>
              </div>
            </div>

            <div v-if="nextRankProgress.daysRemaining > 0" class="stat-bar stat-bar--wide">
              <div class="stat-bar-fill" :style="{ width: `${nextRankProgress.progressPercentage}%` }"></div>
            </div>

            <div v-if="nextRankProgress.daysRemaining > 0" class="mt-3 flex items-center justify-center gap-4 text-xs text-theme-text-muted">
              <div class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ nextRankProgress.totalDays }} jour{{ nextRankProgress.totalDays !== 1 ? 's' : '' }} dans la guilde</span>
              </div>
              <span>•</span>
              <div class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                <span>{{ nextRankProgress.requiredDays }} jour{{ nextRankProgress.requiredDays !== 1 ? 's' : '' }} requis</span>
              </div>
            </div>
          </div>

          <!-- Mules Section -->
          <div v-if="user.character.mules && user.character.mules.length > 0" class="mt-8 pt-6 border-t border-theme-border">
            <span class="text-xs font-bold uppercase tracking-wide text-theme-text-muted mb-3 block">Mes mules ({{ user.character.mules.length }})</span>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <div v-for="mule in user.character.mules" :key="mule.id" class="profile-mule-tile">
                <ClassDropdown
                  :class-name="mule.class"
                  :classes="classes"
                  :entity-id="mule.id"
                  :entity-type="'mule'"
                  size="sm"
                  @update-class="updateMuleClass"
                />
                <div
                  v-if="!isEditing('mule', mule.id)"
                  class="profile-mule-name-row"
                  @click="startEditing('mule', mule.id, mule.pseudo)"
                  title="Modifier le pseudo"
                >
                  <p class="text-sm font-semibold text-theme-text">{{ mule.pseudo }}</p>
                  <svg class="profile-mule-edit-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </div>
                <input
                  v-else
                  :ref="setPseudoInputEl"
                  v-model="pseudoDraft"
                  class="profile-mule-name-input"
                  placeholder="Nouveau pseudo"
                  @keydown.enter.prevent="savePseudoEdit"
                  @keydown.esc="cancelPseudoEdit"
                  @blur="savePseudoEdit"
                />
                <p class="text-xs text-theme-text-muted font-mono mt-1">{{ mule.ankamaPseudo }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- No Character Message -->
        <div v-else class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
          <svg class="w-16 h-16 mx-auto mb-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
          <p class="text-lg font-medium mb-1">Aucun personnage</p>
          <p class="text-sm">Aucun personnage principal n'est associé à votre compte</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, nextTick, ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import axios from '@/config/axios';
import ClassDropdown from '@/components/ClassDropdown.vue';
import RankBadge from '@/components/RankBadge.vue';

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
const ankamaVisible = ref(false);
// Édition de pseudo (personnage principal ou mule) : un seul champ actif à
// la fois, identifié par { type, id }.
const editingTarget = ref(null);
const pseudoDraft = ref('');
// Ref "fonction" plutôt que ref nommée : la même chaîne "ref" utilisée à la
// fois hors v-for (pseudo du personnage) et dans un v-for (pseudo des mules)
// pousse Vue à transformer la valeur en tableau pour l'usage en v-for, ce qui
// casse .focus()/.select() pour l'un des deux cas selon lequel est monté.
let pseudoInputEl = null;
function setPseudoInputEl(el) {
  pseudoInputEl = el;
}

function isEditing(type, id) {
  return editingTarget.value?.type === type && editingTarget.value?.id === id;
}

function startEditing(type, id, currentPseudo) {
  editingTarget.value = { type, id };
  pseudoDraft.value = currentPseudo || '';
  nextTick(() => {
    pseudoInputEl?.focus();
    pseudoInputEl?.select();
  });
}

function cancelPseudoEdit() {
  editingTarget.value = null;
}

async function savePseudoEdit() {
  if (!editingTarget.value) return;
  const { type, id } = editingTarget.value;
  editingTarget.value = null;

  const newPseudo = pseudoDraft.value.trim();
  if (!newPseudo) return;

  try {
    if (type === 'character') {
      const character = user.value?.character;
      if (!character || newPseudo === character.pseudo) return;
      await axios.put(`${API_URL}/characters/${id}/update-pseudo`, { pseudo: newPseudo });
      character.pseudo = newPseudo;
    } else if (type === 'mule') {
      const mule = user.value?.character?.mules?.find(m => m.id === id);
      if (!mule || newPseudo === mule.pseudo) return;
      await axios.put(`${API_URL}/mules/${id}/update-pseudo`, { pseudo: newPseudo });
      mule.pseudo = newPseudo;
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du pseudo:', error.response?.data || error.message);
  }
}

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

  // Show the banner for a week after the rank is reached, then stop.
  // The starting rank (everyone's default on joining) is never a real promotion, so it never gets a banner.
  const daysInCurrentRank = totalDays - achievedRank.requiredDays;
  const isStartingRank = achievedRank === ranksWithDays[0];
  const justUnlockedRank = !isStartingRank && daysInCurrentRank >= 0 && daysInCurrentRank < 7;

  return {
    nextRankName: nextRank.name,
    achievedRankName: achievedRank.name,
    achievedRankRequiredDays: achievedRank.requiredDays,
    totalDays,
    requiredDays,
    daysRemaining,
    progressPercentage,
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
.stat-icon-chip {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
}

.stat-bar {
  width: 8rem;
  height: 0.4rem;
  border-radius: 9999px;
  background-color: var(--bg-muted);
  overflow: hidden;
}
.stat-bar--wide {
  width: 100%;
}
.stat-bar-fill {
  height: 100%;
  border-radius: 9999px;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  transition: width 0.3s ease;
}

.profile-portrait-wrap {
  position: relative;
  margin-bottom: 0.75rem;
}

.profile-rank-pill {
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--card);
  border: 1px solid var(--border);
  border-radius: 9999px;
  padding: 0.2rem 0.65rem;
  white-space: nowrap;
}

.profile-name {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.6rem;
  color: var(--primary);
  margin: 0.7rem 0 0.35rem;
}

.profile-name-row {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  border-radius: 0.6rem;
  padding: 0.15rem 0.5rem;
  margin: 0.55rem -0.5rem 0.2rem;
  transition: background-color 0.2s;
}
.profile-name-row:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.profile-name-edit-icon {
  width: 1rem;
  height: 1rem;
  color: var(--text-muted);
  opacity: 0;
  transition: opacity 0.2s;
  flex-shrink: 0;
}
.profile-name-row:hover .profile-name-edit-icon {
  opacity: 1;
}

.profile-name-input {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.6rem;
  color: var(--primary);
  background-color: var(--bg-muted);
  border: 2px solid var(--primary);
  border-radius: 0.6rem;
  padding: 0.2rem 0.75rem;
  margin: 0.55rem 0 0.2rem;
  text-align: center;
  width: 100%;
  max-width: 16rem;
}
.profile-name-input:focus {
  outline: none;
}

.profile-ankama {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.78rem;
  color: var(--text-muted);
  transition: color 0.2s;
}
.profile-ankama:hover {
  color: var(--primary);
}

.profile-tile {
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  padding: 0.75rem;
  text-align: center;
  transition: border-color 0.2s, background-color 0.2s;
}
.profile-tile:hover {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}
.profile-tile .l {
  display: block;
  font-size: 0.65rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-muted);
}
.profile-tile .v {
  display: block;
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--text);
  margin-top: 0.2rem;
}

.profile-unlock-banner {
  text-align: center;
  padding: 1.75rem;
  border-radius: 1rem;
  background-color: rgba(var(--accent-rgb), 0.08);
  border: 1px solid rgba(var(--accent-rgb), 0.35);
}

.profile-progress {
  padding: 1.25rem;
  border-radius: 1rem;
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
}

.profile-mule-tile {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  padding: 0.9rem;
  transition: border-color 0.2s, background-color 0.2s;
}
.profile-mule-tile:hover {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}

.profile-mule-name-row {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
  border-radius: 0.5rem;
  padding: 0.1rem 0.4rem;
  margin: 0.5rem -0.4rem 0;
  transition: background-color 0.2s;
}
.profile-mule-name-row:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.profile-mule-edit-icon {
  width: 0.8rem;
  height: 0.8rem;
  color: var(--text-muted);
  opacity: 0;
  transition: opacity 0.2s;
  flex-shrink: 0;
}
.profile-mule-name-row:hover .profile-mule-edit-icon {
  opacity: 1;
}

.profile-mule-name-input {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text);
  background-color: var(--bg-muted);
  border: 1.5px solid var(--primary);
  border-radius: 0.5rem;
  padding: 0.15rem 0.5rem;
  margin-top: 0.5rem;
  text-align: center;
  width: 100%;
}
.profile-mule-name-input:focus {
  outline: none;
}
</style>
