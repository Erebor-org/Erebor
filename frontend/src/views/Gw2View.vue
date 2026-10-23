<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Guild Wars 2</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Section privée : tes personnages et leur progression</p>
      </div>

      <!-- API key permissions -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <div v-if="tokenLoading" class="text-sm text-theme-text-muted">Vérification de la clé API...</div>
        <div v-else-if="tokenError" class="text-sm text-theme-error font-medium">{{ tokenError }}</div>
        <div v-else class="flex flex-col md:flex-row md:items-center gap-3">
          <div class="flex items-center gap-2 flex-shrink-0">
            <svg class="w-5 h-5" :class="tokenInfo.missing.length ? 'text-theme-warning' : 'text-theme-success'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
            <span class="font-semibold text-theme-text">Clé « {{ tokenInfo.name }} »</span>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="permission in tokenInfo.expected"
              :key="permission"
              class="gw-chip"
              :class="tokenInfo.permissions.includes(permission) ? 'gw-chip--ok' : 'gw-chip--missing'"
              :title="tokenInfo.permissions.includes(permission) ? 'Permission accordée' : 'Permission manquante'"
            >
              {{ permissionLabel(permission) }}
            </span>
            <span v-for="permission in extraPermissions" :key="permission" class="gw-chip" title="Permission accordée (non requise)">
              {{ permissionLabel(permission) }}
            </span>
          </div>
          <span v-if="tokenInfo.missing.length" class="text-xs text-theme-warning md:ml-auto">
            {{ tokenInfo.missing.length }} permission{{ tokenInfo.missing.length > 1 ? 's' : '' }} manquante{{ tokenInfo.missing.length > 1 ? 's' : '' }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Characters list -->
        <div class="glass-card rounded-2xl overflow-hidden">
          <div class="px-5 py-4 border-b border-theme-border flex items-center justify-between">
            <h2 class="font-serif font-bold text-lg text-theme-primary">Personnages</h2>
            <span v-if="!charactersLoading && !charactersError" class="text-sm text-theme-text-muted">{{ characters.length }}</span>
          </div>

          <div v-if="charactersLoading" class="text-center py-10 text-theme-text-muted text-sm">Chargement des personnages...</div>
          <div v-else-if="charactersError" class="text-center py-10 px-4">
            <p class="text-theme-error font-medium mb-3 text-sm">{{ charactersError }}</p>
            <button @click="loadCharacters" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
          </div>
          <div v-else-if="characters.length === 0" class="text-center py-10 text-theme-text-muted text-sm">Aucun personnage sur ce compte</div>

          <button
            v-for="character in characters"
            v-else
            :key="character.name"
            @click="selectCharacter(character.name)"
            class="gw-row"
            :class="{ 'gw-row--active': selectedName === character.name }"
          >
            <span class="gw-prof" :style="{ '--prof': professionColor(character.profession) }">{{ professionLabel(character.profession).charAt(0) }}</span>
            <span class="flex-1 min-w-0 text-left">
              <span class="block font-bold text-theme-primary truncate">{{ character.name }}</span>
              <span class="block text-xs text-theme-text-muted truncate">{{ professionLabel(character.profession) }} · {{ raceLabel(character.race) }}</span>
            </span>
            <span class="gw-level">{{ character.level }}</span>
          </button>
        </div>

        <!-- Character detail -->
        <div class="lg:col-span-2">
          <div v-if="!selectedName" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
            Sélectionne un personnage pour voir sa fiche
          </div>
          <div v-else-if="detailLoading" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
            Chargement de {{ selectedName }}...
          </div>
          <div v-else-if="detailError" class="glass-card rounded-2xl text-center py-16 px-4">
            <p class="text-theme-error font-medium mb-3">{{ detailError }}</p>
            <button @click="selectCharacter(selectedName)" class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium">Réessayer</button>
          </div>

          <template v-else-if="detail">
            <!-- Identity -->
            <div class="glass-card rounded-2xl p-6 mb-6">
              <div class="flex items-center gap-4 mb-5">
                <span class="gw-prof gw-prof--lg" :style="{ '--prof': professionColor(detail.profession) }">{{ professionLabel(detail.profession).charAt(0) }}</span>
                <div class="min-w-0">
                  <h2 class="text-2xl font-serif font-bold text-theme-primary truncate">{{ detail.name }}</h2>
                  <p class="text-sm text-theme-text-muted">
                    {{ professionLabel(detail.profession) }} {{ raceLabel(detail.race) }} · {{ detail.gender === 'Female' ? 'Femme' : 'Homme' }}
                    <template v-if="detail.guild"> · [{{ detail.guild.tag }}] {{ detail.guild.name }}</template>
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="gw-stat">
                  <span class="gw-stat-label">Niveau</span>
                  <span class="gw-stat-value">{{ detail.level }}<span class="text-sm text-theme-text-muted"> / 80</span></span>
                  <span class="gw-bar"><span :style="{ width: `${Math.min(100, detail.level / 80 * 100)}%` }"></span></span>
                </div>
                <div class="gw-stat">
                  <span class="gw-stat-label">Temps de jeu</span>
                  <span class="gw-stat-value">{{ formatPlayedTime(detail.age) }}</span>
                </div>
                <div class="gw-stat">
                  <span class="gw-stat-label">Morts</span>
                  <span class="gw-stat-value">{{ detail.deaths }}</span>
                </div>
                <div class="gw-stat">
                  <span class="gw-stat-label">Sacs</span>
                  <span class="gw-stat-value">{{ detail.bags.used }}<span class="text-sm text-theme-text-muted"> / {{ detail.bags.slots }}</span></span>
                  <span class="gw-bar"><span :style="{ width: `${detail.bags.slots ? detail.bags.used / detail.bags.slots * 100 : 0}%` }"></span></span>
                </div>
              </div>
              <p class="text-xs text-theme-text-muted mt-4">Créé le {{ formatDate(detail.created) }} · {{ detail.bags.count }} sacs équipés</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <!-- Active build -->
              <div class="glass-card rounded-2xl p-5">
                <h3 class="font-serif font-bold text-theme-primary mb-3">Spécialisations actives</h3>
                <p v-if="detail.specializations.length === 0" class="text-sm text-theme-text-muted">Aucune spécialisation choisie</p>
                <div v-for="spec in detail.specializations" :key="spec.id" class="flex items-center gap-3 py-1.5">
                  <img v-if="spec.icon" :src="spec.icon" :alt="spec.name" class="w-8 h-8 rounded-md" loading="lazy" />
                  <span class="font-medium text-theme-text">{{ spec.name }}</span>
                  <span v-if="spec.elite" class="gw-chip gw-chip--ok ml-auto">Élite</span>
                </div>
              </div>

              <!-- Crafting -->
              <div class="glass-card rounded-2xl p-5">
                <h3 class="font-serif font-bold text-theme-primary mb-3">Artisanat</h3>
                <p v-if="detail.crafting.length === 0" class="text-sm text-theme-text-muted">Aucune discipline apprise</p>
                <div v-for="craft in detail.crafting" :key="craft.discipline" class="py-1.5">
                  <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-theme-text" :class="{ 'opacity-60': !craft.active }">
                      {{ craftingLabel(craft.discipline) }}<span v-if="!craft.active" class="text-xs text-theme-text-muted"> (inactif)</span>
                    </span>
                    <span class="text-theme-text-muted">{{ craft.rating }} / {{ craftingMax(craft.discipline) }}</span>
                  </div>
                  <span class="gw-bar"><span :style="{ width: `${craft.rating / craftingMax(craft.discipline) * 100}%` }"></span></span>
                </div>
              </div>
            </div>

            <!-- Equipment -->
            <div class="glass-card rounded-2xl p-5">
              <h3 class="font-serif font-bold text-theme-primary mb-3">Équipement porté</h3>
              <p v-if="sortedEquipment.length === 0" class="text-sm text-theme-text-muted">Aucun équipement</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div v-for="item in sortedEquipment" :key="item.slot" class="gw-item" :style="{ '--rarity': rarityColor(item.rarity) }">
                  <img v-if="item.icon" :src="item.icon" :alt="item.name" class="gw-item-icon" loading="lazy" />
                  <span class="min-w-0">
                    <span class="block text-sm font-medium text-theme-text truncate" :title="item.name">{{ item.name || `Objet #${item.id}` }}</span>
                    <span class="block text-xs text-theme-text-muted">{{ slotLabel(item.slot) }}<template v-if="item.level"> · niv. {{ item.level }}</template></span>
                  </span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchGw2TokenInfo, fetchGw2Characters, fetchGw2Character, gw2ErrorMessage } from '@/services/gw2Api';
