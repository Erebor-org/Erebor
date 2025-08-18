<template>
  <div class="min-h-screen bg-theme-bg text-theme-text flex flex-col items-center p-4">
    <h1 class="text-2xl font-bold mb-4 text-theme-primary">Roue de sélection Dofus</h1>
    <div class="w-full max-w-6xl grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Sélecteur de personnages -->
      <div class="bg-theme-card border border-theme-border rounded shadow p-4 flex flex-col">
        <div class="flex items-center mb-2">
          <input type="text" v-model="search" placeholder="Rechercher un pseudo..." class="input input-bordered w-full max-w-xs mr-2 bg-theme-bg text-theme-text border-theme-border" />
          <button class="btn btn-sm ml-2" @click="toggleSelectAll">{{ allSelected ? 'Tout désélectionner' : 'Tout sélectionner' }}</button>
        </div>
        <div class="text-sm mb-2 font-semibold">{{ selectedCount }} sélectionné(s) sur {{ characters.length }}</div>
        <div class="flex-1 overflow-y-auto max-h-80 border border-theme-border rounded bg-theme-bg">
          <div v-for="char in filteredCharacters.length ? filteredCharacters : characters"
            :key="char.id"
            class="flex items-center py-2 px-2 border-b border-theme-border last:border-b-0 cursor-pointer transition-all"
            :class="[
              char.eliminated ? 'bg-theme-bg-muted text-theme-text-muted line-through opacity-60 cursor-not-allowed' : (char.selected ? 'bg-theme-primary/10 border-l-4 border-theme-primary' : 'hover:bg-theme-bg-muted'),
              'group'
            ]"
            @click="!char.eliminated && (char.selected = !char.selected)"
          >
            <input
              type="checkbox"
              v-model="char.selected"
              :disabled="char.eliminated"
              class="mr-3 w-5 h-5 accent-theme-primary cursor-pointer"
              @click.stop
            />
            <img :src="getClassIcon(char.class)" alt="class icon" class="w-6 h-6 mr-2" />
            <span>{{ char.pseudo }}</span>
          </div>
        </div>
      </div>
      <!-- Roue de sélection -->
      <div class="flex flex-col items-center justify-center bg-theme-card border border-theme-border rounded shadow p-4">
        <div class="w-96 h-96 flex items-center justify-center">
          <canvas ref="wheelCanvas" width="384" height="384" class="border-4 border-theme-primary rounded-full shadow-2xl bg-theme-bg transition-all duration-300"></canvas>
        </div>
        <button class="btn btn-primary mt-8 w-full max-w-xs" :disabled="remainingPlayers.length < 2 || spinning" @click="spinWheel">
          {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
        </button>
        <div v-if="lastEliminated" class="mt-4 text-center">
          <div class="font-bold">Éliminé :</div>
          <img :src="getClassIcon(lastEliminated.class)" alt="class icon" class="w-8 h-8 inline-block mr-2" />
          <span class="text-lg">{{ lastEliminated.pseudo }}</span>
        </div>
        <div v-if="winner" class="mt-6 text-center">
          <div class="text-2xl font-bold text-theme-success">🎉 Gagnant : {{ winner.pseudo }} 🎉</div>
          <img :src="getClassIcon(winner.class)" alt="class icon" class="w-12 h-12 inline-block" />
        </div>
      </div>
      <!-- Historique des éliminés -->
      <div class="bg-theme-card border border-theme-border rounded shadow p-4 flex flex-col">
        <div class="font-bold mb-2">Historique des éliminés</div>
        <div class="flex-1 overflow-y-auto max-h-80 border border-theme-border rounded bg-theme-bg">
          <div v-for="char in eliminatedHistory" :key="char.id" class="flex items-center py-2 px-2 border-b border-theme-border last:border-b-0">
            <img :src="getClassIcon(char.class)" alt="class icon" class="w-5 h-5 mr-2" />
            <span>{{ char.pseudo }}</span>
          </div>
        </div>
      </div>
    </div>
    <!-- Contrôles -->
    <div class="flex gap-4 mt-6">
      <button class="btn btn-secondary" @click="undoElimination" :disabled="eliminatedHistory.length === 0">Annuler élimination</button>
      <button class="btn btn-warning" @click="resetWheel">Réinitialiser</button>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

interface Character {
  id: number;
  pseudo: string;
  class: string;
  selected: boolean;
  eliminated: boolean;
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
      }));
    resetWheel();
  } catch (error: any) {
    console.error('Erreur lors du chargement des personnages:', error?.response?.data || error.message);
  }
};

