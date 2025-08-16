#!/usr/bin/env node

/**
 * Script de migration des thèmes pour Erebor
 * Remplace automatiquement les couleurs codées en dur par les classes de thème
 */

const fs = require('fs');
const path = require('path');
const glob = require('glob');

// Mappings des couleurs vers les classes de thème
const colorMappings = {
  // Couleurs de fond
  'bg-gray-900': 'bg-theme-card',
  'bg-gray-800': 'bg-theme-bg-muted',
  'bg-gray-700': 'bg-theme-border',
  'bg-gray-600': 'bg-theme-border',
  'bg-black': 'bg-theme-bg',
  'bg-white': 'bg-theme-bg',
  'bg-red-700': 'bg-theme-primary',
  'bg-red-800': 'bg-theme-primary-hover',
  'bg-red-900': 'bg-theme-error',
  'bg-amber-500': 'bg-theme-primary',
  'bg-amber-600': 'bg-theme-primary-hover',
  'bg-yellow-500': 'bg-theme-primary',
  'bg-yellow-600': 'bg-theme-primary-hover',
  'bg-yellow-50': 'bg-theme-card',
  'bg-green-500': 'bg-theme-success',
  
  // Couleurs de texte
  'text-gray-200': 'text-theme-text',
  'text-gray-300': 'text-theme-text',
  'text-gray-400': 'text-theme-text-muted',
  'text-gray-500': 'text-theme-text-muted',
  'text-gray-600': 'text-theme-text-muted',
  'text-gray-800': 'text-theme-text',
  'text-gray-900': 'text-theme-text',
  'text-white': 'text-theme-text',
  'text-black': 'text-theme-text',
  'text-red-800': 'text-theme-text',
  'text-amber-400': 'text-theme-primary',
  'text-amber-500': 'text-theme-primary',
  'text-yellow-500': 'text-theme-primary',
  'text-yellow-600': 'text-theme-primary',
  'text-yellow-900': 'text-theme-text-muted',
  'text-red-400': 'text-theme-error',
  'text-red-600': 'text-theme-error',
  'text-green-500': 'text-theme-success',
  
  // Couleurs de bordure
  'border-gray-600': 'border-theme-border',
  'border-gray-700': 'border-theme-border',
  'border-gray-800': 'border-theme-border',
  'border-red-700': 'border-theme-border',
  'border-yellow-500': 'border-theme-border',
  'border-amber-500': 'border-theme-border',
  
  // Couleurs de focus/ring
  'focus:ring-yellow-300': 'focus:ring-theme-ring',
  'focus:ring-amber-500': 'focus:ring-theme-ring',
  'focus:ring-red-500': 'focus:ring-theme-ring',
  'focus:border-yellow-500': 'focus:border-theme-primary',
  'focus:border-amber-500': 'focus:border-theme-primary',
  'focus:border-red-500': 'focus:border-theme-primary',
  
  // Gradients
  'from-gray-900': 'from-theme-card',
  'to-gray-900': 'to-theme-card',
  'from-gray-800': 'from-theme-bg-muted',
  'to-gray-800': 'to-theme-bg-muted',
  'from-amber-400': 'from-theme-primary',
  'to-amber-400': 'to-theme-primary',
  'from-yellow-500': 'from-theme-primary',
  'to-yellow-500': 'to-theme-primary',
  'from-amber-500': 'from-theme-primary',
  'to-amber-500': 'from-theme-primary',
  'from-amber-600': 'from-theme-primary-hover',
  'to-amber-600': 'to-theme-primary-hover',
  'from-yellow-600': 'from-theme-primary-hover',
  'to-yellow-600': 'to-theme-primary-hover',
  'from-red-700': 'from-theme-primary',
  'to-red-700': 'to-theme-primary',
  'from-red-800': 'from-theme-primary-hover',
  'to-red-800': 'to-theme-primary-hover',
  
  // Hover states
  'hover:from-amber-600': 'hover:from-theme-primary-hover',
  'hover:to-yellow-700': 'hover:to-theme-primary-hover',
  'hover:from-red-800': 'hover:from-theme-primary-hover',
  'hover:bg-gray-700': 'hover:bg-theme-border',
  'hover:bg-gray-600': 'hover:bg-theme-border',
  'hover:bg-red-700': 'hover:bg-theme-primary-hover',
  'hover:bg-red-800': 'hover:bg-theme-primary-hover',
  'hover:bg-amber-600': 'hover:bg-theme-primary-hover',
  'hover:bg-yellow-700': 'hover:bg-theme-primary-hover',
  'hover:text-amber-400': 'hover:text-theme-primary',
  'hover:text-yellow-300': 'hover:text-theme-primary',
  'hover:text-red-300': 'hover:text-theme-error',
  'hover:text-red-400': 'hover:text-theme-error',
  'hover:text-white': 'hover:text-theme-text',
  'hover:border-amber-500': 'hover:border-theme-primary',
  'hover:border-yellow-500': 'hover:border-theme-primary',
  'hover:border-red-500': 'hover:border-theme-error',
  
  // Opacités
  'bg-red-900/20': 'bg-theme-error/20',
  'bg-amber-500/30': 'bg-theme-primary/30',
  'bg-yellow-500/30': 'bg-theme-primary/30',
  'border-amber-500/50': 'border-theme-primary/50',
  'border-yellow-500/50': 'border-theme-primary/50',
  'shadow-amber-500/25': 'shadow-theme-primary/25',
  'shadow-red-500/30': 'shadow-theme-error/30',
  
  // Couleurs spécifiques
  'text-amber-400': 'text-theme-primary',
  'text-yellow-300': 'text-theme-primary',
  'text-yellow-400': 'text-theme-primary',
  'text-yellow-600': 'text-theme-primary',
  'text-amber-600': 'text-theme-primary',
  'text-amber-500': 'text-theme-primary',
  'text-red-500': 'text-theme-error',
  'text-red-600': 'text-theme-error',
  'text-red-700': 'text-theme-error',
  'text-red-800': 'text-theme-error',
  'text-red-900': 'text-theme-error',
  'text-green-500': 'text-theme-success',
  'text-green-600': 'text-theme-success',
  'text-green-700': 'text-theme-success',
  'text-green-800': 'text-theme-success',
  'text-green-900': 'text-theme-success',
  'text-yellow-500': 'text-theme-warning',
  'text-yellow-600': 'text-theme-warning',
  'text-yellow-700': 'text-theme-warning',
  'text-yellow-800': 'text-theme-warning',
  'text-yellow-900': 'text-theme-warning',
  'text-amber-500': 'text-theme-warning',
  'text-amber-600': 'text-theme-warning',
  'text-amber-700': 'text-theme-warning',
  'text-amber-800': 'text-theme-warning',
  'text-amber-900': 'text-theme-warning',
  'text-orange-500': 'text-theme-warning',
  'text-orange-600': 'text-theme-warning',
  'text-orange-700': 'text-theme-warning',
  'text-orange-800': 'text-theme-warning',
  'text-orange-900': 'text-theme-warning',
  'text-pink-500': 'text-theme-primary',
  'text-pink-600': 'text-theme-primary',
  'text-pink-700': 'text-theme-primary',
  'text-pink-800': 'text-theme-primary',
  'text-pink-900': 'text-theme-primary',
  'text-purple-500': 'text-theme-primary',
  'text-purple-600': 'text-theme-primary',
  'text-purple-700': 'text-theme-primary',
  'text-purple-800': 'text-theme-primary',
  'text-purple-900': 'text-theme-primary',
  'text-indigo-500': 'text-theme-primary',
  'text-indigo-600': 'text-theme-primary',
  'text-indigo-700': 'text-theme-primary',
  'text-indigo-800': 'text-theme-primary',
  'text-indigo-900': 'text-theme-primary',
  'text-blue-500': 'text-theme-primary',
  'text-blue-600': 'text-theme-primary',
  'text-blue-700': 'text-theme-primary',
  'text-blue-800': 'text-theme-primary',
  'text-blue-900': 'text-theme-primary',
  'text-cyan-500': 'text-theme-primary',
  'text-cyan-600': 'text-theme-primary',
  'text-cyan-700': 'text-theme-primary',
  'text-cyan-800': 'text-theme-primary',
  'text-cyan-900': 'text-theme-primary',
  'text-teal-500': 'text-theme-primary',
  'text-teal-600': 'text-theme-primary',
  'text-teal-700': 'text-theme-primary',
  'text-teal-800': 'text-theme-primary',
  'text-teal-900': 'text-theme-primary',
  'text-emerald-500': 'text-theme-success',
  'text-emerald-600': 'text-theme-success',
  'text-emerald-700': 'text-theme-success',
  'text-emerald-800': 'text-theme-success',
  'text-emerald-900': 'text-theme-success',
  'text-lime-500': 'text-theme-success',
  'text-lime-600': 'text-theme-success',
  'text-lime-700': 'text-theme-success',
  'text-lime-800': 'text-theme-success',
  'text-lime-900': 'text-theme-success',
  'text-rose-500': 'text-theme-error',
  'text-rose-600': 'text-theme-error',
  'text-rose-700': 'text-theme-error',
  'text-rose-800': 'text-theme-error',
  'text-rose-900': 'text-theme-error',
  'text-slate-500': 'text-theme-text-muted',
  'text-slate-600': 'text-theme-text-muted',
  'text-slate-700': 'text-theme-text-muted',
  'text-slate-800': 'text-theme-text-muted',
  'text-slate-900': 'text-theme-text-muted',
  'text-zinc-500': 'text-theme-text-muted',
  'text-zinc-600': 'text-theme-text-muted',
  'text-zinc-700': 'text-theme-text-muted',
  'text-zinc-800': 'text-theme-text-muted',
  'text-zinc-900': 'text-theme-text-muted',
  'text-neutral-500': 'text-theme-text-muted',
  'text-neutral-600': 'text-theme-text-muted',
  'text-neutral-700': 'text-theme-text-muted',
  'text-neutral-800': 'text-theme-text-muted',
  'text-neutral-900': 'text-theme-text-muted',
  'text-stone-500': 'text-theme-text-muted',
  'text-stone-600': 'text-theme-text-muted',
  'text-stone-700': 'text-theme-text-muted',
  'text-stone-800': 'text-theme-text-muted',
  'text-stone-900': 'text-theme-text-muted',
};

