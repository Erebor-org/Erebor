<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Roue des Classes</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Tirage aléatoire parmi les classes sélectionnées</p>
      </div>

      <div class="flex justify-center mb-8">
        <div class="inline-flex rounded-xl bg-theme-bg-muted p-1 border border-theme-border">
          <RouterLink to="/wheel" class="wheel-tab">Membres</RouterLink>
          <RouterLink to="/wheel-classes" class="wheel-tab wheel-tab--active">Classes</RouterLink>
          <RouterLink to="/wheel-numbers" class="wheel-tab">Nombres</RouterLink>
        </div>
      </div>

      <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Sélection des classes -->
        <div class="glass-card rounded-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-theme-text">Classes à inclure</span>
            <button @click="toggleSelectAll" class="text-theme-primary hover:text-theme-primary-hover text-xs font-semibold">
              {{ selectedClasses.length === classList.length ? 'Tout désélectionner' : 'Tout sélectionner' }}
            </button>
          </div>

          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <button
              v-for="cls in classList"
              :key="cls.key"
              type="button"
              @click="toggleClass(cls.key)"
              class="wheel-class-tile"
              :class="{ 'wheel-class-tile--active': selectedClasses.includes(cls.key) }"
            >
              <img :src="cls.icon" :alt="cls.label" class="w-10 h-10 rounded-lg" />
              <span class="text-xs font-medium mt-1.5">{{ cls.label }}</span>
              <div v-if="selectedClasses.includes(cls.key)" class="wheel-class-check">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Roue -->
        <div class="glass-card rounded-2xl p-6 flex flex-col items-center justify-center">
          <div class="wheel-ring">
            <canvas ref="wheelCanvas" width="384" height="384"></canvas>
          </div>

          <button class="wheel-spin-btn mt-6" :disabled="selectedClasses.length < 2 || spinning" @click="spinWheel">
            {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
          </button>

          <transition name="winner-pop">
            <div v-if="winner && !spinning" class="mt-6 text-center">
              <div class="text-xl font-serif font-bold brand-gradient-text">🎉 Classe gagnante : {{ winner.label }} 🎉</div>
              <img :src="winner.icon" :alt="winner.label" class="w-16 h-16 mx-auto mt-2 rounded-full border-2 border-theme-primary" />
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>

  <div v-if="spinning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="flex flex-col items-center justify-center">
      <div class="wheel-ring wheel-ring--big">
        <canvas ref="wheelCanvas" width="800" height="800" style="max-width:85vw; max-height:85vh;"></canvas>
      </div>
      <div class="mt-8 text-theme-text text-xl font-serif font-bold animate-pulse">Tirage en cours...</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import confetti from 'canvas-confetti';
import { classIcons } from '@/config/classIcons';
import { RouterLink } from 'vue-router';
import {
  getWheelSliceColors,
  getWheelSliceGradient,
  getWheelConfettiColors,
  drawWheelRimBezel,
  drawWheelHub,
  drawWheelPointer,
} from '@/utils/wheelPalette';

const classList = [
  { key: 'iop', label: 'Iop', icon: classIcons.iop },
  { key: 'enutrof', label: 'Enutrof', icon: classIcons.enutrof },
  { key: 'cra', label: 'Cra', icon: classIcons.cra },
  { key: 'sram', label: 'Sram', icon: classIcons.sram },
  { key: 'osamodas', label: 'Osamodas', icon: classIcons.osamodas },
  { key: 'sacrieur', label: 'Sacrieur', icon: classIcons.sacrieur },
  { key: 'pandawa', label: 'Pandawa', icon: classIcons.pandawa },
  { key: 'ecaflip', label: 'Ecaflip', icon: classIcons.ecaflip },
  { key: 'eniripsa', label: 'Eniripsa', icon: classIcons.eniripsa },
  { key: 'feca', label: 'Féca', icon: classIcons.feca },
  { key: 'xelor', label: 'Xélor', icon: classIcons.xelor },
  { key: 'sadida', label: 'Sadida', icon: classIcons.sadida },
  { key: 'roublard', label: 'Roublard', icon: classIcons.roublard },
  { key: 'steamer', label: 'Steamer', icon: classIcons.steamer },
  { key: 'ouginak', label: 'Ouginak', icon: classIcons.ouginak },
  { key: 'huppermage', label: 'Huppermage', icon: classIcons.huppermage },
  { key: 'eliotrope', label: 'Eliotrope', icon: classIcons.eliotrope },
  { key: 'forgelance', label: 'Forgelance', icon: classIcons.forgelance },
  { key: 'zobal', label: 'Zobal', icon: classIcons.zobal },
];

const selectedClasses = ref<string[]>(classList.map(c => c.key));
const spinning = ref(false);
const winner = ref<{ key: string; label: string; icon: string } | null>(null);
const wheelCanvas = ref<HTMLCanvasElement | null>(null);
let spinOrder: typeof classList = [];
let selectedIndex = 0;
let spinPlayersCount = 0;
let animationFrame: number | null = null;
let targetAngle = 0;
let spinStart = 0;
let spinDuration = 0;
let currentAngle = 0;

function toggleClass(key: string) {
  if (selectedClasses.value.includes(key)) {
    selectedClasses.value = selectedClasses.value.filter(k => k !== key);
  } else {
    selectedClasses.value.push(key);
  }
}

function toggleSelectAll() {
  selectedClasses.value = selectedClasses.value.length === classList.length
    ? []
    : classList.map(c => c.key);
}

function spinWheel() {
  if (spinning.value || selectedClasses.value.length < 2) return;
  spinning.value = true;
  winner.value = null;
  // Prépare la liste des classes sélectionnées
  spinOrder = classList.filter(c => selectedClasses.value.includes(c.key));
  spinPlayersCount = spinOrder.length;
  selectedIndex = Math.floor(Math.random() * spinPlayersCount);
  const seg = (2 * Math.PI) / spinPlayersCount;
  const baseTurns = 5 + Math.floor(Math.random() * 2); // 5–6 tours
  targetAngle = baseTurns * 2 * Math.PI - (selectedIndex + 0.5) * seg - Math.PI / 2;
  spinStart = performance.now();
  spinDuration = 3200 + Math.random() * 800;
  animateWheel();
}

function animateWheel(now?: number) {
  if (!now) now = performance.now();
  const elapsed = now - spinStart;
  const t = Math.min(elapsed / spinDuration, 1);
  currentAngle = (1 - Math.pow(1 - t, 3)) * targetAngle;
  drawWheel(currentAngle, spinPlayersCount, spinOrder);
  if (t < 1) {
    animationFrame = requestAnimationFrame(animateWheel);
  } else {
    // Fin du spin : sélectionne la classe gagnante
    winner.value = spinOrder[selectedIndex];
    spinning.value = false;
    nextTick(() => {
      confetti({
        particleCount: 120,
        spread: 80,
        origin: { y: 0.6 },
        zIndex: 9999,
        colors: getWheelConfettiColors()
      });
    });
    drawWheel(0, undefined, undefined);
  }
}

function drawWheel(angle = 0, nOverride?: number, classesOverride?: typeof classList | null) {
  const canvas = wheelCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  const classes = classesOverride ?? classList.filter(c => selectedClasses.value.includes(c.key));
  const n = nOverride || classes.length;
  if (n === 0) return;
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = Math.min(centerX, centerY) - 10;
  const hubRadius = radius * 0.14;
  const iconSize = canvas.width / 9.6;
  const fontSize = canvas.width / 26;
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
    const icon = new window.Image();
    icon.src = classes[i].icon;
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
    // Texte nom de la classe SOUS l’icône
    const textRadius = radius * 0.82;
    const textX = centerX + Math.cos(midAngle) * textRadius;
    const textY = centerY + Math.sin(midAngle) * textRadius;
    ctx.save();
    ctx.font = `bold ${fontSize}px ui-serif, Georgia, serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    let label = classes[i].label;
    if (ctx.measureText(label).width > iconSize * 2.2) {
      while (label.length > 0 && ctx.measureText(label + '…').width > iconSize * 2.2) {
        label = label.slice(0, -1);
      }
      label += '…';
    }
    ctx.lineWidth = 3;
    ctx.strokeStyle = 'rgba(0,0,0,0.55)';
    ctx.strokeText(label, 0, 0);
    ctx.fillStyle = '#ffffff';
    ctx.fillText(label, 0, 0);
    ctx.restore();
  }
  drawWheelRimBezel(ctx, centerX, centerY, radius);
  drawWheelHub(ctx, centerX, centerY, hubRadius);
  drawWheelPointer(ctx, centerX, centerY, radius);
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

.wheel-class-tile {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.85rem 0.5rem;
  border-radius: 0.85rem;
  border: 2px solid var(--border);
  background-color: rgba(255, 255, 255, 0.02);
  transition: border-color 0.2s, background-color 0.2s, transform 0.15s;
}
.wheel-class-tile:hover {
  border-color: var(--primary);
  transform: translateY(-1px);
}
.wheel-class-tile--active {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.12);
}
.wheel-class-check {
  position: absolute;
  top: -0.4rem;
  right: -0.4rem;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 9999px;
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
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