import {
  RACES, CRAFTING, RARITY_COLORS, SLOTS, SLOT_ORDER, PERMISSION_LABELS,
  professionLabel, professionColor, formatPlayedTime, formatDate,
} from '@/utils/gw2';

const tokenInfo = ref(null);
const tokenLoading = ref(true);
const tokenError = ref('');

const characters = ref([]);
const charactersLoading = ref(true);
const charactersError = ref('');

const selectedName = ref(null);
const detail = ref(null);
const detailLoading = ref(false);
const detailError = ref('');

const raceLabel = (race) => RACES[race] || race;
const craftingLabel = (discipline) => CRAFTING[discipline] || discipline;
// Jeweler and Scribe cap lower than the other disciplines
const craftingMax = (discipline) => (['Jeweler', 'Scribe'].includes(discipline) ? 400 : 500);
const rarityColor = (rarity) => RARITY_COLORS[rarity] || 'var(--border)';
const slotLabel = (slot) => SLOTS[slot] || slot;
const permissionLabel = (permission) => PERMISSION_LABELS[permission] || permission;

const extraPermissions = computed(() =>
  (tokenInfo.value?.permissions || []).filter(p => !tokenInfo.value.expected.includes(p))
);

const sortedEquipment = computed(() =>
  [...(detail.value?.equipment || [])].sort((a, b) => SLOT_ORDER.indexOf(a.slot) - SLOT_ORDER.indexOf(b.slot))
);

