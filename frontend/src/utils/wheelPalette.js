// Palette rouge/or de la guilde pour les roues de tirage (canvas ne sait pas
// résoudre les variables CSS, donc on lit leur valeur calculée à l'affichage).

function hexToRgb(hex) {
  const clean = hex.replace('#', '');
  const bigint = parseInt(clean.length === 3 ? clean.split('').map(c => c + c).join('') : clean, 16);
  return { r: (bigint >> 16) & 255, g: (bigint >> 8) & 255, b: bigint & 255 };
}

function mixColors(hexA, hexB, t) {
  const a = hexToRgb(hexA);
  const b = hexToRgb(hexB);
  const r = Math.round(a.r + (b.r - a.r) * t);
  const g = Math.round(a.g + (b.g - a.g) * t);
  const bl = Math.round(a.b + (b.b - a.b) * t);
  return `rgb(${r}, ${g}, ${bl})`;
}

export function cssVar(name, fallback) {
  if (typeof document === 'undefined') return fallback;
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function paletteStops() {
  return [
    cssVar('--primary', '#9E1B32'),
    cssVar('--accent', '#C9A227'),
    cssVar('--primary-hover', '#7E1527'),
    cssVar('--accent-hover', '#A98620'),
  ];
}

export function getWheelSliceColors(count) {
  const stops = paletteStops();
  if (count <= 1) return [stops[0]];
  const colors = [];
  for (let i = 0; i < count; i++) {
    const t = i / (count - 1);
    const scaled = t * (stops.length - 1);
    const segment = Math.min(Math.floor(scaled), stops.length - 2);
    const localT = scaled - segment;
    colors.push(mixColors(stops[segment], stops[segment + 1], localT));
  }
  return colors;
}

export function getWheelConfettiColors() {
  return [...paletteStops(), cssVar('--text', '#F5EFE6')];
}

function parseRgb(color) {
  const match = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
  if (!match) return { r: 158, g: 27, b: 50 };
  return { r: +match[1], g: +match[2], b: +match[3] };
}

function shade(rgb, amount) {
  const clamp = (v) => Math.max(0, Math.min(255, v));
  return `rgb(${clamp(rgb.r + amount)}, ${clamp(rgb.g + amount)}, ${clamp(rgb.b + amount)})`;
}

// Dégradé radial (clair au centre, sombre au bord) au lieu d'un aplat, pour
// donner un effet de dôme/relief à chaque tranche plutôt qu'un rendu plat.
export function getWheelSliceGradient(ctx, centerX, centerY, hubRadius, radius, baseColor) {
  const rgb = parseRgb(baseColor);
  const gradient = ctx.createRadialGradient(centerX, centerY, hubRadius * 0.5, centerX, centerY, radius);
  gradient.addColorStop(0, shade(rgb, 45));
  gradient.addColorStop(0.6, baseColor);
  gradient.addColorStop(1, shade(rgb, -35));
  return gradient;
}

// Liseré biseauté rouge/or autour de la roue.
export function drawWheelRimBezel(ctx, centerX, centerY, radius) {
  ctx.save();
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.lineWidth = Math.max(4, radius / 30);
  const gradient = ctx.createLinearGradient(centerX - radius, centerY - radius, centerX + radius, centerY + radius);
  gradient.addColorStop(0, cssVar('--accent', '#C9A227'));
  gradient.addColorStop(0.5, cssVar('--primary', '#9E1B32'));
  gradient.addColorStop(1, cssVar('--accent', '#C9A227'));
  ctx.strokeStyle = gradient;
  ctx.stroke();
  ctx.restore();
}

// Moyeu central façon médaillon, pour ne plus laisser un simple point de
// jonction nu au centre de la roue.
export function drawWheelHub(ctx, centerX, centerY, hubRadius) {
  ctx.save();
  const gradient = ctx.createRadialGradient(
    centerX - hubRadius * 0.3, centerY - hubRadius * 0.35, hubRadius * 0.1,
    centerX, centerY, hubRadius
  );
  gradient.addColorStop(0, '#fff6df');
  gradient.addColorStop(0.45, cssVar('--accent', '#C9A227'));
  gradient.addColorStop(1, cssVar('--primary-hover', '#7E1527'));
  ctx.beginPath();
  ctx.arc(centerX, centerY, hubRadius, 0, 2 * Math.PI);
  ctx.fillStyle = gradient;
  ctx.shadowColor = 'rgba(0,0,0,0.45)';
  ctx.shadowBlur = hubRadius * 0.5;
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.lineWidth = Math.max(1.5, hubRadius / 12);
  ctx.strokeStyle = 'rgba(255,255,255,0.55)';
  ctx.stroke();
  ctx.restore();
}

// Flèche/pointeur en relief (dégradé + halo) au lieu d'un triangle plat.
// Les dimensions sont proportionnelles au rayon pour rester cohérentes
// entre le petit canvas et le grand canvas plein écran pendant le spin.
export function drawWheelPointer(ctx, centerX, centerY, radius) {
  const armLength = radius * 0.16;
  const armWidth = radius * 0.19;
  ctx.save();
  ctx.translate(centerX, centerY);
  ctx.beginPath();
  ctx.moveTo(0, -radius - armLength);
  ctx.lineTo(-armWidth, -radius + armLength * 0.2);
  ctx.lineTo(armWidth, -radius + armLength * 0.2);
  ctx.closePath();
  const gradient = ctx.createLinearGradient(0, -radius - armLength, 0, -radius + armLength * 0.2);
  gradient.addColorStop(0, '#fff6df');
  gradient.addColorStop(1, cssVar('--primary', '#9E1B32'));
  ctx.fillStyle = gradient;
  ctx.shadowColor = cssVar('--accent', '#C9A227');
  ctx.shadowBlur = radius * 0.06;
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.lineWidth = 1.5;
  ctx.strokeStyle = 'rgba(0,0,0,0.4)';
  ctx.stroke();
  ctx.restore();
}
