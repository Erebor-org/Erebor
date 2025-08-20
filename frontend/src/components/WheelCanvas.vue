<template>
  <div class="flex flex-col items-center justify-center">
    <canvas ref="wheelCanvas" :width="size" :height="size" class="border-4 border-theme-primary rounded-full shadow-2xl bg-theme-bg transition-all duration-300"></canvas>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import type { Character } from '@/stores/memberWheelStore';

const props = defineProps<{
  players: Character[];
  angle?: number;
  size?: number;
}>();

const size = props.size || 384;
const wheelCanvas = ref<HTMLCanvasElement|null>(null);

function drawWheel(angle = 0) {
  const canvas = wheelCanvas.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  const players = props.players;
  const n = players.length;
  if (n === 0) return;
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = Math.min(centerX, centerY) - 10;
  const iconSize = 40;
  const fontSize = 15;
  for (let i = 0; i < n; i++) {
    const angleStart = (i / n) * 2 * Math.PI + (props.angle || 0);
    const angleEnd = ((i + 1) / n) * 2 * Math.PI + (props.angle || 0);
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
    icon.src = `/src/assets/icon_classe/${players[i].class}.avif`;
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

watch(
  () => [props.players, props.angle],
  () => nextTick(() => drawWheel(props.angle || 0)),
  { deep: true }
);
onMounted(() => drawWheel(props.angle || 0));
</script>