onMounted(fetchCharacters);

const filteredCharacters = computed(() => {
  return characters.value.filter(c => c.pseudo.toLowerCase().includes(search.value.toLowerCase()));
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
  return `/src/assets/icon_classe/${className}.avif`;
}

function spinWheel() {
  if (spinning.value || remainingPlayers.value.length < 2) return;

  // Verrouille la sélection pendant le spin (simple flag)
  spinning.value = true;

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
    if (char && !char.eliminated) {
      char.eliminated = true;
      eliminatedHistory.value.unshift(char);
      lastEliminated.value = char;
    }

    if (remainingPlayers.value.length === 1) {
      winner.value = remainingPlayers.value[0];
    }

    spinning.value = false;

    // Redessine à l’angle 0 avec l’état **actuel**
    drawWheel(0, undefined, undefined);
  }
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
  const iconSize = 40;
  const fontSize = 15;
  for (let i = 0; i < n; i++) {
    const angleStart = (i / n) * 2 * Math.PI + angle;
    const angleEnd = ((i + 1) / n) * 2 * Math.PI + angle;
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, angleStart, angleEnd);
    ctx.closePath();
    ctx.fillStyle = `hsl(${(i * 360) / n}, 70%, 60%)`;
    ctx.shadowColor = 'rgba(0,0,0,0.15)';
    ctx.shadowBlur = 8;
    ctx.fill();
    ctx.restore();
    // Position radiale pour l’icône et le texte
    const midAngle = angleStart + (angleEnd - angleStart) / 2;
    const iconRadius = radius * 0.7;
    const iconX = centerX + Math.cos(midAngle) * iconRadius - iconSize / 2;
    const iconY = centerY + Math.sin(midAngle) * iconRadius - iconSize / 2;
    // Icône de classe
    const icon = new window.Image();
    icon.src = getClassIcon(players[i].class);
    icon.onload = () => {
      ctx.save();
      ctx.beginPath();
      ctx.arc(iconX + iconSize / 2, iconY + iconSize / 2, iconSize / 2, 0, 2 * Math.PI);
      ctx.closePath();
      ctx.clip();
      ctx.drawImage(icon, iconX, iconY, iconSize, iconSize);
      ctx.restore();
    };
    if (icon.complete) {
      ctx.save();
      ctx.beginPath();
      ctx.arc(iconX + iconSize / 2, iconY + iconSize / 2, iconSize / 2, 0, 2 * Math.PI);
      ctx.closePath();
      ctx.clip();
      ctx.drawImage(icon, iconX, iconY, iconSize, iconSize);
      ctx.restore();
    }
    // Texte pseudo SOUS l’icône, à 82% du rayon
    const textRadius = radius * 0.82;
    const textX = centerX + Math.cos(midAngle) * textRadius;
    const textY = centerY + Math.sin(midAngle) * textRadius;
    ctx.save();
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#222';
    ctx.shadowColor = 'rgba(0,0,0,0.12)';
    ctx.shadowBlur = 2;
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    let pseudo = players[i].pseudo;
    if (ctx.measureText(pseudo).width > iconSize * 2.2) {
      while (pseudo.length > 0 && ctx.measureText(pseudo + '…').width > iconSize * 2.2) {
        pseudo = pseudo.slice(0, -1);
      }
      pseudo += '…';
    }
    ctx.fillText(pseudo, 0, 0);
    ctx.restore();
  }
  // Flèche
  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.rotate(0);
  ctx.beginPath();
  ctx.moveTo(0, -radius - 16);
  ctx.lineTo(-18, -radius + 18);
  ctx.lineTo(18, -radius + 18);
  ctx.closePath();
  ctx.fillStyle = '#2563eb';
  ctx.shadowColor = ctx.fillStyle;
  ctx.shadowBlur = 12;
  ctx.fill();
  ctx.restore();
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
  drawWheel();
}

watch(remainingPlayers, () => {
  drawWheel();
});
</script>

<style scoped>
.input {
  @apply border rounded px-2 py-1;
}
.btn {
  @apply px-4 py-2 rounded font-semibold shadow;
}
.btn-primary {
  @apply bg-theme-primary text-white hover:bg-theme-primary-hover;
}
.btn-secondary {
  @apply bg-theme-bg-muted text-theme-text hover:bg-theme-border;
}
.btn-warning {
  @apply bg-theme-warning text-gray-900 hover:bg-yellow-500;
}
</style>