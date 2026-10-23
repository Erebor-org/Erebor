// Display helpers for the Guild Wars 2 section: French labels and in-game colors.

export const PROFESSIONS = {
  Guardian: { label: 'Gardien', color: '#72C1D9' },
  Warrior: { label: 'Guerrier', color: '#FFD166' },
  Revenant: { label: 'Revenant', color: '#D16E5A' },
  Engineer: { label: 'Ingénieur', color: '#D09C59' },
  Ranger: { label: 'Rôdeur', color: '#8CDC82' },
  Thief: { label: 'Voleur', color: '#C08F95' },
  Elementalist: { label: 'Élémentaliste', color: '#F68A87' },
  Mesmer: { label: 'Envoûteur', color: '#B679D5' },
  Necromancer: { label: 'Nécromant', color: '#52A76F' },
};

export const RACES = { Human: 'Humain', Norn: 'Norn', Charr: 'Charr', Asura: 'Asura', Sylvari: 'Sylvari' };

export const CRAFTING = {
  Armorsmith: "Forgeron d'armures",
  Artificer: 'Artificier',
  Chef: 'Maître-queux',
  Huntsman: 'Chasseur',
  Jeweler: 'Bijoutier',
  Leatherworker: 'Travailleur du cuir',
  Scribe: 'Scribe',
  Tailor: 'Tailleur',
  Weaponsmith: "Forgeron d'armes",
};

export const RARITY_COLORS = {
  Junk: '#AAAAAA',
  Basic: '#C8C8C8',
  Fine: '#62A4DA',
  Masterwork: '#1A9306',
  Rare: '#E0B800',
  Exotic: '#FFA405',
  Ascended: '#FB3E8D',
  Legendary: '#8E4FD6',
};

export const RARITY_LABELS = {
  Junk: 'Inutile',
  Basic: 'Simple',
  Fine: 'Raffiné',
  Masterwork: "Chef-d'œuvre",
  Rare: 'Rare',
  Exotic: 'Exotique',
  Ascended: 'Élevé',
  Legendary: 'Légendaire',
};

export const ATTRIBUTES = {
  Power: 'Puissance',
  Precision: 'Précision',
  Toughness: 'Robustesse',
  Vitality: 'Vitalité',
  CritDamage: 'Férocité',
  ConditionDamage: 'Dégâts par altération',
  Healing: 'Guérison',
  BoonDuration: 'Concentration',
  ConditionDuration: 'Expertise',
  AgonyResistance: "Résistance à l'agonie",
};

export const WEAPON_TYPES = {
  Axe: 'Hache',
  Dagger: 'Dague',
  Mace: 'Masse',
  Pistol: 'Pistolet',
  Scepter: 'Sceptre',
  Sword: 'Épée',
  Focus: 'Focus',
  Shield: 'Bouclier',
  Torch: 'Torche',
  Warhorn: 'Cor de guerre',
  Greatsword: 'Espadon',
  Hammer: 'Marteau',
  LongBow: 'Arc long',
  Rifle: 'Fusil',
  ShortBow: 'Arc court',
  Staff: 'Bâton',
  Harpoon: 'Lance',
  Speargun: 'Fusil-harpon',
  Trident: 'Trident',
};

export const WEIGHT_CLASSES = { Heavy: 'Lourde', Medium: 'Intermédiaire', Light: 'Légère', Clothing: 'Vêtement' };

export const BINDINGS = { Character: "Lié à l'âme", Account: 'Lié au compte' };

export const ITEM_TYPES = {
  Armor: 'Armure',
  Weapon: 'Arme',
  Trinket: 'Colifichet',
  Back: 'Objet de dos',
  Bag: 'Sac',
  Consumable: 'Consommable',
  Container: 'Conteneur',
  CraftingMaterial: "Matériau d'artisanat",
  Gathering: 'Outil de récolte',
  Gizmo: 'Gizmo',
  MiniPet: 'Miniature',
  Tool: 'Outil',
  Trophy: 'Trophée',
  UpgradeComponent: 'Amélioration',
};

