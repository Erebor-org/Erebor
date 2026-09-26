<template>
  <div class="min-h-screen">
    <NotificationCenter ref="notificationRef" />

    <div class="container mx-auto px-4 py-8 max-w-5xl">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Rangs de fonction</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Attribuez, modifiez ou retirez les rangs qui ne dépendent pas de l'ancienneté : direction, recrutement, animation</p>
      </div>

      <!-- Toolbar -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:justify-between">
          <div class="relative flex-1 md:max-w-md">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
              v-model="searchQuery"
              placeholder="Rechercher un personnage..."
              class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
            />
          </div>

          <div class="flex items-center gap-3">
            <span class="text-sm text-theme-text-muted whitespace-nowrap">{{ members.length }} personnage{{ members.length === 1 ? '' : 's' }}</span>
            <button
              @click="openAddModal"
              :disabled="isLoading"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl shadow-lg shadow-theme-primary/30 transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14" /></svg>
              Attribuer un rang
            </button>
          </div>
        </div>
      </div>

      <div v-if="isLoading" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        Chargement des rangs...
      </div>

      <div v-else-if="loadError" class="glass-card rounded-2xl text-center py-16">
        <p class="text-theme-error font-medium mb-3">{{ loadError }}</p>
        <button @click="fetchData" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
      </div>

      <template v-else>
        <!-- One section per special rank, in hierarchy order -->
        <!-- The section holding an open dropdown is raised so the menu isn't covered by the next section -->
        <div
          v-for="group in groupedMembers"
          :key="group.rank.id"
          class="mb-6 relative"
          :class="{ 'z-20': group.members.some(m => m.id === openRankDropdownId) }"
        >
          <div class="flex items-center gap-3 mb-3 px-1">
            <RankBadge :rank="group.rank" />
            <span class="text-xs text-theme-text-muted">{{ group.members.length }}</span>
            <div class="flex-1 h-px bg-theme-border"></div>
          </div>

          <div v-if="group.members.length > 0" class="glass-card rounded-2xl">
            <div v-for="member in group.members" :key="member.id" class="ml-row">
              <img :src="getClassIcon(member.class)" :alt="`Classe ${member.class}`" class="ml-class-icon" />

              <div class="ml-identity">
                <span class="ml-pseudo">{{ member.pseudo }}</span>
                <span class="ml-meta">{{ member.ankamaPseudo }} · {{ formatSeniority(member.seniorityDays) }}</span>
              </div>

              <div class="relative rank-dropdown">
                <button @click.stop="toggleRankDropdown(member.id, $event)" class="ml-select-btn" :disabled="savingId === member.id" title="Changer de rang de fonction">
                  <RankBadge :rank="member.rank" size="sm" />
                  <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': openRankDropdownId === member.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div v-if="openRankDropdownId === member.id" class="ml-menu" :class="{ 'ml-menu--up': rankDropdownUp }">
                  <button
                    v-for="rank in specialRanks"
                    :key="rank.id"
                    @click="changeSpecialRank(member, rank)"
                    class="ml-menu-option"
                    :class="{ 'ml-menu-option--active': member.rank.id === rank.id }"
                  >
                    <RankBadge :rank="rank" size="sm" />
                    <svg v-if="member.rank.id === rank.id" class="w-4 h-4 text-theme-primary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </button>
                </div>
              </div>

              <button
                @click="openRemoveModal(member)"
                :disabled="savingId === member.id"
                class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200 flex-shrink-0"
                title="Retirer le rang de fonction"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
              </button>
            </div>
          </div>

          <div v-else class="glass-card rounded-2xl text-center py-5 text-sm text-theme-text-muted">
            {{ searchQuery ? 'Aucun résultat' : 'Aucun personnage à ce rang' }}
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- Assign rank modal (kept outside glass containers: backdrop-filter breaks fixed positioning) -->
  <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="closeAddModal">
    <div class="glass-modal rounded-2xl p-6 max-w-lg w-full flex flex-col max-h-[90vh]">
      <h2 class="text-xl font-serif font-bold text-theme-primary mb-1">Attribuer un rang de fonction</h2>
      <p class="text-sm text-theme-text-muted mb-5">Seul le rang du personnage change, pas les droits du compte utilisateur.</p>

      <label class="block text-sm font-medium text-theme-text mb-2">Personnage</label>
      <div class="relative mb-2">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input
          ref="candidateSearchInput"
          v-model="candidateQuery"
          placeholder="Pseudo ou pseudo Ankama..."
          class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
        />
      </div>
      <div class="ml-candidates">
        <button
          v-for="candidate in filteredCandidates"
          :key="candidate.id"
          @click="selectedCandidate = candidate"
          class="ml-candidate"
          :class="{ 'ml-candidate--active': selectedCandidate?.id === candidate.id }"
        >
          <img :src="getClassIcon(candidate.class)" :alt="`Classe ${candidate.class}`" class="ml-class-icon ml-class-icon--sm" />
          <span class="flex-1 min-w-0 text-left">
            <span class="block font-semibold truncate">{{ candidate.pseudo }}</span>
            <span class="block text-xs text-theme-text-muted truncate">{{ candidate.ankamaPseudo }}</span>
          </span>
          <RankBadge :rank="candidate.rank" size="sm" />
        </button>
        <p v-if="filteredCandidates.length === 0" class="text-sm text-theme-text-muted text-center py-6">Aucun personnage trouvé</p>
      </div>

      <label class="block text-sm font-medium text-theme-text mt-5 mb-2">Rang de fonction</label>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="rank in specialRanks"
          :key="rank.id"
          @click="selectedSpecialRankId = rank.id"
          class="ml-choice"
          :class="{ 'ml-choice--active': selectedSpecialRankId === rank.id }"
        >
          <RankBadge :rank="rank" size="sm" />
        </button>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="closeAddModal" class="px-4 py-2 rounded-lg bg-theme-bg-muted text-theme-text hover:bg-theme-error/10 border border-theme-border transition-colors">Annuler</button>
        <button
          @click="assignRank"
          :disabled="!selectedCandidate || !selectedSpecialRankId || isSubmitting"
          class="px-4 py-2 rounded-lg bg-theme-primary text-white hover:bg-theme-primary-hover font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Attribuer
        </button>
      </div>
    </div>
  </div>

  <!-- Remove rank modal -->
  <div v-if="memberToRemove" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="memberToRemove = null">
    <div class="glass-modal rounded-2xl p-6 max-w-md w-full">
      <h2 class="text-xl font-serif font-bold text-theme-primary mb-1">Retirer le rang de fonction</h2>
      <p class="text-sm text-theme-text mb-5">
        <span class="font-semibold">{{ memberToRemove.pseudo }}</span> n'aura plus le rang
        <span class="font-semibold">{{ memberToRemove.rank.name }}</span>. Choisissez son nouveau rang.
      </p>

      <div class="ml-rank-grid">
        <button
          v-for="rank in classicRanks"
          :key="rank.id"
          @click="selectedClassicRankId = rank.id"
          class="ml-choice"
          :class="{ 'ml-choice--active': selectedClassicRankId === rank.id }"
        >
          <RankBadge :rank="rank" size="sm" />
          <span v-if="rank.id === memberToRemove.seniorityRankId" class="ml-suggested">ancienneté</span>
        </button>
      </div>
      <p class="text-xs text-theme-text-muted mt-3">
        {{ formatSeniority(memberToRemove.seniorityDays) }} dans la guilde<span v-if="seniorityRankName"> : le rang d'ancienneté est {{ seniorityRankName }}</span>.
      </p>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="memberToRemove = null" class="px-4 py-2 rounded-lg bg-theme-bg-muted text-theme-text hover:bg-theme-error/10 border border-theme-border transition-colors">Annuler</button>
        <button
          @click="removeSpecialRank"
          :disabled="!selectedClassicRankId || isSubmitting"
          class="px-4 py-2 rounded-lg bg-theme-primary text-white hover:bg-theme-primary-hover font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Retirer le rang
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import axios from '@/config/axios';
import NotificationCenter from '@/components/NotificationCenter.vue';
import RankBadge from '@/components/RankBadge.vue';
import { getClassIcon } from '@/config/classIcons';

