// Détermine l'insigne visuel d'un rang de guilde : une icône propre à chaque
// rang (pas une simple couleur), reconnue par nom exact. La couleur de fond
// reste liée au palier (ancienneté croissante ou direction) pour donner un
// second repère visuel, mais c'est l'icône qui porte l'identité du rang.
//
// Si un rang inconnu apparaît (renommage côté backend), on retombe sur une
// icône générique cohérente avec ses attributs réels (lead / recruiter /
// requiredDays) plutôt que de planter.

const NAMED_ICONS = {
  néophyte: { icon: 'sprout', color: 'bronze' },
  explorateur: { icon: 'binoculars', color: 'bronze' },
  sentinelle: { icon: 'halberd', color: 'silver' },
  gardien: { icon: 'shield', color: 'silver' },
  vétéran: { icon: 'sword', color: 'gold' },
  seigneur: { icon: 'scepter', color: 'gold' },
  héro: { icon: 'crossed-swords', color: 'legend' },
  légende: { icon: 'star', color: 'legend' },
  enroleur: { icon: 'flag', color: 'special' },
  animateur: { icon: 'megaphone', color: 'special' },
  conseiller: { icon: 'scroll', color: 'lead' },
  'main du roi': { icon: 'gauntlet', color: 'lead' },
  'vieux roi': { icon: 'crown', color: 'lead' },
};

export function getRankVisual(rank) {
  if (!rank) {
    return { icon: 'none', color: 'none', label: 'Aucun rang' };
  }

  const key = (rank.name || '').trim().toLowerCase();
  if (NAMED_ICONS[key]) {
    return { ...NAMED_ICONS[key], label: rank.name };
  }

  if (rank.lead) {
    return { icon: 'crown', color: 'lead', label: rank.name };
  }

  if (rank.requiredDays === null || rank.requiredDays === undefined) {
    return { icon: 'spark', color: 'special', label: rank.name };
  }

  const color =
    rank.requiredDays <= 30 ? 'bronze' :
    rank.requiredDays <= 200 ? 'silver' :
    rank.requiredDays <= 650 ? 'gold' : 'legend';
  return { icon: 'shield', color, label: rank.name };
}
