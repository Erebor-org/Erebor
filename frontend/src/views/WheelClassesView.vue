<template>
  <div class="min-h-screen bg-theme-bg text-theme-text flex flex-col items-center p-4">
    <div class="w-full flex justify-end mb-4">
      <RouterLink to="/wheel" class="btn btn-secondary">Basculer vers la roue des membres</RouterLink>
    </div>
    <h1 class="text-2xl font-bold mb-6 text-theme-primary">Roue des Classes Dofus</h1>
    <div class="w-full max-w-3xl bg-theme-card border border-theme-border rounded shadow p-6 flex flex-col items-center">
      <div class="mb-4 text-lg font-semibold">Sélectionne les classes à inclure :</div>
      <div class="flex flex-wrap gap-4 justify-center mb-6">
        <label v-for="cls in classList" :key="cls.key" class="flex flex-col items-center cursor-pointer select-none">
          <input type="checkbox" v-model="selectedClasses" :value="cls.key" class="mb-2 accent-theme-primary w-5 h-5" />
          <img :src="cls.icon" :alt="cls.label" class="w-12 h-12 mb-1 rounded-full border border-theme-border bg-theme-bg" />
          <span class="text-xs font-medium mt-1">{{ cls.label }}</span>
        </label>
      </div>
      <button class="btn btn-primary mt-2" :disabled="selectedClasses.length < 2 || spinning" @click="spinWheel">
        {{ spinning ? 'Tirage en cours...' : 'Lancer la roue' }}
      </button>
    </div>
    <transition name="winner-pop">
      <div v-if="winner && !spinning" class="mt-8 text-center winner-anim">
        <div class="text-2xl font-bold text-theme-success">🎉 Classe gagnante : {{ winner.label }} 🎉</div>
        <img :src="winner.icon" :alt="winner.label" class="w-20 h-20 mx-auto mt-2 rounded-full border-4 border-theme-primary bg-theme-bg" />
      </div>
    </transition>
    <div v-if="spinning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80">
      <div class="flex flex-col items-center justify-center relative">
        <canvas ref="wheelCanvas" width="800" height="800" class="border-8 border-theme-primary rounded-full shadow-2xl bg-theme-bg transition-all duration-300" style="max-width:95vw; max-height:95vh;"></canvas>
        <div class="mt-8 text-white text-xl font-bold animate-pulse">Tirage en cours...</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import confetti from 'canvas-confetti';
import { classIcons } from '@/config/classIcons';
import { RouterLink } from 'vue-router';

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
        colors: ['#2563eb', '#fbbf24', '#22c55e', '#ef4444', '#a21caf']
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
  const iconSize = 80;
  const fontSize = 22;
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
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#222';
    ctx.shadowColor = 'rgba(0,0,0,0.12)';
    ctx.shadowBlur = 2;
    ctx.translate(textX, textY);
    ctx.rotate(midAngle + Math.PI / 2);
    let label = classes[i].label;
    if (ctx.measureText(label).width > iconSize * 2.2) {
      while (label.length > 0 && ctx.measureText(label + '…').width > iconSize * 2.2) {
        label = label.slice(0, -1);
      }
      label += '…';
    }
    ctx.fillText(label, 0, 0);
    ctx.restore();
  }
  // Flèche
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
  @apply px-4 py-2 rounded font-semibold shadow;
}
.btn-primary {
  @apply bg-theme-primary text-white hover:bg-theme-primary-hover;
}
.btn-secondary {
  @apply bg-theme-primary text-white hover:bg-theme-primary-hover;
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