const API_URL = import.meta.env.VITE_API_URL;

const notificationRef = ref(null);
const isLoading = ref(true);
const loadError = ref('');
const isSubmitting = ref(false);
const savingId = ref(null);

const members = ref([]);
const candidates = ref([]);
const specialRanks = ref([]);
const classicRanks = ref([]);

const searchQuery = ref('');
const openRankDropdownId = ref(null);
const rankDropdownUp = ref(false);

const showAddModal = ref(false);
const candidateQuery = ref('');
const candidateSearchInput = ref(null);
const selectedCandidate = ref(null);
const selectedSpecialRankId = ref(null);

const memberToRemove = ref(null);
const selectedClassicRankId = ref(null);

const notify = (message, type = 'success') => notificationRef.value?.showNotification(message, type);

const matches = (character, query) => {
  const q = query.trim().toLowerCase();
  if (!q) return true;
  return character.pseudo.toLowerCase().includes(q) || (character.ankamaPseudo || '').toLowerCase().includes(q);
};

const groupedMembers = computed(() =>
  specialRanks.value.map(rank => ({
    rank,
    members: members.value.filter(m => m.rank.id === rank.id && matches(m, searchQuery.value)),
  }))
);

const filteredCandidates = computed(() => candidates.value.filter(c => matches(c, candidateQuery.value)));

