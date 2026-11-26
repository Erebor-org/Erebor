// Traduit les chiffres des tableaux de bord en phrases compréhensibles sans explication :
// pas de pourcentages, pas de « n = », pas de « J+61 ».

const plural = (n, one, many) => (Math.abs(n) > 1 ? many : one);

// Palier de rétention en jours → délai parlant.
const MILESTONE_LABELS = { 14: '2 semaines', 61: '2 mois', 122: '4 mois' };
export function milestoneLabel(days) {
  return MILESTONE_LABELS[days] || durationLabel(days);
}

// Nombre de jours → durée lisible : « 5 jours », « 3 semaines », « 4 mois », « 2 ans et 3 mois ».
export function durationLabel(days) {
  const d = Math.max(0, Math.round(days));
  if (d < 1) return "moins d'un jour";
  if (d < 14) return `${d} ${plural(d, 'jour', 'jours')}`;
  if (d < 60) {
    const w = Math.round(d / 7);
    return `${w} semaines`;
  }
  if (d < 365) {
    const m = Math.round(d / 30.4);
    return `${m} mois`;
  }
  const years = Math.floor(d / 365.25);
  const months = Math.floor((d - years * 365.25) / 30.4);
  const y = `${years} ${plural(years, 'an', 'ans')}`;
  return months > 0 ? `${y} et ${months} mois` : y;
}

// Ancienneté d'une recrue : « arrivé aujourd'hui », « arrivé hier », « arrivé il y a 5 jours ».
export function arrivedLabel(days) {
  if (days <= 0) return "arrivé aujourd'hui";
  if (days === 1) return 'arrivé hier';
  return `arrivé il y a ${durationLabel(days)}`;
}

// Écart entre deux nombres de personnes : « 2 de plus que <ref> », « autant que <ref> ».
export function compareCount(delta, reference) {
  if (delta == null) return '';
  const que = /^[aeiouyéèh]/i.test(reference) ? "qu'" : 'que ';
  if (delta === 0) return `autant ${que}${reference}`;
  return `${Math.abs(delta)} de ${delta > 0 ? 'plus' : 'moins'} ${que}${reference}`;
}

// Comparaison à une habitude (moyenne) : « plus que d'habitude », « comme d'habitude »…
// Une marge de 25 % évite de qualifier de « plus » un écart insignifiant.
export function habitLabel(value, average) {
  if (average == null || average === 0) return value > 0 ? "plus que d'habitude" : "comme d'habitude";
  const ratio = value / average;
  if (ratio >= 1.25) return "plus que d'habitude";
  if (ratio <= 0.75) return "moins que d'habitude";
  return "comme d'habitude";
}

// Évolution d'un taux : « mieux que le mois dernier », « moins bien que… », « comme… ».
// En dessous de 3 points d'écart, on considère que rien n'a changé.
export function trendLabel(deltaPoints, reference = 'le mois dernier') {
  if (deltaPoints == null) return '';
  if (deltaPoints >= 3) return `mieux que ${reference}`;
  if (deltaPoints <= -3) return `moins bien que ${reference}`;
  return `comme ${reference}`;
}

// « 7 sur 10 » arrondi, pour une proportion lisible d'un coup d'œil.
export function outOfTen(retained, total) {
  if (!total) return null;
  return Math.round((10 * retained) / total);
}

// Niveau d'une proportion en mots (sert aussi à la couleur) : bien / moyen / faible.
export function keptLevel(retained, total) {
  if (!total) return 'none';
  const ratio = retained / total;
  if (ratio >= 0.75) return 'good';
  if (ratio >= 0.5) return 'fair';
  return 'low';
}

export function ordinalFr(n) {
  return n === 1 ? '1er' : `${n}e`;
}
