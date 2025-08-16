#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

console.log('🎨 Final color cleanup - Eliminating ALL remaining hardcoded colors\n');

// Dossier racine (depuis scripts/)
const rootDir = path.join(__dirname, '..');
const srcDir = path.join(rootDir, 'src');

console.log(`📁 Racine: ${rootDir}`);
console.log(`📁 Source: ${srcDir}`);

// Vérifier que src existe
if (!fs.existsSync(srcDir)) {
  console.error('❌ Dossier src non trouvé!');
  process.exit(1);
}

// Chercher tous les fichiers .vue et .js
function findFiles(dir) {
  const files = [];
  const items = fs.readdirSync(dir);
  
  for (const item of items) {
    const fullPath = path.join(dir, item);
    const stat = fs.statSync(fullPath);
    
    if (stat.isDirectory()) {
      files.push(...findFiles(fullPath));
    } else if (item.endsWith('.vue') || item.endsWith('.js')) {
      files.push(fullPath);
    }
  }
  
  return files;
}

const allFiles = findFiles(srcDir);
console.log(`📂 ${allFiles.length} fichiers trouvés\n`);

// FINAL color mappings - catch ALL remaining hardcoded colors
const finalColorMappings = {
  // Amber colors (all variations)
  'amber-50': 'theme-card',
  'amber-100': 'theme-bg-muted',
  'amber-200': 'theme-primary',
  'amber-300': 'theme-primary',
  'amber-400': 'theme-primary',
  'amber-500': 'theme-primary',
  'amber-600': 'theme-primary',
  'amber-700': 'theme-primary-hover',
  'amber-800': 'theme-primary-hover',
  'amber-900': 'theme-primary-hover',
  
  // Yellow colors (all variations)
  'yellow-50': 'theme-card',
  'yellow-100': 'theme-bg-muted',
  'yellow-200': 'theme-primary',
  'yellow-300': 'theme-primary-hover',
  'yellow-400': 'theme-primary',
  'yellow-500': 'theme-primary',
  'yellow-600': 'theme-primary-hover',
  'yellow-700': 'theme-primary-hover',
  'yellow-800': 'theme-primary-hover',
  'yellow-900': 'theme-text-muted',
  
  // Red colors (all variations)
  'red-50': 'theme-card',
  'red-100': 'theme-bg-muted',
  'red-200': 'theme-error',
  'red-300': 'theme-error',
  'red-400': 'theme-error',
  'red-500': 'theme-error',
  'red-600': 'theme-error',
  'red-700': 'theme-error',
  'red-800': 'theme-error',
  'red-900': 'theme-error',
  
  // Green colors (all variations)
  'green-50': 'theme-card',
  'green-100': 'theme-bg-muted',
  'green-200': 'theme-success',
  'green-300': 'theme-success',
  'green-400': 'theme-success',
  'green-500': 'theme-success',
  'green-600': 'theme-success',
  'green-700': 'theme-success',
  'green-800': 'theme-success',
  'green-900': 'theme-success',
  
  // Blue colors (all variations)
  'blue-50': 'theme-card',
  'blue-100': 'theme-bg-muted',
  'blue-200': 'theme-primary',
  'blue-300': 'theme-primary',
  'blue-400': 'theme-primary',
  'blue-500': 'theme-primary',
  'blue-600': 'theme-primary',
  'blue-700': 'theme-primary',
  'blue-800': 'theme-primary',
  'blue-900': 'theme-primary',
  
  // Indigo colors (all variations)
  'indigo-50': 'theme-card',
  'indigo-100': 'theme-bg-muted',
  'indigo-200': 'theme-primary',
  'indigo-300': 'theme-primary',
  'indigo-400': 'theme-primary',
  'indigo-500': 'theme-primary',
  'indigo-600': 'theme-primary',
  'indigo-700': 'theme-primary',
  'indigo-800': 'theme-primary',
  'indigo-900': 'theme-primary',
  
  // Purple colors (all variations)
  'purple-50': 'theme-card',
  'purple-100': 'theme-bg-muted',
  'purple-200': 'theme-primary',
  'purple-300': 'theme-primary',
  'purple-400': 'theme-primary',
  'purple-500': 'theme-primary',
  'purple-600': 'theme-primary',
  'purple-700': 'theme-primary',
  'purple-800': 'theme-primary',
  'purple-900': 'theme-primary',
  
  // Pink colors (all variations)
  'pink-50': 'theme-card',
  'pink-100': 'theme-bg-muted',
  'pink-200': 'theme-primary',
  'pink-300': 'theme-primary',
  'pink-400': 'theme-primary',
  'pink-500': 'theme-primary',
  'pink-600': 'theme-primary',
  'pink-700': 'theme-primary',
  'pink-800': 'theme-primary',
  'pink-900': 'theme-primary',
  
  // Cyan colors (all variations)
  'cyan-50': 'theme-card',
  'cyan-100': 'theme-bg-muted',
  'cyan-200': 'theme-primary',
  'cyan-300': 'theme-primary',
  'cyan-400': 'theme-primary',
  'cyan-500': 'theme-primary',
  'cyan-600': 'theme-primary',
  'cyan-700': 'theme-primary',
  'cyan-800': 'theme-primary',
  'cyan-900': 'theme-primary',
  
  // Teal colors (all variations)
  'teal-50': 'theme-card',
  'teal-100': 'theme-bg-muted',
  'teal-200': 'theme-primary',
  'teal-300': 'theme-primary',
  'teal-400': 'theme-primary',
  'teal-500': 'theme-primary',
  'teal-600': 'theme-primary',
  'teal-700': 'theme-primary',
  'teal-800': 'theme-primary',
  'teal-900': 'theme-primary',
  
  // Emerald colors (all variations)
  'emerald-50': 'theme-card',
  'emerald-100': 'theme-bg-muted',
  'emerald-200': 'theme-success',
  'emerald-300': 'theme-success',
  'emerald-400': 'theme-success',
  'emerald-500': 'theme-success',
  'emerald-600': 'theme-success',
  'emerald-700': 'theme-success',
  'emerald-800': 'theme-success',
  'emerald-900': 'theme-success',
  
  // Lime colors (all variations)
  'lime-50': 'theme-card',
  'lime-100': 'theme-bg-muted',
  'lime-200': 'theme-success',
  'lime-300': 'theme-success',
  'lime-400': 'theme-success',
  'lime-500': 'theme-success',
  'lime-600': 'theme-success',
  'lime-700': 'theme-success',
  'lime-800': 'theme-success',
  'lime-900': 'theme-success',
  
  // Rose colors (all variations)
  'rose-50': 'theme-card',
  'rose-100': 'theme-bg-muted',
  'rose-200': 'theme-error',
  'rose-300': 'theme-error',
  'rose-400': 'theme-error',
  'rose-500': 'theme-error',
  'rose-600': 'theme-error',
  'rose-700': 'theme-error',
  'rose-800': 'theme-error',
  'rose-900': 'theme-error',
  
  // Orange colors (all variations)
  'orange-50': 'theme-card',
  'orange-100': 'theme-bg-muted',
  'orange-200': 'theme-warning',
  'orange-300': 'theme-warning',
  'orange-400': 'theme-warning',
  'orange-500': 'theme-warning',
  'orange-600': 'theme-warning',
  'orange-700': 'theme-warning',
  'orange-800': 'theme-warning',
  'orange-900': 'theme-warning',
  
  // Gray colors (all variations)
  'gray-50': 'theme-card',
  'gray-100': 'theme-bg-muted',
  'gray-200': 'theme-text',
  'gray-300': 'theme-text',
  'gray-400': 'theme-text-muted',
  'gray-500': 'theme-text-muted',
  'gray-600': 'theme-text-muted',
  'gray-700': 'theme-bg-muted',
  'gray-800': 'theme-bg-muted',
  'gray-900': 'theme-card',
  
  // Slate colors (all variations)
  'slate-50': 'theme-card',
  'slate-100': 'theme-bg-muted',
  'slate-200': 'theme-text',
  'slate-300': 'theme-text',
  'slate-400': 'theme-text-muted',
  'slate-500': 'theme-text-muted',
  'slate-600': 'theme-text-muted',
  'slate-700': 'theme-text-muted',
  'slate-800': 'theme-text-muted',
  'slate-900': 'theme-text-muted',
  
  // Zinc colors (all variations)
  'zinc-50': 'theme-card',
  'zinc-100': 'theme-bg-muted',
  'zinc-200': 'theme-text',
  'zinc-300': 'theme-text',
  'zinc-400': 'theme-text-muted',
  'zinc-500': 'theme-text-muted',
  'zinc-600': 'theme-text-muted',
  'zinc-700': 'theme-text-muted',
  'zinc-800': 'theme-text-muted',
  'zinc-900': 'theme-text-muted',
  
  // Neutral colors (all variations)
  'neutral-50': 'theme-card',
  'neutral-100': 'theme-bg-muted',
  'neutral-200': 'theme-text',
  'neutral-300': 'theme-text',
  'neutral-400': 'theme-text-muted',
  'neutral-500': 'theme-text-muted',
  'neutral-600': 'theme-text-muted',
  'neutral-700': 'theme-text-muted',
  'neutral-800': 'theme-text-muted',
  'neutral-900': 'theme-text-muted',
  
  // Stone colors (all variations)
  'stone-50': 'theme-card',
  'stone-100': 'theme-bg-muted',
  'stone-200': 'theme-text',
  'stone-300': 'theme-text',
  'stone-400': 'theme-text-muted',
  'stone-500': 'theme-text-muted',
  'stone-600': 'theme-text-muted',
  'stone-700': 'theme-text-muted',
  'stone-800': 'theme-text-muted',
  'stone-900': 'theme-text-muted',
  
  // Black and white
  'black': 'theme-bg',
  'white': 'theme-text',
};