const loadTokenInfo = async () => {
  tokenLoading.value = true;
  tokenError.value = '';
  try {
    tokenInfo.value = await fetchGw2TokenInfo();
  } catch (error) {
    tokenError.value = gw2ErrorMessage(error, 'Impossible de vérifier la clé API');
  } finally {
    tokenLoading.value = false;
  }
};

const loadCharacters = async () => {
  charactersLoading.value = true;
  charactersError.value = '';
  try {
    characters.value = await fetchGw2Characters();
    if (!selectedName.value && characters.value.length === 1) {
      selectCharacter(characters.value[0].name);
    }
  } catch (error) {
    charactersError.value = gw2ErrorMessage(error, 'Impossible de charger les personnages');
  } finally {
    charactersLoading.value = false;
  }
};

const selectCharacter = async (name) => {
  selectedName.value = name;
  detailLoading.value = true;
  detailError.value = '';
  try {
    const data = await fetchGw2Character(name);
    // Ignore a late answer when another character was clicked meanwhile
    if (selectedName.value === name) detail.value = data;
  } catch (error) {
    if (selectedName.value === name) detailError.value = gw2ErrorMessage(error, `Impossible de charger ${name}`);
  } finally {
    if (selectedName.value === name) detailLoading.value = false;
  }
};

onMounted(() => {
  loadTokenInfo();
  loadCharacters();
});
</script>

<style scoped>
.gw-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid var(--border);
  color: var(--text-muted);
}
.gw-chip--ok {
  color: var(--success);
  border-color: color-mix(in srgb, var(--success) 40%, transparent);
  background-color: color-mix(in srgb, var(--success) 10%, transparent);
}
.gw-chip--missing {
  color: var(--error);
  border-color: color-mix(in srgb, var(--error) 40%, transparent);
  background-color: color-mix(in srgb, var(--error) 10%, transparent);
  text-decoration: line-through;
}

.gw-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  width: 100%;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid var(--border);
  border-left: 3px solid transparent;
  transition: background-color 0.2s;
}
.gw-row:last-child {
  border-bottom: none;
}
.gw-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}
.gw-row--active {
  background-color: rgba(var(--primary-rgb), 0.08);
  border-left-color: var(--primary);
}

.gw-prof {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 0.5rem;
  flex-shrink: 0;
  font-weight: 800;
  color: var(--prof);
  background-color: color-mix(in srgb, var(--prof) 18%, transparent);
  border: 1px solid color-mix(in srgb, var(--prof) 50%, transparent);
}
.gw-prof--lg {
  width: 3.25rem;
  height: 3.25rem;
  font-size: 1.4rem;
  border-radius: 0.75rem;
}

.gw-level {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  font-variant-numeric: tabular-nums;
}

.gw-stat {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.75rem 0.9rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  background-color: rgba(var(--primary-rgb), 0.03);
}
.gw-stat-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.gw-stat-value {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

.gw-bar {
  display: block;
  height: 0.3rem;
  border-radius: 9999px;
  background-color: var(--border);
  overflow: hidden;
  margin-top: 0.25rem;
}
.gw-bar > span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
}

.gw-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.65rem;
  border-radius: 0.65rem;
  border: 1px solid var(--border);
  border-left: 3px solid var(--rarity);
}
.gw-item-icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.4rem;
  border: 1px solid var(--rarity);
  flex-shrink: 0;
}
</style>
