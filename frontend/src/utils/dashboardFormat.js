// Petits formateurs partagés par les blocs du tableau de bord.

const dayFormatter = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short' });
const weekdayFormatter = new Intl.DateTimeFormat('fr-FR', { weekday: 'short', day: 'numeric' });
const monthFormatter = new Intl.DateTimeFormat('fr-FR', { month: 'short' });

// Les dates du backend sont « locales » sans fuseau ("2026-11-26" ou "2026-11-26 20:00:00").
export function parseLocalDate(value) {
  if (!value) return null;
  const [datePart, timePart = '00:00:00'] = String(value).split(/[ T]/);
  const [y, m, d] = datePart.split('-').map(Number);
  const [hh, mm, ss] = timePart.split(':').map(Number);
  return new Date(y, m - 1, d, hh || 0, mm || 0, ss || 0);
}

function startOfDay(date) {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

export function daysBetween(from, to) {
  return Math.round((startOfDay(to) - startOfDay(from)) / 86400000);
}

// "aujourd'hui", "hier", "il y a 3 j", sinon "13 nov."
export function relativeDay(value, now = new Date()) {
  const date = parseLocalDate(value);
  if (!date) return '';
  const diff = daysBetween(date, now);
  if (diff === 0) return "aujourd'hui";
  if (diff === 1) return 'hier';
  if (diff > 1 && diff < 7) return `il y a ${diff} j`;
  return dayFormatter.format(date);
}

// "demain", "lun. 30", "aujourd'hui"
export function upcomingDay(value, now = new Date()) {
  const date = parseLocalDate(value);
  if (!date) return '';
  const diff = daysBetween(now, date);
  if (diff === 0) return "aujourd'hui";
  if (diff === 1) return 'demain';
  return weekdayFormatter.format(date);
}

export function shortDate(value) {
  const date = parseLocalDate(value);
  return date ? dayFormatter.format(date) : '';
}

// "2026-11" -> "nov."
export function monthLabel(yyyyMm) {
  const [y, m] = yyyyMm.split('-').map(Number);
  return monthFormatter.format(new Date(y, m - 1, 1));
}

export function signed(value) {
  if (value > 0) return `+${value}`;
  if (value < 0) return `−${Math.abs(value)}`;
  return '0';
}

export function capitalize(value) {
  return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}

export function cssVar(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

// Semaine ISO ("2026-W48") décalée de `offset` semaines.
export function shiftIsoWeek(isoWeek, offset) {
  const [y, w] = isoWeek.split('-W').map(Number);
  // Le 4 janvier est toujours en semaine 1.
  const jan4 = new Date(Date.UTC(y, 0, 4));
  const monday = new Date(jan4);
  monday.setUTCDate(jan4.getUTCDate() - ((jan4.getUTCDay() + 6) % 7) + (w - 1 + offset) * 7);
  const thursday = new Date(monday);
  thursday.setUTCDate(monday.getUTCDate() + 3);
  const year = thursday.getUTCFullYear();
  const firstJan = new Date(Date.UTC(year, 0, 1));
  const week = Math.ceil(((thursday - firstJan) / 86400000 + 1) / 7);
  return `${year}-W${String(week).padStart(2, '0')}`;
}

// Classe de couleur d'un taux de rétention (pour les cellules de tableau).
export function rateTone(rate) {
  if (rate == null) return 'is-empty';
  if (rate >= 75) return 'is-high';
  if (rate >= 50) return 'is-mid';
  return 'is-low';
}