// What to do with an item stack (Gw2SellAdvisor actions), in display order
export const ADVICE_ACTIONS = {
  sell: { label: 'Vendre au comptoir', short: 'Comptoir', color: 'var(--success)' },
  salvage: { label: 'Recycler', short: 'Recycler', color: '#62A4DA' },
  vendor: { label: 'Vendre au marchand', short: 'Marchand', color: '#E0B800' },
  keep: { label: 'Garder', short: 'Garder', color: 'var(--text-muted)' },
};

export const GENERATIONS = {
  1: { label: 'Gén. 1', title: 'Première génération (jeu de base)' },
  2: { label: 'Gén. 2', title: 'Deuxième génération (Heart of Thorns)' },
  3: { label: 'Gén. 3', title: "Troisième génération (End of Dragons, armes d'Aurène)" },
};

// How a legendary tree node gets done (Gw2LegendaryPlanner decisions and recipe sources)
export const PLAN_DECISIONS = {
  owned: { label: 'Possédé', color: 'var(--success)' },
  buy: { label: 'Acheter', color: '#E0B800' },
  craft: { label: 'Fabriquer', color: '#62A4DA' },
  objective: { label: 'Objectif', color: '#B679D5' },
};

export const RECIPE_SOURCES = {
  mystic_forge: 'Forge mystique',
  crafting: 'Artisanat',
  vendor: 'Marchand',
};

export const STOCK_LOCATIONS = {
  bank: 'Banque',
  materials: 'Stockage',
  inventory: 'Sacs',
  shared: 'Partagé',
  wallet: 'Portefeuille',
};

export const SLOTS = {
  Helm: 'Casque',
  Shoulders: 'Épaulières',
  Coat: 'Torse',
  Gloves: 'Gants',
  Leggings: 'Jambières',
  Boots: 'Bottes',
  Backpack: 'Dos',
  Amulet: 'Amulette',
  Accessory1: 'Accessoire 1',
  Accessory2: 'Accessoire 2',
  Ring1: 'Anneau 1',
  Ring2: 'Anneau 2',
  WeaponA1: 'Arme A1',
  WeaponA2: 'Arme A2',
  WeaponB1: 'Arme B1',
  WeaponB2: 'Arme B2',
  HelmAquatic: 'Respirateur',
  WeaponAquaticA: 'Arme aquatique A',
  WeaponAquaticB: 'Arme aquatique B',
  Relic: 'Relique',
};

// Order the equipment like the in-game hero panel
export const SLOT_ORDER = Object.keys(SLOTS);

export const PERMISSION_LABELS = {
  account: 'Compte',
  inventories: 'Inventaires',
  characters: 'Personnages',
  wallet: 'Portefeuille',
  tradingpost: 'Comptoir',
  unlocks: 'Déblocages',
  progression: 'Progression',
  builds: 'Builds',
  guilds: 'Guildes',
  pvp: 'JcJ',
  wvw: 'McM',
};

// Copper amount split the in-game way: 1 po (gold) = 100 pa (silver) = 10 000 pc (copper)
export function splitCoins(copper) {
  const value = Math.max(0, Math.round(copper || 0));
  return { gold: Math.floor(value / 10000), silver: Math.floor((value % 10000) / 100), copper: value % 100 };
}

export const attributeLabel = (attribute) => ATTRIBUTES[attribute] || attribute;
export const rarityColor = (rarity) => RARITY_COLORS[rarity] || 'var(--border)';
export const capitalize = (text) => (text ? text.charAt(0).toUpperCase() + text.slice(1) : text);

export const professionLabel = (profession) => PROFESSIONS[profession]?.label || profession;
export const professionColor = (profession) => PROFESSIONS[profession]?.color || 'var(--primary)';

// "age" is the played time, in seconds
export function formatPlayedTime(seconds) {
  const hours = Math.floor((seconds || 0) / 3600);
  if (hours < 24) return `${hours} h`;
  return `${Math.floor(hours / 24)} j ${hours % 24} h`;
}

export function formatDate(iso) {
  return iso ? new Date(iso).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
}
