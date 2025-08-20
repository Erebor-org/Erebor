// Configuration centralisée des couleurs du thème
// Modifiez ce fichier pour changer toutes les couleurs du site

export const themeColors = {
  light: {
    // Backgrounds
    bg: '#ffffff',
    'bg-muted': '#f7f8fa',
    card: '#ffffff',
    'card-hover': '#f0f2f5',
    
    // Borders
    border: '#e6e8ec',
    'border-hover': '#d1d5db',
    'border-focus': '#2563eb',
    
    // Text
    text: '#0b1220',
    'text-muted': '#5b6472',
    'text-light': '#9ca3af',
    
    // Primary colors
    primary: '#2563eb',
    'primary-hover': '#1d4ed8',
    'primary-light': '#dbeafe',
    
    // Accents
    link: '#2563eb',
    ring: '#93c5fd',
    
    // Status colors
    success: '#16a34a',
    'success-light': '#dcfce7',
    warning: '#f59e0b',
    'warning-light': '#fef3c7',
    error: '#dc2626',
    'error-light': '#fee2e2',
    
    // Interactive elements
    button: '#2563eb',
    'button-hover': '#1d4ed8',
    'button-secondary': '#f3f4f6',
    'button-secondary-hover': '#e5e7eb',
    
    // Special elements
    header: '#2563eb',
    'header-text': '#ffffff',
    toggle: '#f59e0b',
    'toggle-active': '#2563eb',
    
    // Shadows
    shadow: '0 4px 16px rgba(0, 0, 0, 0.06)',
    'shadow-hover': '0 8px 25px rgba(0, 0, 0, 0.1)',
  },
  
  dark: {
    // Backgrounds
    bg: '#0b0b0f',
    'bg-muted': '#121218',
    card: '#14141b',
    'card-hover': '#1a1a22',
    
    // Borders
    border: '#262633',
    'border-hover': '#3a3a4a',
    'border-focus': '#d946ef',
    
    // Text
    text: '#f4f6fb',
    'text-muted': '#a8adbb',
    'text-light': '#6b7280',
    
    // Primary colors
    primary: '#d946ef',
    'primary-hover': '#c026d3',
    'primary-light': '#2d1b3d',
    
    // Accents
    link: '#e879f9',
    ring: '#f0abfc',
    
    // Status colors
    success: '#22c55e',
    'success-light': '#1e3a2e',
    warning: '#facc15',
    'warning-light': '#3d2c0d',
    error: '#ef4444',
    'error-light': '#3d1d1d',
    
    // Interactive elements
    button: '#d946ef',
    'button-hover': '#c026d3',
    'button-secondary': '#1a1a22',
    'button-secondary-hover': '#262633',
    
    // Special elements
    header: '#d946ef',
    'header-text': '#ffffff',
    toggle: '#facc15',
    'toggle-active': '#d946ef',
    
    // Shadows
    shadow: '0 4px 16px rgba(0, 0, 0, 0.24)',
    'shadow-hover': '0 8px 25px rgba(0, 0, 0, 0.35)',
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