let migratedCount = 0;

// Traiter chaque fichier
for (const file of allFiles) {
  const relativePath = path.relative(rootDir, file);
  console.log(`🔍 Traitement de ${relativePath}...`);
  
  try {
    let content = fs.readFileSync(file, 'utf8');
    let hasChanges = false;
    
    // Appliquer les mappings pour TOUTES les couleurs
    for (const [oldColor, newColor] of Object.entries(finalColorMappings)) {
      // Patterns pour capturer toutes les variations
      const patterns = [
        `bg-${oldColor}`,
        `text-${oldColor}`,
        `border-${oldColor}`,
        `ring-${oldColor}`,
        `from-${oldColor}`,
        `to-${oldColor}`,
        `hover:bg-${oldColor}`,
        `hover:text-${oldColor}`,
        `hover:border-${oldColor}`,
        `focus:ring-${oldColor}`,
        `focus:border-${oldColor}`,
        `focus:ring-offset-${oldColor}`,
        `shadow-${oldColor}`,
        `bg-${oldColor}/`,
        `text-${oldColor}/`,
        `border-${oldColor}/`,
        `ring-${oldColor}/`,
        `shadow-${oldColor}/`,
      ];
      
      for (const pattern of patterns) {
        const regex = new RegExp(`\\b${pattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'g');
        if (regex.test(content)) {
          // Remplacer le pattern avec la bonne classe de thème
          const replacement = pattern.replace(oldColor, newColor);
          content = content.replace(regex, replacement);
          hasChanges = true;
          console.log(`  ✓ ${pattern} → ${replacement}`);
        }
      }
    }
    
    if (hasChanges) {
      fs.writeFileSync(file, content, 'utf8');
      migratedCount++;
      console.log(`  ✅ Fichier migré\n`);
    } else {
      console.log(`  ⏭️  Aucun changement\n`);
    }
    
  } catch (error) {
    console.error(`  ❌ Erreur: ${error.message}\n`);
  }
}

console.log(`\n🎉 Nettoyage final terminé !`);
console.log(`📊 ${migratedCount} fichiers migrés sur ${allFiles.length} fichiers traités`);
console.log(`\n✨ Toutes les couleurs codées en dur ont été éliminées !`);
