// Configuration centralisée des couleurs du thème
// Modifiez ce fichier pour changer toutes les couleurs du site
// NB: la valeur réellement appliquée à l'exécution vient de src/stores/themeStore.js
// (ce fichier sert de référence / d'export pour d'éventuels usages externes).

export const themeColors = {
  light: {
    // Backgrounds
    bg: '#ffffff',
    'bg-muted': '#FAF7F2',
    card: '#ffffff',
    'card-hover': '#FBF4E9',

    // Borders
    border: '#ECE6DC',
    'border-hover': '#DCD3C4',
    'border-focus': '#9E1B32',

    // Text
    text: '#1A1412',
    'text-muted': '#6B625B',
    'text-light': '#A79E93',

    // Primary colors (rouge de guilde)
    primary: '#9E1B32',
    'primary-hover': '#7E1527',
    'primary-light': '#F7E4E7',

    // Accent (or)
    accent: '#C9A227',
    'accent-hover': '#A98620',
    'accent-light': '#FBF1D6',

    // Accents
    link: '#9E1B32',
    ring: '#E3AEB8',

    // Status colors
    success: '#16a34a',
    'success-light': '#dcfce7',
    warning: '#C9A227',
    'warning-light': '#FBF1D6',
    error: '#DC2626',
    'error-light': '#fee2e2',

    // Interactive elements
    button: '#9E1B32',
    'button-hover': '#7E1527',
    'button-secondary': '#FAF7F2',
    'button-secondary-hover': '#ECE6DC',

    // Special elements
    header: '#9E1B32',
    'header-text': '#ffffff',
    toggle: '#C9A227',
    'toggle-active': '#9E1B32',

    // Shadows
    shadow: '0 1px 2px rgba(26,20,18,0.04), 0 4px 16px rgba(26,20,18,0.06)',
    'shadow-hover': '0 8px 28px rgba(26,20,18,0.12)',
  },

  dark: {
    // Backgrounds
    bg: '#0F0B0A',
    'bg-muted': '#17110F',
    card: '#1C1512',
    'card-hover': '#241A16',

    // Borders
    border: '#2E211C',
    'border-hover': '#3D2B23',
    'border-focus': '#E3B23C',

    // Text
    text: '#F5EFE6',
    'text-muted': '#B3A79B',
    'text-light': '#7A6F63',

    // Primary colors
    primary: '#E14456',
    'primary-hover': '#C22E3F',
    'primary-light': '#3A1418',

    // Accent (or)
    accent: '#E3B23C',
    'accent-hover': '#C9992A',
    'accent-light': '#3A2E12',

    // Accents
    link: '#E3B23C',
    ring: '#6B2530',

    // Status colors
    success: '#22c55e',
    'success-light': '#1e3a2e',
    warning: '#E3B23C',
    'warning-light': '#3d2c0d',
    error: '#ef4444',
    'error-light': '#3d1d1d',

    // Interactive elements
    button: '#E14456',
    'button-hover': '#C22E3F',
    'button-secondary': '#1C1512',
    'button-secondary-hover': '#2E211C',

    // Special elements
    header: '#1C1512',
    'header-text': '#F5EFE6',
    toggle: '#E3B23C',
    'toggle-active': '#E14456',

    // Shadows
    shadow: '0 1px 2px rgba(0,0,0,0.2), 0 4px 16px rgba(0,0,0,0.35)',
    'shadow-hover': '0 12px 32px rgba(0,0,0,0.5)',
  }
};

// Fonction pour obtenir la couleur actuelle
export function getThemeColor(colorKey, theme = 'light') {
  return themeColors[theme]?.[colorKey] || themeColors.light[colorKey];
}

// Fonction pour obtenir toutes les couleurs d'un thème
export function getThemeColors(theme = 'light') {
  return themeColors[theme] || themeColors.light;
}
