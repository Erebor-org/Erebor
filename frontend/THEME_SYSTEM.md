# Système de Thèmes Erebor

## Vue d'ensemble

Le projet Erebor implémente un système de thèmes complet avec support light/dark utilisant des CSS Custom Properties (variables CSS) et Tailwind CSS. Aucune couleur n'est codée en dur dans les composants.

## Palettes de Couleurs

### Light Theme - "Blanc & Bleu"

- **--bg**: #ffffff (fond principal)
- **--bg-muted**: #f7f8fa (fond secondaire)
- **--card**: #ffffff (fond des cartes)
- **--border**: #e6e8ec (bordures)
- **--text**: #0b1220 (texte principal)
- **--text-muted**: #5b6472 (texte secondaire)
- **--primary**: #2563eb (couleur principale - bleu)
- **--primary-hover**: #1d4ed8 (couleur principale au survol)
- **--link**: #2563eb (liens)
- **--ring**: #93c5fd (outline/focus)
- **--success**: #16a34a (succès)
- **--warning**: #f59e0b (attention)
- **--error**: #dc2626 (erreur)

### Dark Theme - "Noir & Magenta"

- **--bg**: #0b0b0f (fond principal)
- **--bg-muted**: #121218 (fond secondaire)
- **--card**: #14141b (fond des cartes)
- **--border**: #262633 (bordures)
- **--text**: #f4f6fb (texte principal)
- **--text-muted**: #a8adbb (texte secondaire)
- **--primary**: #d946ef (couleur principale - magenta)
- **--primary-hover**: #c026d3 (couleur principale au survol)
- **--link**: #e879f9 (liens)
- **--ring**: #f0abfc (outline/focus)
- **--success**: #22c55e (succès)
- **--warning**: #facc15 (attention)
- **--error**: #ef4444 (erreur)

## Architecture

### 1. CSS Custom Properties

Les variables sont définies dans `src/index.css` :

```css
:root {
  /* Light theme variables */
  --bg: #ffffff;
  --primary: #2563eb;
  /* ... */
}

[data-theme='dark'] {
  /* Dark theme variables */
  --bg: #0b0b0f;
  --primary: #d946ef;
  /* ... */
}
```

### 2. Classes Tailwind Personnalisées

Configuration dans `tailwind.config.js` :

```javascript
colors: {
  'theme': {
    'bg': 'var(--bg)',
    'primary': 'var(--primary)',
    // ...
  }
}
```

### 3. Store Pinia

Gestion centralisée dans `stores/themeStore.js` :

- État du thème actuel
- Persistance localStorage
- Fallback sur `prefers-color-scheme`
- Écoute des changements système

### 4. Composant ThemeToggle

Interface utilisateur pour basculer entre thèmes avec :

- Bouton toggle animé
- Icônes soleil/lune
- État réactif

## Utilisation

### Dans les Composants Vue

```vue
<template>
  <div class="bg-theme-card text-theme-text border border-theme-border">
    <h1 class="text-theme-primary">Titre</h1>
    <p class="text-theme-text-muted">Description</p>
    <button class="bg-theme-primary text-theme-bg hover:bg-theme-primary-hover">Action</button>
  </div>
</template>
```

### Classes CSS Disponibles

- **Fonds**: `bg-theme-bg`, `bg-theme-card`, `bg-theme-primary`
- **Texte**: `text-theme-text`, `text-theme-primary`, `text-theme-text-muted`
- **Bordures**: `border-theme-border`, `border-theme-primary`
- **Focus**: `focus:ring-theme-ring`, `focus:border-theme-primary`

### Programmatiquement

```javascript
import { useThemeStore } from '@/stores/themeStore';

const themeStore = useThemeStore();

// Basculer le thème
themeStore.toggleTheme();

// Définir un thème spécifique
themeStore.setTheme('dark');

// Réinitialiser au thème système
themeStore.resetToSystemTheme();
```

## Migration des Composants Existants

### Remplacer les Couleurs Codées en Dur

```javascript
// ❌ Avant
class="bg-gray-900 text-amber-400 border-red-500"

// ✅ Après
class="bg-theme-card text-theme-primary border-theme-error"
```

### Remplacer les Gradients

```javascript
// ❌ Avant
class="bg-gradient-to-r from-amber-400 to-yellow-500"

// ✅ Après
class="bg-theme-primary"
```

### Remplacer les Opacités

```javascript
// ❌ Avant
class="bg-red-500/20"

// ✅ Après
class="bg-theme-error/20"
```

## Bonnes Pratiques

### 1. Utiliser les Classes Thème

- Toujours utiliser `bg-theme-*`, `text-theme-*`, etc.
- Éviter les couleurs hexadécimales directes
- Éviter les classes Tailwind de couleur non mappées

### 2. Gérer les États

- Utiliser `hover:`, `focus:`, `active:` avec les classes thème
- Préférer les opacités pour les variations d'état
- Utiliser `transition-colors` pour les animations

### 3. Accessibilité

- Maintenir un contraste suffisant entre thèmes
- Tester avec des outils d'accessibilité
- Respecter les préférences système

## Test et Démonstration

### Page de Démonstration

Accédez à `/theme-demo` pour tester :

- Tous les composants avec les deux thèmes
- Palette de couleurs complète
- Contrôles de thème
- Exemples d'utilisation

### Développement

```bash
# Démarrer le serveur de développement
npm run dev

# Naviguer vers /theme-demo
# Tester le switch de thème
# Vérifier la persistance localStorage
```

## Dépannage

### Thème Ne Change Pas

1. Vérifier que `data-theme` est bien défini sur `<html>`
2. Vérifier que les variables CSS sont chargées
3. Vérifier la console pour les erreurs JavaScript

### Couleurs Manquantes

1. Vérifier que Tailwind est configuré correctement
2. Vérifier que les classes `theme-*` sont disponibles
3. Vérifier que les variables CSS sont définies

### Performance

1. Les transitions sont limitées à 300ms
2. Utiliser `will-change` pour les animations complexes
3. Éviter les recalculs de layout lors des changements de thème

## Évolutions Futures

### Thèmes Personnalisés

- Support de thèmes utilisateur
- Éditeur de couleurs
- Import/export de palettes

### Composants Avancés

- Thèmes saisonniers
- Thèmes par section
- Thèmes dynamiques

### Intégration Backend

- Synchronisation des préférences utilisateur
- Thèmes par guilde
- Historique des changements