const seniorityRankName = computed(() =>
  classicRanks.value.find(r => r.id === memberToRemove.value?.seniorityRankId)?.name
);

const formatSeniority = (days) => `${days} jour${days > 1 ? 's' : ''}`;

const fetchData = async () => {
  isLoading.value = true;
  loadError.value = '';
  try {
    const { data } = await axios.get(`${API_URL}/admin/special-ranks`);
    members.value = data.members;
    candidates.value = data.candidates;
    specialRanks.value = data.specialRanks;
    classicRanks.value = data.classicRanks;
  } catch (error) {
    console.error('Error fetching special ranks:', error);
    loadError.value = 'Impossible de charger les rangs de fonction';
  } finally {
    isLoading.value = false;
  }
};

const toggleRankDropdown = (id, event) => {
  // Open upwards when the menu would overflow the bottom of the viewport
  const menuHeight = specialRanks.value.length * 44 + 16;
  rankDropdownUp.value = window.innerHeight - event.currentTarget.getBoundingClientRect().bottom < menuHeight;
  openRankDropdownId.value = openRankDropdownId.value === id ? null : id;
};

const changeSpecialRank = async (member, rank) => {
  openRankDropdownId.value = null;
  if (member.rank.id === rank.id) return;
  savingId.value = member.id;
  try {
    const { data } = await axios.put(`${API_URL}/admin/special-ranks/${member.id}`, { rankId: rank.id });
    Object.assign(member, data);
    notify(`${member.pseudo} est maintenant ${rank.name}`);
  } catch (error) {
    console.error('Error changing special rank:', error);
    notify(error.response?.data?.error || 'Erreur lors du changement de rang', 'error');
  } finally {
    savingId.value = null;
  }
};

const openAddModal = async () => {
  selectedCandidate.value = null;
  candidateQuery.value = '';
  selectedSpecialRankId.value = null;
  showAddModal.value = true;
  await nextTick();
  candidateSearchInput.value?.focus();
};

const closeAddModal = () => {
  showAddModal.value = false;
};

