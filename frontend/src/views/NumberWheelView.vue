<template>
  <div class="min-h-screen bg-theme-bg text-theme-text flex flex-col items-center p-4">
    <h1 class="text-2xl font-bold mb-6 text-theme-primary">Roue des Nombres</h1>
    <div class="w-full max-w-3xl bg-theme-card border border-theme-border rounded shadow p-6 flex flex-col items-center">
      <div class="mb-6 w-full">
        <div class="text-lg font-semibold mb-4 text-center">Sélectionne une plage de nombres :</div>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <div class="flex flex-col items-center">
            <label for="minNumber" class="text-sm font-medium mb-2">Nombre minimum</label>
            <input
              id="minNumber"
              type="number"
              v-model.number="minNumber"
              :min="1"
              :max="maxNumber - 1"
              class="w-24 px-4 py-2 border border-theme-border rounded bg-theme-bg text-theme-text text-center text-lg font-bold focus:outline-none focus:ring-2 focus:ring-theme-primary"
              @input="validateRange"
            />
          </div>
          <div class="text-2xl font-bold text-theme-primary">-</div>
          <div class="flex flex-col items-center">
            <label for="maxNumber" class="text-sm font-medium mb-2">Nombre maximum</label>
            <input
              id="maxNumber"
              type="number"
              v-model.number="maxNumber"
              :min="minNumber + 1"
              class="w-24 px-4 py-2 border border-theme-border rounded bg-theme-bg text-theme-text text-center text-lg font-bold focus:outline-none focus:ring-2 focus:ring-theme-primary"
              @input="validateRange"
            />
          </div>
        </div>
        <div class="mt-4 text-center text-sm text-theme-text-muted">
          Plage : {{ minNumber }} à {{ maxNumber }} ({{ numberRange.length }} nombres)
        </div>
      </div>
      <button 
        class="btn btn-primary mt-2" 
        :disabled="numberRange.length < 2 || spinning" 
        @click="spinWheel"
      >
        {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
      </button>
    </div>
    <transition name="winner-pop">
      <div v-if="winner !== null && !spinning" class="mt-8 text-center winner-anim">
        <div class="text-4xl font-bold text-theme-success mb-2">🎉</div>
        <div class="text-3xl font-bold text-theme-success">Nombre gagnant : {{ winner }}</div>
      </div>
    </transition>
    <div v-if="spinning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80">
      <div class="flex flex-col items-center justify-center relative">
        <canvas ref="wheelCanvas" width="1000" height="1000" class="border-8 border-theme-primary rounded-full shadow-2xl bg-theme-bg transition-all duration-300" style="max-width:95vw; max-height:95vh;"></canvas>
        <div class="mt-8 text-white text-xl font-bold animate-pulse">Tirage en cours...</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import confetti from 'canvas-confetti';

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
        colors: ['#2563eb', '#fbbf24', '#22c55e', '#ef4444', '#a21caf']
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
  const fontSize = Math.max(16, Math.min(32, 400 / n));
  
  for (let i = 0; i < n; i++) {
    const angleStart = (i / n) * 2 * Math.PI + angle;
    const angleEnd = ((i + 1) / n) * 2 * Math.PI + angle;
    
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, angleStart, angleEnd);
    ctx.closePath();
    
    // Couleur alternée pour meilleure visibilité
    const hue = (i * 360) / n;
    ctx.fillStyle = `hsl(${hue}, 70%, 60%)`;
    ctx.shadowColor = 'rgba(0,0,0,0.15)';
    ctx.shadowBlur = 8;
    ctx.fill();
    ctx.restore();
    
    // Position pour le texte
    const midAngle = angleStart + (angleEnd - angleStart) / 2;
    const textRadius = radius * 0.75;
    const textX = centerX + Math.cos(midAngle) * textRadius;
    const textY = centerY + Math.sin(midAngle) * textRadius;
    
    ctx.save();
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#222';
    ctx.shadowColor = 'rgba(255,255,255,0.8)';
    ctx.shadowBlur = 4;
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    ctx.fillText(numbers[i].toString(), 0, 0);
    ctx.restore();
  }
  
  // Flèche pointant vers le haut
  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.rotate(0);
  ctx.beginPath();
  ctx.moveTo(0, -radius - 24);
  ctx.lineTo(-28, -radius + 28);
  ctx.lineTo(28, -radius + 28);
  ctx.closePath();
  ctx.fillStyle = '#2563eb';
  ctx.shadowColor = ctx.fillStyle;
  ctx.shadowBlur = 12;
  ctx.fill();
  ctx.restore();
}
</script>

<style scoped>
.btn {
  @apply px-6 py-3 rounded font-semibold shadow transition-all;
}
.btn-primary {
  @apply bg-theme-primary text-white hover:bg-theme-primary-hover disabled:opacity-50 disabled:cursor-not-allowed;
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

