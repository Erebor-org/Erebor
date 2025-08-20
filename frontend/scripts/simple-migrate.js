#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

console.log('🎨 Migration simple des thèmes Erebor\n');

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

// Mappings simples
const mappings = {
  'bg-gray-900': 'bg-theme-card',
  'bg-gray-800': 'bg-theme-bg-muted',
  'bg-black': 'bg-theme-bg',
  'text-amber-400': 'text-theme-primary',
  'text-yellow-500': 'text-theme-primary',
  'text-gray-400': 'text-theme-text-muted',
  'text-gray-300': 'text-theme-text',
  'text-white': 'text-theme-text',
  'border-gray-800': 'border-theme-border',
  'border-gray-600': 'border-theme-border',
  'bg-red-700': 'bg-theme-primary',
  'bg-yellow-50': 'bg-theme-card',
  'text-red-800': 'text-theme-text',
  'text-yellow-900': 'text-theme-text-muted',
  'bg-yellow-300': 'bg-theme-primary',
  'bg-yellow-100': 'bg-theme-bg-muted',
  'border-yellow-500': 'border-theme-border',
  'focus:ring-yellow-300': 'focus:ring-theme-ring',
  'text-yellow-600': 'text-theme-primary',
  'bg-green-500': 'bg-theme-success',
  'text-red-600': 'text-theme-error',
  'bg-red-800': 'bg-theme-primary-hover',
  'text-red-700': 'text-theme-error',
  'text-red-500': 'text-theme-error',
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

let migratedCount = 0;

// Traiter chaque fichier
for (const file of allFiles) {
  const relativePath = path.relative(rootDir, file);
  console.log(`🔍 Traitement de ${relativePath}...`);
  
  try {
    let content = fs.readFileSync(file, 'utf8');
    let hasChanges = false;
    
    // Appliquer les mappings
    for (const [oldColor, newColor] of Object.entries(mappings)) {
      const regex = new RegExp(`\\b${oldColor}\\b`, 'g');
      if (regex.test(content)) {
        content = content.replace(regex, newColor);
        hasChanges = true;
        console.log(`  ✓ ${oldColor} → ${newColor}`);
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

console.log(`\n🎉 Migration terminée !`);
console.log(`📊 ${migratedCount} fichiers migrés sur ${allFiles.length} fichiers traités`);
