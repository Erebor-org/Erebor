<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Roue des Membres</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Tirage aléatoire parmi les membres sélectionnés</p>
      </div>

      <div class="flex justify-center mb-8">
        <div class="inline-flex rounded-xl bg-theme-bg-muted p-1 border border-theme-border">
          <RouterLink to="/wheel" class="wheel-tab wheel-tab--active">Membres</RouterLink>
          <RouterLink to="/wheel-classes" class="wheel-tab">Classes</RouterLink>
          <RouterLink to="/wheel-numbers" class="wheel-tab">Nombres</RouterLink>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Sélecteur de personnages -->
        <div class="glass-card rounded-2xl p-5 flex flex-col">
          <div class="relative mb-3">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
              type="text"
              v-model="search"
              placeholder="Rechercher un pseudo..."
              class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
            />
          </div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-theme-text-muted">{{ selectedCount }} sélectionné{{ selectedCount === 1 ? '' : 's' }} sur {{ characters.length }}</span>
            <button @click="toggleSelectAll" class="text-theme-primary hover:text-theme-primary-hover text-xs font-semibold">
              {{ allSelected ? 'Tout désélectionner' : 'Tout sélectionner' }}
            </button>
          </div>
          <div class="wheel-list">
            <label
              v-for="char in filteredCharacters.length ? filteredCharacters : characters"
              :key="char.id"
              class="wheel-list-row"
              :class="{ 'wheel-list-row--eliminated': char.eliminated, 'wheel-list-row--selected': char.selected && !char.eliminated }"
            >
              <input type="checkbox" v-model="char.selected" :disabled="char.eliminated" class="accent-theme-primary w-4 h-4 flex-shrink-0" />
              <img :src="getClassIcon(char.class)" alt="" class="wheel-list-icon" />
              <span class="truncate">{{ char.pseudo }}</span>
            </label>
          </div>
        </div>

        <!-- Roue de sélection -->
        <div class="glass-card rounded-2xl p-6 flex flex-col items-center">
          <div class="wheel-ring">
            <canvas ref="wheelCanvas" width="384" height="384"></canvas>
          </div>

          <button class="wheel-spin-btn mt-6" :disabled="remainingPlayers.length < 2 || spinning" @click="spinWheel">
            {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
          </button>

          <div v-if="lastEliminated" class="mt-5 text-center">
            <span class="text-xs uppercase tracking-wide text-theme-text-muted font-bold">Éliminé</span>
            <div class="flex items-center justify-center gap-2 mt-1">
              <img :src="getClassIcon(lastEliminated.class)" alt="" class="w-6 h-6 rounded-full border border-theme-border" />
              <span class="font-semibold text-theme-text">{{ lastEliminated.pseudo }}</span>
            </div>
          </div>

          <transition name="winner-pop">
            <div v-if="winner && hasSpun && lastSpinPlayersCount > 1 && !spinning" class="mt-6 text-center">
              <div class="text-xl font-serif font-bold brand-gradient-text">🎉 Gagnant : {{ winner.pseudo }} 🎉</div>
              <img :src="getClassIcon(winner.class)" alt="" class="w-12 h-12 mx-auto mt-2 rounded-full border-2 border-theme-primary" />
            </div>
          </transition>

          <div class="flex gap-3 mt-6">
            <button class="wheel-btn-secondary" @click="undoElimination" :disabled="eliminatedHistory.length === 0">Annuler élimination</button>
            <button class="wheel-btn-secondary" @click="resetWheel">Réinitialiser</button>
          </div>
          <label class="flex items-center gap-2 mt-4 text-sm text-theme-text-muted cursor-pointer">
            <input type="checkbox" v-model="tirageInstantane" class="accent-theme-primary w-4 h-4" />
            Tirage instantané (pas d'élimination)
          </label>
        </div>

        <!-- Historique des éliminés -->
        <div class="glass-card rounded-2xl p-5 flex flex-col">
          <span class="text-xs font-bold uppercase tracking-wide text-theme-text-muted mb-3">Historique des éliminés</span>
          <div class="wheel-list">
            <div v-for="char in eliminatedHistory" :key="char.id" class="wheel-list-row wheel-list-row--readonly">
              <img :src="getClassIcon(char.class)" alt="" class="wheel-list-icon" />
              <span class="truncate">{{ char.pseudo }}</span>
            </div>
            <p v-if="eliminatedHistory.length === 0" class="text-sm text-theme-text-muted text-center py-8">Aucune élimination pour l'instant</p>
          </div>
        </div>
      </div>

      <!-- Filtre par rang -->
      <div class="glass-card rounded-2xl p-5 mt-6">
        <span class="text-xs font-bold uppercase tracking-wide text-theme-text-muted mb-3 block">Sélectionner par rang</span>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="rank in ranks.slice().sort((a, b) => a.id - b.id)"
            :key="rank.id"
            class="wheel-rank-chip"
            :class="{ 'wheel-rank-chip--active': checkedRanks.includes(rank.id) }"
            @click="toggleRankCheckbox(rank.id)"
            :title="rank.description || ''"
            type="button"
          >
            <span>{{ rank.name }}</span>
            <span v-if="rankMemberCount(rank.id) > 0" class="wheel-rank-count">{{ rankMemberCount(rank.id) }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div v-if="spinning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="flex flex-col items-center justify-center">
      <div class="wheel-ring wheel-ring--big">
        <canvas ref="wheelCanvas" width="1000" height="1000" style="max-width:85vw; max-height:85vh;"></canvas>
      </div>
      <div class="mt-8 text-theme-text text-xl font-serif font-bold animate-pulse">Tirage en cours...</div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import confetti from 'canvas-confetti';
import { RouterLink } from 'vue-router';
import { getClassIcon as getClassIconFromConfig } from '@/config/classIcons';
import {
  getWheelSliceColors,
  getWheelSliceGradient,
  getWheelConfettiColors,
  drawWheelRimBezel,
  drawWheelHub,
  drawWheelPointer,
} from '@/utils/wheelPalette';

const API_URL = import.meta.env.VITE_API_URL;

interface Character {
  id: number;
  pseudo: string;
  class: string;
  selected: boolean;
  eliminated: boolean;
  rank?: { id: number; name: string };
}

const characters = ref<Character[]>([]);
const search = ref('');
const spinning = ref(false);
const lastEliminated = ref<Character|null>(null);
const winner = ref<Character|null>(null);
const eliminatedHistory = ref<Character[]>([]);
const wheelCanvas = ref<HTMLCanvasElement | null>(null);
let spinOrder: Character[] = [];    // snapshot du tour en cours (ordre et contenu figés)
let spinIds: number[] = [];         // ids pour élimination fiable
const tirageInstantane = ref(false);
const hasSpun = ref(false);
let lastSpinPlayersCount = 0;


let selectedIndex = 0;
let spinPlayersCount = 0;
let animationFrame: number | null = null;
let targetAngle = 0;
let spinStart = 0;
let spinDuration = 0;
let currentAngle = 0;


const fetchCharacters = async () => {
  try {
    const { data } = await axios.get(`${API_URL}/characters/`);
    characters.value = data
      .filter((c: any) => c.isArchived === false)
      .map((c: any) => ({
        id: c.id,
        pseudo: c.pseudo,
        class: c.class,
        selected: false,
        eliminated: false,
        // Correction : toujours un objet ou null
        rank: c.rank && typeof c.rank === 'object' && c.rank.id ? { id: c.rank.id, name: c.rank.name } : null,
      }));
    resetWheel();
  } catch (error: any) {
    console.error('Erreur lors du chargement des personnages:', error?.response?.data || error.message);
  }
};

// Ajout : liste complète des rangs
const ranks = ref<{ id: number; name: string }[]>([]);

const fetchRanks = async () => {
  try {
    const { data } = await axios.get(`${API_URL}/ranks`);
    ranks.value = data;
  } catch (error: any) {
    console.error('Erreur lors du chargement des rangs:', error?.response?.data || error.message);
  }
};

onMounted(() => {
  fetchCharacters();
  fetchRanks();
});

const filteredCharacters = computed(() => {
  return characters.value.filter(c =>
    c.pseudo.toLowerCase().includes(search.value.toLowerCase())
  );
});

const selectedCount = computed(() => characters.value.filter(c => c.selected && !c.eliminated).length);
const allSelected = computed(() => characters.value.every(c => c.selected && !c.eliminated));
const remainingPlayers = computed(() => characters.value.filter(c => c.selected && !c.eliminated));

function toggleSelectAll() {
  const select = !allSelected.value;
  characters.value.forEach(c => {
    if (!c.eliminated) c.selected = select;
  });
}

function getClassIcon(className: string) {
  return getClassIconFromConfig(className?.toLowerCase() || 'iop');
}

function spinWheel() {
  if (spinning.value || remainingPlayers.value.length < 2) return;
  lastSpinPlayersCount = remainingPlayers.value.length;
  spinning.value = true;
  hasSpun.value = false;

  // 1) Snapshot des joueurs restants dans l'ordre **visible au dessin**
  spinOrder = remainingPlayers.value.map(c => c);
  spinIds = spinOrder.map(c => c.id);
  spinPlayersCount = spinOrder.length;

  // 2) Tirage équitable d'un index dans CETTE liste figée
  selectedIndex = Math.floor(Math.random() * spinPlayersCount);

  // 3) Calcul de l’angle cible avec l’offset -π/2 (flèche en haut)
  const seg = (2 * Math.PI) / spinPlayersCount;
  const baseTurns = 5 + Math.floor(Math.random() * 2); // 5–6 tours
  targetAngle = baseTurns * 2 * Math.PI - (selectedIndex + 0.5) * seg - Math.PI / 2;

  spinStart = performance.now();
  spinDuration = 3200 + Math.random() * 800;

  // 4) Lancer l’animation en dessinant avec **spinOrder**
  animateWheel();
}

function animateWheel(now?: number) {
  if (!now) now = performance.now();
  const elapsed = now - spinStart;
  const t = Math.min(elapsed / spinDuration, 1);
  currentAngle = (1 - Math.pow(1 - t, 3)) * targetAngle;

  // Dessine pendant le spin avec la **liste figée**
  drawWheel(currentAngle, spinPlayersCount, spinOrder);

  if (t < 1) {
    animationFrame = requestAnimationFrame(animateWheel);
  } else {
    // Fin du spin : élimine **le snapshot** sélectionné
    const eliminatedId = spinIds[selectedIndex];
    const char = characters.value.find(c => c.id === eliminatedId);
    if (tirageInstantane.value) {
      winner.value = char || null;
    } else {
      if (char && !char.eliminated) {
        char.eliminated = true;
        eliminatedHistory.value.unshift(char);
        lastEliminated.value = char;
      }
      if (remainingPlayers.value.length === 1) {
        winner.value = remainingPlayers.value[0];
      }
    }
    spinning.value = false;
    hasSpun.value = true;
    nextTick(() => drawWheel(0, undefined, undefined));
  }
}

const iconCache: Record<string, HTMLImageElement> = {};

function getIcon(className: string): HTMLImageElement {
  const normalizedClass = className?.toLowerCase() || 'iop';
  if (!iconCache[normalizedClass]) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = getClassIcon(normalizedClass);
    iconCache[normalizedClass] = img;
  }
  return iconCache[normalizedClass];
}
function drawWheel(angle = 0, nOverride?: number, playersOverride?: Character[] | null) {
  const canvas = wheelCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // si playersOverride fourni, on l’utilise, sinon on reprend remainingPlayers
  const players = playersOverride ?? remainingPlayers.value;
  const n = nOverride || players.length;
  if (n === 0) return;
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = Math.min(centerX, centerY) - 10;
  const hubRadius = radius * 0.14;
  const iconSize = canvas.width / 9.6;
  const fontSize = canvas.width / 25.6;
  const sliceColors = getWheelSliceColors(n);
  for (let i = 0; i < n; i++) {
    const angleStart = (i / n) * 2 * Math.PI + angle;
    const angleEnd = ((i + 1) / n) * 2 * Math.PI + angle;
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, angleStart, angleEnd);
    ctx.closePath();
    ctx.fillStyle = getWheelSliceGradient(ctx, centerX, centerY, hubRadius, radius, sliceColors[i]);
    ctx.fill();
    ctx.lineWidth = Math.max(1.5, canvas.width / 400);
    ctx.strokeStyle = 'rgba(0,0,0,0.35)';
    ctx.stroke();
    ctx.restore();
    // Position radiale pour l’icône et le texte
    const midAngle = angleStart + (angleEnd - angleStart) / 2;
    const iconRadius = radius * 0.7;
    const iconX = centerX + Math.cos(midAngle) * iconRadius - iconSize / 2;
    const iconY = centerY + Math.sin(midAngle) * iconRadius - iconSize / 2;
    // Icône de classe
    const className = players[i].class?.toLowerCase() || 'iop';
    const icon = getIcon(className);

    // Draw icon if already loaded
    if (icon.complete && icon.naturalWidth > 0) {
      ctx.save();
      ctx.beginPath();
      ctx.arc(iconX + iconSize / 2, iconY + iconSize / 2, iconSize / 2, 0, 2 * Math.PI);
      ctx.closePath();
      ctx.clip();
      ctx.drawImage(icon, iconX, iconY, iconSize, iconSize);
      ctx.restore();
    } else {
      // Wait for image to load
      icon.onload = () => {
        // Redraw the wheel when image loads
        if (canvas && ctx) {
          drawWheel(angle, nOverride, playersOverride);
        }
      };
      icon.onerror = () => {
        // Fallback: try loading default icon if class icon fails
        if (className !== 'iop') {
          const fallbackIcon = getIcon('iop');
          if (fallbackIcon.complete) {
            ctx.save();
            ctx.beginPath();
            ctx.arc(iconX + iconSize / 2, iconY + iconSize / 2, iconSize / 2, 0, 2 * Math.PI);
            ctx.closePath();
            ctx.clip();
            ctx.drawImage(fallbackIcon, iconX, iconY, iconSize, iconSize);
            ctx.restore();
          }
        }
      };
    }
    // Texte pseudo SOUS l’icône, à 82% du rayon
    const textRadius = radius * 0.82;
    const textX = centerX + Math.cos(midAngle) * textRadius;
    const textY = centerY + Math.sin(midAngle) * textRadius;
    ctx.save();
    ctx.font = `bold ${fontSize}px ui-serif, Georgia, serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    let pseudo = players[i].pseudo;
    if (ctx.measureText(pseudo).width > iconSize * 2.2) {
      while (pseudo.length > 0 && ctx.measureText(pseudo + '…').width > iconSize * 2.2) {
        pseudo = pseudo.slice(0, -1);
      }
      pseudo += '…';
    }
    ctx.lineWidth = 3;
    ctx.strokeStyle = 'rgba(0,0,0,0.55)';
    ctx.strokeText(pseudo, 0, 0);
    ctx.fillStyle = '#ffffff';
    ctx.fillText(pseudo, 0, 0);
    ctx.restore();
  }
  drawWheelRimBezel(ctx, centerX, centerY, radius);
  drawWheelHub(ctx, centerX, centerY, hubRadius);
  drawWheelPointer(ctx, centerX, centerY, radius);
}

function undoElimination() {
  const last = eliminatedHistory.value.shift();
  if (last) {
    last.eliminated = false;
    lastEliminated.value = null;
    winner.value = null;
    drawWheel();
  }
}

function resetWheel() {
  characters.value.forEach(c => {
    c.eliminated = false;
    c.selected = false;
  });
  eliminatedHistory.value = [];
  lastEliminated.value = null;
  winner.value = null;
  checkedRanks.value = [];
  hasSpun.value = false;
  drawWheel();
}

watch(remainingPlayers, () => {
  drawWheel();
  if (remainingPlayers.value.length === 1) {
    winner.value = remainingPlayers.value[0];
  } else if (remainingPlayers.value.length > 1) {
    winner.value = null;
  }
});

watch(winner, (newWinner, oldWinner) => {
  if (
    newWinner &&
    newWinner !== oldWinner &&
    hasSpun.value &&
    lastSpinPlayersCount > 1 // On ne fait pas de confetti si on n'a jamais spin à plus d'un joueur
  ) {
    confetti({
      particleCount: 120,
      spread: 80,
      origin: { y: 0.6 },
      zIndex: 9999,
      colors: getWheelConfettiColors()
    });
  }
});

// Calcule la liste des rangs distincts présents dans les personnages (avec id et name)
const availableRanks = computed(() => {
  const map = new Map<number, string>();
  characters.value.forEach(c => {
    if (c.rank && c.rank.id && c.rank.name) map.set(c.rank.id, c.rank.name);
  });
  return Array.from(map.entries()).map(([id, name]) => ({ id, name }));
});

// Map des rangs cochés (pour l'état des checkbox)
const checkedRanks = ref<number[]>([]);

function toggleRankCheckbox(rankId: number) {
  const isChecked = checkedRanks.value.includes(rankId);
  if (isChecked) {
    // Désélectionne tous les persos de ce rang
    characters.value.forEach(c => {
      if (c.rank && c.rank.id === rankId) c.selected = false;
    });
    checkedRanks.value = checkedRanks.value.filter(r => r !== rankId);
  } else {
    // Sélectionne tous les persos de ce rang (non éliminés)
    characters.value.forEach(c => {
      if (c.rank && c.rank.id === rankId && !c.eliminated) c.selected = true;
    });
    checkedRanks.value.push(rankId);
  }
}

function rankMemberCount(rankId: number) {
  return characters.value.filter(c => c.rank && c.rank.id === rankId).length;
}
</script>

<style scoped>
.wheel-tab {
  padding: 0.6rem 1.6rem;
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: 0.6rem;
  color: var(--text-muted);
  transition: background-color 0.2s, color 0.2s;
}
.wheel-tab:hover {
  color: var(--text);
}
.wheel-tab--active {
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
  color: #fff;
}

.wheel-list {
  flex: 1;
  overflow-y: auto;
  max-height: 22rem;
  border: 1px solid var(--border);
  border-radius: 0.85rem;
}

.wheel-list-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.75rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem;
  color: var(--text);
  cursor: pointer;
  transition: background-color 0.2s;
}
.wheel-list-row:last-child {
  border-bottom: none;
}
.wheel-list-row:hover {
  background-color: rgba(var(--primary-rgb), 0.05);
}
.wheel-list-row--selected {
  background-color: rgba(var(--primary-rgb), 0.08);
  border-left: 3px solid var(--primary);
  padding-left: calc(0.75rem - 3px);
}
.wheel-list-row--eliminated {
  opacity: 0.45;
  text-decoration: line-through;
  cursor: not-allowed;
}
.wheel-list-row--readonly {
  cursor: default;
}

.wheel-list-icon {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

.wheel-ring {
  display: inline-flex;
  padding: 6px;
  border-radius: 9999px;
  background-image: conic-gradient(from 200deg, var(--accent), transparent 40%, var(--primary), var(--accent));
}
.wheel-ring canvas {
  border-radius: 9999px;
  background-color: var(--bg);
  display: block;
}
.wheel-ring--big {
  padding: 10px;
}

.wheel-spin-btn {
  width: 100%;
  max-width: 20rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  color: #fff;
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
  transition: filter 0.2s, opacity 0.2s;
}
.wheel-spin-btn:hover:not(:disabled) {
  filter: brightness(1.08);
}
.wheel-spin-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.wheel-btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: 0.6rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text);
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  transition: border-color 0.2s, color 0.2s;
}
.wheel-btn-secondary:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.wheel-btn-secondary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.wheel-rank-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.9rem;
  border-radius: 9999px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  border: 1px solid var(--border);
  transition: border-color 0.2s, background-color 0.2s, color 0.2s;
}
.wheel-rank-chip:hover {
  border-color: var(--accent);
  color: var(--accent-hover);
}
.wheel-rank-chip--active {
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
  border-color: transparent;
  color: #fff;
}
.wheel-rank-count {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.05rem 0.4rem;
  border-radius: 9999px;
  background-color: rgba(255, 255, 255, 0.2);
}

.winner-pop-enter-active {
  transition: all 0.5s cubic-bezier(.68,-0.55,.27,1.55);
}
.winner-pop-leave-active {
  transition: all 0.3s ease;
}
.winner-pop-enter-from {
  opacity: 0;
  transform: scale(0.7) rotate(-10deg);
}
.winner-pop-enter-to {
  opacity: 1;
  transform: scale(1) rotate(0deg);
}
.winner-pop-leave-from {
  opacity: 1;
  transform: scale(1);
}
.winner-pop-leave-to {
  opacity: 0;
  transform: scale(0.7) rotate(10deg);
}
</style>