// Fonction pour remplacer les couleurs dans un fichier
function migrateFile(filePath) {
  try {
    let content = fs.readFileSync(filePath, 'utf8');
    let hasChanges = false;
    
    // Appliquer tous les mappings
    for (const [oldColor, newColor] of Object.entries(colorMappings)) {
      const regex = new RegExp(`\\b${oldColor}\\b`, 'g');
      if (regex.test(content)) {
        content = content.replace(regex, newColor);
        hasChanges = true;
        console.log(`  ✓ ${oldColor} → ${newColor}`);
      }
    }
    
    // Sauvegarder si des changements ont été effectués
    if (hasChanges) {
      fs.writeFileSync(filePath, content, 'utf8');
      return true;
    }
    
    return false;
  } catch (error) {
    console.error(`  ✗ Erreur lors du traitement de ${filePath}:`, error.message);
    return false;
  }
}

// Fonction principale
function main() {
  console.log('🎨 Migration des thèmes Erebor\n');
  
  // Trouver tous les fichiers Vue depuis la racine du projet
  const projectRoot = path.resolve(__dirname, '..');
  console.log(`🔍 Racine du projet: ${projectRoot}`);
  
  const vueFiles = glob.sync(path.join(projectRoot, 'src/**/*.vue'));
  const jsFiles = glob.sync(path.join(projectRoot, 'src/**/*.js'));
  const allFiles = [...vueFiles, ...jsFiles];
  
  console.log(`📂 Fichiers Vue trouvés: ${vueFiles.length}`);
  console.log(`📂 Fichiers JS trouvés: ${jsFiles.length}`);
  
  console.log(`📁 ${allFiles.length} fichiers trouvés\n`);
  
  let migratedCount = 0;
  
  for (const file of allFiles) {
    console.log(`🔍 Traitement de ${file}...`);
    
    if (migrateFile(file)) {
      migratedCount++;
      console.log(`  ✅ Fichier migré avec succès\n`);
    } else {
      console.log(`  ⏭️  Aucun changement nécessaire\n`);
    }
  }
  
  console.log(`\n🎉 Migration terminée !`);
  console.log(`📊 ${migratedCount} fichiers migrés sur ${allFiles.length} fichiers traités`);
  
  if (migratedCount > 0) {
    console.log(`\n⚠️  IMPORTANT: Vérifiez les changements et testez votre application !`);
    console.log(`📖 Consultez THEME_SYSTEM.md pour plus d'informations`);
  }
}

// Exécuter le script
if (require.main === module) {
  main();
}

module.exports = { migrateFile, colorMappings };