const assignRank = async () => {
  if (!selectedCandidate.value || !selectedSpecialRankId.value) return;
  isSubmitting.value = true;
  const candidate = selectedCandidate.value;
  try {
    const { data } = await axios.put(`${API_URL}/admin/special-ranks/${candidate.id}`, { rankId: selectedSpecialRankId.value });
    candidates.value = candidates.value.filter(c => c.id !== candidate.id);
    members.value = [...members.value, data].sort((a, b) => a.pseudo.localeCompare(b.pseudo));
    notify(`${data.pseudo} est maintenant ${data.rank.name}`);
    closeAddModal();
  } catch (error) {
    console.error('Error assigning special rank:', error);
    notify(error.response?.data?.error || "Erreur lors de l'attribution du rang", 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const openRemoveModal = (member) => {
  memberToRemove.value = member;
  selectedClassicRankId.value = member.seniorityRankId;
};

const removeSpecialRank = async () => {
  const member = memberToRemove.value;
  if (!member || !selectedClassicRankId.value) return;
  isSubmitting.value = true;
  try {
    const { data } = await axios.post(`${API_URL}/admin/special-ranks/${member.id}/remove`, { rankId: selectedClassicRankId.value });
    members.value = members.value.filter(m => m.id !== member.id);
    candidates.value = [...candidates.value, data].sort((a, b) => a.pseudo.localeCompare(b.pseudo));
    notify(`${data.pseudo} n'a plus de rang de fonction (nouveau rang : ${data.rank.name})`);
    memberToRemove.value = null;
  } catch (error) {
    console.error('Error removing special rank:', error);
    notify(error.response?.data?.error || 'Erreur lors du retrait du rang', 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.rank-dropdown')) {
    openRankDropdownId.value = null;
  }
};

onMounted(() => {
  fetchData();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.ml-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}
.ml-row:last-child {
  border-bottom: none;
}
.ml-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.ml-class-icon {
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}
.ml-class-icon--sm {
  width: 1.7rem;
  height: 1.7rem;
}

.ml-identity {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.ml-pseudo {
  font-weight: 700;
  color: var(--primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ml-meta {
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ml-select-btn {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  min-width: 160px;
  justify-content: space-between;
  padding: 0.45rem 0.7rem;
  border-radius: 0.65rem;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  transition: border-color 0.2s;
}
.ml-select-btn:hover {
  border-color: var(--primary);
}
.ml-select-btn:disabled {
  opacity: 0.5;
}

.ml-menu {
  position: absolute;
  right: 0;
  z-index: 50;
  margin-top: 0.4rem;
  min-width: 200px;
  background-color: var(--card);
  border: 1px solid rgba(var(--accent-rgb), 0.2);
  border-radius: 0.75rem;
  box-shadow: 0 24px 64px -24px rgba(0, 0, 0, 0.5);
  padding: 0.35rem;
}
.ml-menu--up {
  bottom: 100%;
  margin-top: 0;
  margin-bottom: 0.4rem;
}
.ml-menu-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.65rem;
  border-radius: 0.55rem;
  transition: background-color 0.2s;
}
.ml-menu-option:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.ml-menu-option--active {
  background-color: rgba(var(--primary-rgb), 0.1);
}

.ml-candidates {
  flex: 1;
  min-height: 8rem;
  max-height: 16rem;
  overflow-y: auto;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 0.3rem;
  background-color: var(--bg-muted);
}
.ml-candidate {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.5rem 0.6rem;
  border-radius: 0.55rem;
  font-size: 0.85rem;
  color: var(--text);
  border: 1px solid transparent;
  transition: background-color 0.2s, border-color 0.2s;
}
.ml-candidate:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.ml-candidate--active {
  background-color: rgba(var(--primary-rgb), 0.12);
  border-color: var(--primary);
}

.ml-rank-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.5rem;
}

.ml-choice {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.55rem 0.75rem;
  border-radius: 0.65rem;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  transition: border-color 0.2s, background-color 0.2s;
}
.ml-choice:hover {
  border-color: var(--primary);
}
.ml-choice--active {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.12);
  box-shadow: 0 0 0 1px var(--primary);
}

.ml-suggested {
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--accent);
}

@media (max-width: 640px) {
  .ml-row {
    flex-wrap: wrap;
  }
  .ml-identity {
    flex-basis: calc(100% - 3rem);
  }
  .rank-dropdown {
    flex: 1;
  }
  .ml-select-btn {
    width: 100%;
  }
  .ml-rank-grid {
    grid-template-columns: minmax(0, 1fr);
  }
}
</style>
