<template>
  <div class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Roue des Nombres</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Tirage aléatoire d'un nombre dans une plage donnée</p>
      </div>

      <div class="flex justify-center mb-8">
        <div class="inline-flex rounded-xl bg-theme-bg-muted p-1 border border-theme-border">
          <RouterLink to="/wheel" class="wheel-tab">Membres</RouterLink>
          <RouterLink to="/wheel-classes" class="wheel-tab">Classes</RouterLink>
          <RouterLink to="/wheel-numbers" class="wheel-tab wheel-tab--active">Nombres</RouterLink>
        </div>
      </div>

      <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Plage de nombres -->
        <div class="glass-card rounded-2xl p-6">
          <span class="text-sm font-semibold text-theme-text mb-4 block">Plage de nombres</span>

          <div class="flex items-center justify-center gap-4">
            <div class="flex flex-col items-center">
              <label for="minNumber" class="text-xs font-medium text-theme-text-muted mb-2 uppercase tracking-wide">Minimum</label>
              <input
                id="minNumber"
                type="number"
                v-model.number="minNumber"
                :min="1"
                :max="maxNumber - 1"
                class="wheel-number-input"
                @input="validateRange"
              />
            </div>
            <div class="text-2xl font-serif font-bold text-theme-primary mt-5">—</div>
            <div class="flex flex-col items-center">
              <label for="maxNumber" class="text-xs font-medium text-theme-text-muted mb-2 uppercase tracking-wide">Maximum</label>
              <input
                id="maxNumber"
                type="number"
                v-model.number="maxNumber"
                :min="minNumber + 1"
                class="wheel-number-input"
                @input="validateRange"
              />
            </div>
          </div>

          <div class="mt-5 text-center text-sm text-theme-text-muted">
            Plage : <span class="font-semibold text-theme-text">{{ minNumber }} à {{ maxNumber }}</span> ({{ numberRange.length }} nombres)
          </div>
        </div>

        <!-- Roue -->
        <div class="glass-card rounded-2xl p-6 flex flex-col items-center justify-center">
          <div class="wheel-ring">
            <canvas ref="wheelCanvas" width="384" height="384"></canvas>
          </div>

          <button class="wheel-spin-btn mt-6" :disabled="numberRange.length < 2 || spinning" @click="spinWheel">
            {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
          </button>

          <transition name="winner-pop">
            <div v-if="winner !== null && !spinning" class="mt-6 text-center">
              <div class="text-xl font-serif font-bold brand-gradient-text">🎉 Nombre gagnant : {{ winner }} 🎉</div>
            </div>
          </transition>
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

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import confetti from 'canvas-confetti';
import { RouterLink } from 'vue-router';
import {
  getWheelSliceColors,
  getWheelSliceGradient,
  getWheelConfettiColors,
  drawWheelRimBezel,
  drawWheelHub,
  drawWheelPointer,
} from '@/utils/wheelPalette';

const minNumber = ref(6);
const maxNumber = ref(30);
const spinning = ref(false);
const winner = ref<number | null>(null);
const wheelCanvas = ref<HTMLCanvasElement | null>(null);

let spinOrder: number[] = [];
let selectedIndex = 0;
let spinPlayersCount = 0;
let animationFrame: number | null = null;
let targetAngle = 0;
let spinStart = 0;
let spinDuration = 0;
let currentAngle = 0;

const numberRange = computed(() => {
  const range: number[] = [];
  for (let i = minNumber.value; i <= maxNumber.value; i++) {
    range.push(i);
  }
  return range;
});

function validateRange() {
  if (minNumber.value < 1) minNumber.value = 1;
  if (maxNumber.value < minNumber.value) maxNumber.value = minNumber.value + 1;
  if (numberRange.value.length < 2) {
    maxNumber.value = minNumber.value + 1;
  }
}

function spinWheel() {
  const range = numberRange.value;
  if (spinning.value || range.length < 2) return;
  spinning.value = true;
  winner.value = null;

  // Prépare la liste des nombres
  spinOrder = [...range];
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
    // Fin du spin : sélectionne le nombre gagnant
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

function drawWheel(angle = 0, nOverride?: number, numbersOverride?: number[] | null) {
  const canvas = wheelCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const numbers = numbersOverride ?? numberRange.value;
  const n = nOverride || numbers.length;
  if (n === 0) return;

  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = Math.min(centerX, centerY) - 10;
  const hubRadius = radius * 0.14;
  const fontSize = Math.max(canvas.width / 40, Math.min(canvas.width / 14, (canvas.width * 0.9) / n));
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

    // Position pour le texte
    const midAngle = angleStart + (angleEnd - angleStart) / 2;
    const textRadius = radius * 0.78;
    const textX = centerX + Math.cos(midAngle) * textRadius;
    const textY = centerY + Math.sin(midAngle) * textRadius;

    ctx.save();
    ctx.font = `bold ${fontSize}px ui-serif, Georgia, serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    ctx.lineWidth = 3;
    ctx.strokeStyle = 'rgba(0,0,0,0.55)';
    ctx.strokeText(numbers[i].toString(), 0, 0);
    ctx.fillStyle = '#ffffff';
    ctx.fillText(numbers[i].toString(), 0, 0);
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

.wheel-number-input {
  width: 6rem;
  padding: 0.6rem 0.5rem;
  border-radius: 0.65rem;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text);
  text-align: center;
  font-size: 1.1rem;
  font-weight: 700;
}
.wheel-number-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.3);
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
