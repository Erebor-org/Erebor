#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

console.log('🎨 Fixing remaining hardcoded colors in Erebor themes\n');

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

// Comprehensive color mappings for remaining hardcoded colors
const colorMappings = {
  // Gray colors
  'bg-gray-500': 'bg-theme-text-muted',
  'bg-gray-600': 'bg-theme-border',
  'bg-gray-700': 'bg-theme-bg-muted',
  'text-gray-500': 'text-theme-text-muted',
  'text-gray-600': 'text-theme-text-muted',
  
  // Amber colors
  'bg-amber-500': 'bg-theme-primary',
  'bg-amber-600': 'bg-theme-primary',
  'bg-amber-700': 'bg-theme-primary-hover',
  'hover:bg-amber-500': 'hover:bg-theme-primary',
  'hover:bg-amber-600': 'hover:bg-theme-primary-hover',
  'hover:bg-amber-700': 'hover:bg-theme-primary-hover',
  'text-amber-500': 'text-theme-primary',
  'text-amber-600': 'text-theme-primary',
  'text-amber-700': 'text-theme-primary-hover',
  'hover:text-amber-500': 'hover:text-theme-primary',
  'hover:text-amber-600': 'hover:text-theme-primary-hover',
  'hover:text-amber-700': 'hover:text-theme-primary-hover',
  'border-amber-500': 'border-theme-primary',
  'hover:border-amber-500': 'hover:border-theme-primary',
  'focus:ring-amber-500': 'focus:ring-theme-primary',
  'focus:border-amber-500': 'focus:border-theme-primary',
  'ring-amber-500': 'ring-theme-primary',
  'bg-amber-500/10': 'bg-theme-primary/10',
  'bg-amber-500/20': 'bg-theme-primary/20',
  'border-amber-500/30': 'border-theme-primary/30',
  'border-amber-500/50': 'border-theme-primary/50',
  'hover:border-amber-500/30': 'hover:border-theme-primary/30',
  'focus:ring-offset-gray-900': 'focus:ring-offset-theme-bg',
  
  // Yellow colors
  'text-yellow-300': 'text-theme-primary-hover',
  'hover:text-yellow-300': 'hover:text-theme-primary-hover',
  
  // Red colors
  'bg-red-400': 'bg-theme-error',
  'bg-red-500': 'bg-theme-error',
  'bg-red-600': 'bg-theme-error',
  'bg-red-700': 'bg-theme-error',
  'bg-red-800': 'bg-theme-error',
  'bg-red-900': 'bg-theme-error',
  'hover:bg-red-500': 'hover:bg-theme-error',
  'hover:bg-red-600': 'hover:bg-theme-error',
  'hover:bg-red-700': 'hover:bg-theme-error',
  'hover:bg-red-800': 'hover:bg-theme-error',
  'hover:bg-red-900': 'hover:bg-theme-error',
  'text-red-400': 'text-theme-error',
  'text-red-500': 'text-theme-error',
  'text-red-600': 'text-theme-error',
  'text-red-700': 'text-theme-error',
  'text-red-800': 'text-theme-error',
  'text-red-900': 'text-theme-error',
  'hover:text-red-500': 'hover:text-theme-error',
  'hover:text-red-600': 'hover:text-theme-error',
  'hover:text-red-700': 'hover:text-theme-error',
  'hover:text-red-800': 'hover:text-theme-error',
  'hover:text-red-900': 'hover:text-red-900',
  'border-red-500': 'border-theme-error',
  'focus:ring-red-500': 'focus:ring-theme-error',
  'focus:border-red-500': 'focus:border-red-500',
  'ring-red-500': 'ring-theme-error',
  'ring-red-500/30': 'ring-theme-error/30',
  'focus:ring-red-500/30': 'focus:ring-theme-error/30',
  
  // Green colors
  'bg-green-500': 'bg-theme-success',
  'bg-green-600': 'bg-theme-success',
  'bg-green-700': 'bg-theme-success',
  'bg-green-800': 'bg-theme-success',
  'bg-green-900': 'bg-theme-success',
  'hover:bg-green-500': 'hover:bg-theme-success',
  'hover:bg-green-600': 'hover:bg-theme-success',
  'hover:bg-green-700': 'hover:bg-theme-success',
  'hover:bg-green-800': 'hover:bg-theme-success',
  'hover:bg-green-900': 'hover:bg-theme-success',
  'text-green-500': 'text-theme-success',
  'text-green-600': 'text-theme-success',
  'text-green-700': 'text-theme-success',
  'text-green-800': 'text-theme-success',
  'text-green-900': 'text-theme-success',
  'hover:text-green-500': 'hover:text-theme-success',
  'hover:text-green-600': 'hover:text-theme-success',
  'hover:text-green-700': 'hover:text-theme-success',
  'hover:text-green-800': 'hover:text-theme-success',
  'hover:text-green-900': 'hover:text-green-900',
  'border-green-500': 'border-theme-success',
  'focus:ring-green-500': 'focus:ring-theme-success',
  'focus:border-green-500': 'focus:border-green-500',
  'ring-green-500': 'ring-theme-success',
  'ring-green-500/30': 'ring-theme-success/30',
  'focus:ring-green-500/30': 'focus:ring-theme-success/30',
  
  // Blue colors
  'bg-blue-500': 'bg-theme-primary',
  'bg-blue-600': 'bg-theme-primary',
  'bg-blue-700': 'bg-theme-primary',
  'bg-blue-800': 'bg-theme-primary',
  'bg-blue-900': 'bg-theme-primary',
  'hover:bg-blue-500': 'hover:bg-theme-primary',
  'hover:bg-blue-600': 'hover:bg-theme-primary',
  'hover:bg-blue-700': 'hover:bg-theme-primary',
  'hover:bg-blue-800': 'hover:bg-theme-primary',
  'hover:bg-blue-900': 'hover:bg-theme-primary',
  'text-blue-500': 'text-theme-primary',
  'text-blue-600': 'text-theme-primary',
  'text-blue-700': 'text-theme-primary',
  'text-blue-800': 'text-theme-primary',
  'text-blue-900': 'text-theme-primary',
  'hover:text-blue-500': 'hover:text-theme-primary',
  'hover:text-blue-600': 'hover:text-theme-primary',
  'hover:text-blue-700': 'hover:text-theme-primary',
  'hover:text-blue-800': 'hover:text-blue-800',
  'hover:text-blue-900': 'hover:text-blue-900',
  'border-blue-500': 'border-theme-primary',
  'focus:ring-blue-500': 'focus:ring-theme-primary',
  'focus:border-blue-500': 'focus:border-blue-500',
  'ring-blue-500': 'ring-theme-primary',
  'ring-blue-500/30': 'ring-theme-primary/30',
  'focus:ring-blue-500/30': 'focus:ring-theme-primary/30',
  
  // Indigo colors
  'bg-indigo-500': 'bg-theme-primary',
  'bg-indigo-600': 'bg-theme-primary',
  'bg-indigo-700': 'bg-theme-primary',
  'bg-indigo-800': 'bg-theme-primary',
  'bg-indigo-900': 'bg-theme-primary',
  'hover:bg-indigo-500': 'hover:bg-theme-primary',
  'hover:bg-indigo-600': 'hover:bg-theme-primary',
  'hover:bg-indigo-700': 'hover:bg-theme-primary',
  'hover:bg-indigo-800': 'hover:bg-indigo-800',
  'hover:bg-indigo-900': 'hover:bg-indigo-900',
  'text-indigo-500': 'text-theme-primary',
  'text-indigo-600': 'text-theme-primary',
  'text-indigo-700': 'text-theme-primary',
  'text-indigo-800': 'text-theme-primary',
  'text-indigo-900': 'text-theme-primary',
  'hover:text-indigo-500': 'hover:text-theme-primary',
  'hover:text-indigo-600': 'hover:text-indigo-600',
  'hover:text-indigo-700': 'hover:text-indigo-700',
  'hover:text-indigo-800': 'hover:text-indigo-800',
  'hover:text-indigo-900': 'hover:text-indigo-900',
  'border-indigo-500': 'border-theme-primary',
  'focus:ring-indigo-500': 'focus:ring-theme-primary',
  'focus:border-indigo-500': 'focus:border-indigo-500',
  'ring-indigo-500': 'ring-theme-primary',
  'ring-indigo-500/30': 'ring-theme-primary/30',
  'focus:ring-indigo-500/30': 'focus:ring-indigo-500/30',
  
  // Purple colors
  'bg-purple-500': 'bg-theme-primary',
  'bg-purple-600': 'bg-theme-primary',
  'bg-purple-700': 'bg-theme-primary',
  'bg-purple-800': 'bg-theme-primary',
  'bg-purple-900': 'bg-theme-primary',
  'hover:bg-purple-500': 'hover:bg-theme-primary',
  'hover:bg-purple-600': 'hover:bg-purple-600',
  'hover:bg-purple-700': 'hover:bg-purple-700',
  'hover:bg-purple-800': 'hover:bg-purple-800',
  'hover:bg-purple-900': 'hover:bg-purple-900',
  'text-purple-500': 'text-theme-primary',
  'text-purple-600': 'text-theme-primary',
  'text-purple-700': 'text-theme-primary',
  'text-purple-800': 'text-theme-primary',
  'text-purple-900': 'text-theme-primary',
  'hover:text-purple-500': 'hover:text-purple-500',
  'hover:text-purple-600': 'hover:text-purple-600',
  'hover:text-purple-700': 'hover:text-purple-700',
  'hover:text-purple-800': 'hover:text-purple-800',
  'hover:text-purple-900': 'hover:text-purple-900',
  'border-purple-500': 'border-theme-primary',
  'focus:ring-purple-500': 'focus:ring-purple-500',
  'focus:border-purple-500': 'focus:border-purple-500',
  'ring-purple-500': 'ring-purple-500',
  'ring-purple-500/30': 'ring-purple-500/30',
  'focus:ring-purple-500/30': 'focus:ring-purple-500/30',
  
  // Pink colors
  'bg-pink-500': 'bg-theme-primary',
  'bg-pink-600': 'bg-theme-primary',
  'bg-pink-700': 'bg-theme-primary',
  'bg-pink-800': 'bg-theme-primary',
  'bg-pink-900': 'bg-theme-primary',
  'hover:bg-pink-500': 'hover:bg-pink-500',
  'hover:bg-pink-600': 'hover:bg-pink-600',
  'hover:bg-pink-700': 'hover:bg-pink-700',
  'hover:bg-pink-800': 'hover:bg-pink-800',
  'hover:bg-pink-900': 'hover:bg-pink-900',
  'text-pink-500': 'text-theme-primary',
  'text-pink-600': 'text-theme-primary',
  'text-pink-700': 'text-theme-primary',
  'text-pink-800': 'text-theme-primary',
  'text-pink-900': 'text-theme-primary',
  'hover:text-pink-500': 'hover:text-pink-500',
  'hover:text-pink-600': 'hover:text-pink-600',
  'hover:text-pink-700': 'hover:text-pink-700',
  'hover:text-pink-800': 'hover:text-pink-800',
  'hover:text-pink-900': 'hover:text-pink-900',
  'border-pink-500': 'border-pink-500',
  'focus:ring-pink-500': 'focus:ring-pink-500',
  'focus:border-pink-500': 'focus:border-pink-500',
  'ring-pink-500': 'ring-pink-500',
  'ring-pink-500/30': 'ring-pink-500/30',
  'focus:ring-pink-500/30': 'focus:ring-pink-500/30',
  
  // Cyan colors
  'bg-cyan-500': 'bg-theme-primary',
  'bg-cyan-600': 'bg-theme-primary',
  'bg-cyan-700': 'bg-theme-primary',
  'bg-cyan-800': 'bg-theme-primary',
  'bg-cyan-900': 'bg-theme-primary',
  'hover:bg-cyan-500': 'hover:bg-cyan-500',
  'hover:bg-cyan-600': 'hover:bg-cyan-600',
  'hover:bg-cyan-700': 'hover:bg-cyan-700',
  'hover:bg-cyan-800': 'hover:bg-cyan-800',
  'hover:bg-cyan-900': 'hover:bg-cyan-900',
  'text-cyan-500': 'text-theme-primary',
  'text-cyan-600': 'text-theme-primary',
  'text-cyan-700': 'text-theme-primary',
  'text-cyan-800': 'text-theme-primary',
  'text-cyan-900': 'text-theme-primary',
  'hover:text-cyan-500': 'hover:text-cyan-500',
  'hover:text-cyan-600': 'hover:text-cyan-600',
  'hover:text-cyan-700': 'hover:text-cyan-700',
  'hover:text-cyan-800': 'hover:text-cyan-800',
  'hover:text-cyan-900': 'hover:text-cyan-900',
  'border-cyan-500': 'border-cyan-500',
  'focus:ring-cyan-500': 'focus:ring-cyan-500',
  'focus:border-cyan-500': 'focus:border-cyan-500',
  'ring-cyan-500': 'ring-cyan-500',
  'ring-cyan-500/30': 'ring-cyan-500/30',
  'focus:ring-cyan-500/30': 'focus:ring-cyan-500/30',
  
  // Teal colors
  'bg-teal-500': 'bg-theme-primary',
  'bg-teal-600': 'bg-theme-primary',
  'bg-teal-700': 'bg-theme-primary',
  'bg-teal-800': 'bg-theme-primary',
  'bg-teal-900': 'bg-theme-primary',
  'hover:bg-teal-500': 'hover:bg-teal-500',
  'hover:bg-teal-600': 'hover:bg-teal-600',
  'hover:bg-teal-700': 'hover:bg-teal-700',
  'hover:bg-teal-800': 'hover:bg-teal-800',
  'hover:bg-teal-900': 'hover:bg-teal-900',
  'text-teal-500': 'text-theme-primary',
  'text-teal-600': 'text-theme-primary',
  'text-teal-700': 'text-theme-primary',
  'text-teal-800': 'text-theme-primary',
  'text-teal-900': 'text-theme-primary',
  'hover:text-teal-500': 'hover:text-teal-500',
  'hover:text-teal-600': 'hover:text-teal-600',
  'hover:text-teal-700': 'hover:text-teal-700',
  'hover:text-teal-800': 'hover:text-teal-800',
  'hover:text-teal-900': 'hover:text-teal-900',
  'border-teal-500': 'border-teal-500',
  'focus:ring-teal-500': 'focus:ring-teal-500',
  'focus:border-teal-500': 'focus:border-teal-500',
  'ring-teal-500': 'ring-teal-500',
  'ring-teal-500/30': 'ring-teal-500/30',
  'focus:ring-teal-500/30': 'focus:ring-teal-500/30',
  
  // Emerald colors
  'bg-emerald-500': 'bg-theme-success',
  'bg-emerald-600': 'bg-theme-success',
  'bg-emerald-700': 'bg-theme-success',
  'bg-emerald-800': 'bg-theme-success',
  'bg-emerald-900': 'bg-theme-success',
  'hover:bg-emerald-500': 'hover:bg-emerald-500',
  'hover:bg-emerald-600': 'hover:bg-emerald-600',
  'hover:bg-emerald-700': 'hover:bg-emerald-700',
  'hover:bg-emerald-800': 'hover:bg-emerald-800',
  'hover:bg-emerald-900': 'hover:bg-emerald-900',
  'text-emerald-500': 'text-theme-success',
  'text-emerald-600': 'text-theme-success',
  'text-emerald-700': 'text-theme-success',
  'text-emerald-800': 'text-theme-success',
  'text-emerald-900': 'text-theme-success',
  'hover:text-emerald-500': 'hover:text-emerald-500',
  'hover:text-emerald-600': 'hover:text-emerald-600',
  'hover:text-emerald-700': 'hover:text-emerald-700',
  'hover:text-emerald-800': 'hover:text-emerald-800',
  'hover:text-emerald-900': 'hover:text-emerald-900',
  'border-emerald-500': 'border-emerald-500',
  'focus:ring-emerald-500': 'focus:ring-emerald-500',
  'focus:border-emerald-500': 'focus:border-emerald-500',
  'ring-emerald-500': 'ring-emerald-500',
  'ring-emerald-500/30': 'ring-emerald-500/30',
  'focus:ring-emerald-500/30': 'focus:ring-emerald-500/30',
  
  // Lime colors
  'bg-lime-500': 'bg-theme-success',
  'bg-lime-600': 'bg-theme-success',
  'bg-lime-700': 'bg-theme-success',
  'bg-lime-800': 'bg-theme-success',
  'bg-lime-900': 'bg-theme-success',
  'hover:bg-lime-500': 'hover:bg-lime-500',
  'hover:bg-lime-600': 'hover:bg-lime-600',
  'hover:bg-lime-700': 'hover:bg-lime-700',
  'hover:bg-lime-800': 'hover:bg-lime-800',
  'hover:bg-lime-900': 'hover:bg-lime-900',
  'text-lime-500': 'text-theme-success',
  'text-lime-600': 'text-theme-success',
  'text-lime-700': 'text-theme-success',
  'text-lime-800': 'text-theme-success',
  'text-lime-900': 'text-theme-success',
  'hover:text-lime-500': 'hover:text-lime-500',
  'hover:text-lime-600': 'hover:text-lime-600',
  'hover:text-lime-700': 'hover:text-lime-700',
  'hover:text-lime-800': 'hover:text-lime-800',
  'hover:text-lime-900': 'hover:text-lime-900',
  'border-lime-500': 'border-lime-500',
  'focus:ring-lime-500': 'focus:ring-lime-500',
  'focus:border-lime-500': 'focus:border-lime-500',
  'ring-lime-500': 'ring-lime-500',
  'ring-lime-500/30': 'ring-lime-500/30',
  'focus:ring-lime-500/30': 'focus:ring-lime-500/30',
  
  // Rose colors
  'bg-rose-500': 'bg-theme-error',
  'bg-rose-600': 'bg-theme-error',
  'bg-rose-700': 'bg-theme-error',
  'bg-rose-800': 'bg-theme-error',
  'bg-rose-900': 'bg-theme-error',
  'hover:bg-rose-500': 'hover:bg-rose-500',
  'hover:bg-rose-600': 'hover:bg-rose-600',
  'hover:bg-rose-700': 'hover:bg-rose-700',
  'hover:bg-rose-800': 'hover:bg-rose-800',
  'hover:bg-rose-900': 'hover:bg-rose-900',
  'text-rose-500': 'text-theme-error',
  'text-rose-600': 'text-theme-error',
  'text-rose-700': 'text-theme-error',
  'text-rose-800': 'text-theme-error',
  'text-rose-900': 'text-theme-error',
  'hover:text-rose-500': 'hover:text-rose-500',
  'hover:text-rose-600': 'hover:text-rose-600',
  'hover:text-rose-700': 'hover:text-rose-700',
  'hover:text-rose-800': 'hover:text-rose-800',
  'hover:text-rose-900': 'hover:text-rose-900',
  'border-rose-500': 'border-rose-500',
  'focus:ring-rose-500': 'focus:ring-rose-500',
  'focus:border-rose-500': 'focus:border-rose-500',
  'ring-rose-500': 'ring-rose-500',
  'ring-rose-500/30': 'ring-rose-500/30',
  'focus:ring-rose-500/30': 'focus:ring-rose-500/30',
  
  // Slate colors
  'bg-slate-500': 'bg-theme-text-muted',
  'bg-slate-600': 'bg-theme-text-muted',
  'bg-slate-700': 'bg-theme-text-muted',
  'bg-slate-800': 'bg-theme-text-muted',
  'bg-slate-900': 'bg-theme-text-muted',
  'hover:bg-slate-500': 'hover:bg-slate-500',
  'hover:bg-slate-600': 'hover:bg-slate-600',
  'hover:bg-slate-700': 'hover:bg-slate-700',
  'hover:bg-slate-800': 'hover:bg-slate-800',
  'hover:bg-slate-900': 'hover:bg-slate-900',
  'text-slate-500': 'text-theme-text-muted',
  'text-slate-600': 'text-theme-text-muted',
  'text-slate-700': 'text-theme-text-muted',
  'text-slate-800': 'text-theme-text-muted',
  'text-slate-900': 'text-theme-text-muted',
  'hover:text-slate-500': 'hover:text-slate-500',
  'hover:text-slate-600': 'hover:text-slate-600',
  'hover:text-slate-700': 'hover:text-slate-700',
  'hover:text-slate-800': 'hover:text-slate-800',
  'hover:text-slate-900': 'hover:text-slate-900',
  'border-slate-500': 'border-slate-500',
  'focus:ring-slate-500': 'focus:ring-slate-500',
  'focus:border-slate-500': 'focus:border-slate-500',
  'ring-slate-500': 'ring-slate-500',
  'ring-slate-500/30': 'ring-slate-500/30',
  'focus:ring-slate-500/30': 'focus:ring-slate-500/30',
  
  // Zinc colors
  'bg-zinc-500': 'bg-theme-text-muted',
  'bg-zinc-600': 'bg-theme-text-muted',
  'bg-zinc-700': 'bg-theme-text-muted',
  'bg-zinc-800': 'bg-theme-text-muted',
  'bg-zinc-900': 'bg-theme-text-muted',
  'hover:bg-zinc-500': 'hover:bg-zinc-500',
  'hover:bg-zinc-600': 'hover:bg-zinc-600',
  'hover:bg-zinc-700': 'hover:bg-zinc-700',
  'hover:bg-zinc-800': 'hover:bg-zinc-800',
  'hover:bg-zinc-900': 'hover:bg-zinc-900',
  'text-zinc-500': 'text-theme-text-muted',
  'text-zinc-600': 'text-theme-text-muted',
  'text-zinc-700': 'text-theme-text-muted',
  'text-zinc-800': 'text-theme-text-muted',
  'text-zinc-900': 'text-theme-text-muted',
  'hover:text-zinc-500': 'hover:text-zinc-500',
  'hover:text-zinc-600': 'hover:text-zinc-600',
  'hover:text-zinc-700': 'hover:text-zinc-700',
  'hover:text-zinc-800': 'hover:text-zinc-800',
  'hover:text-zinc-900': 'hover:text-zinc-900',
  'border-zinc-500': 'border-zinc-500',
  'focus:ring-zinc-500': 'focus:ring-zinc-500',
  'focus:border-zinc-500': 'focus:border-zinc-500',
  'ring-zinc-500': 'ring-zinc-500',
  'ring-zinc-500/30': 'ring-zinc-500/30',
  'focus:ring-zinc-500/30': 'focus:ring-zinc-500/30',
  
  // Neutral colors
  'bg-neutral-500': 'bg-theme-text-muted',
  'bg-neutral-600': 'bg-theme-text-muted',
  'bg-neutral-700': 'bg-theme-text-muted',
  'bg-neutral-800': 'bg-theme-text-muted',
  'bg-neutral-900': 'bg-theme-text-muted',
  'hover:bg-neutral-500': 'hover:bg-neutral-500',
  'hover:bg-neutral-600': 'hover:bg-neutral-600',
  'hover:bg-neutral-700': 'hover:bg-neutral-700',
  'hover:bg-neutral-800': 'hover:bg-neutral-800',
  'hover:bg-neutral-900': 'hover:bg-neutral-900',
  'text-neutral-500': 'text-theme-text-muted',
  'text-neutral-600': 'text-theme-text-muted',
  'text-neutral-700': 'text-theme-text-muted',
  'text-neutral-800': 'text-theme-text-muted',
  'text-neutral-900': 'text-theme-text-muted',
  'hover:text-neutral-500': 'hover:text-neutral-500',
  'hover:text-neutral-600': 'hover:text-neutral-600',
  'hover:text-neutral-700': 'hover:text-neutral-700',
  'hover:text-neutral-800': 'hover:text-neutral-800',
  'hover:text-neutral-900': 'hover:text-neutral-900',
  'border-neutral-500': 'border-neutral-500',
  'focus:ring-neutral-500': 'focus:ring-neutral-500',
  'focus:border-neutral-500': 'focus:border-neutral-500',
  'ring-neutral-500': 'ring-neutral-500',
  'ring-neutral-500/30': 'ring-neutral-500/30',
  'focus:ring-neutral-500/30': 'focus:ring-neutral-500/30',
  
  // Stone colors
  'bg-stone-500': 'bg-theme-text-muted',
  'bg-stone-600': 'bg-theme-text-muted',
  'bg-stone-700': 'bg-theme-text-muted',
  'bg-stone-800': 'bg-theme-text-muted',
  'bg-stone-900': 'bg-theme-text-muted',
  'hover:bg-stone-500': 'hover:bg-stone-500',
  'hover:bg-stone-600': 'hover:bg-stone-600',
  'hover:bg-stone-700': 'hover:bg-stone-700',
  'hover:bg-stone-800': 'hover:bg-stone-800',
  'hover:bg-stone-900': 'hover:bg-stone-900',
  'text-stone-500': 'text-theme-text-muted',
  'text-stone-600': 'text-theme-text-muted',
  'text-stone-700': 'text-theme-text-muted',
  'text-stone-800': 'text-theme-text-muted',
  'text-stone-900': 'text-theme-text-muted',
  'hover:text-stone-500': 'hover:text-stone-500',
  'hover:text-stone-600': 'hover:text-stone-600',
  'hover:text-stone-700': 'hover:text-stone-700',
  'hover:text-stone-800': 'hover:text-stone-800',
  'hover:text-stone-900': 'hover:text-stone-900',
  'border-stone-500': 'border-stone-500',
  'focus:ring-stone-500': 'focus:ring-stone-500',
  'focus:border-stone-500': 'focus:border-stone-500',
  'ring-stone-500': 'ring-stone-500',
  'ring-stone-500/30': 'ring-stone-500/30',
  'focus:ring-stone-500/30': 'focus:ring-stone-500/30',
  
  // Orange colors
  'bg-orange-500': 'bg-theme-warning',
  'bg-orange-600': 'bg-theme-warning',
  'bg-orange-700': 'bg-theme-warning',
  'bg-orange-800': 'bg-theme-warning',
  'bg-orange-900': 'bg-theme-warning',
  'hover:bg-orange-500': 'hover:bg-orange-500',
  'hover:bg-orange-600': 'hover:bg-orange-600',
  'hover:bg-orange-700': 'hover:bg-orange-700',
  'hover:bg-orange-800': 'hover:bg-orange-800',
  'hover:bg-orange-900': 'hover:bg-orange-900',
  'text-orange-500': 'text-theme-warning',
  'text-orange-600': 'text-theme-warning',
  'text-orange-700': 'text-theme-warning',
  'text-orange-800': 'text-theme-warning',
  'text-orange-900': 'text-theme-warning',
  'hover:text-orange-500': 'hover:text-orange-500',
  'hover:text-orange-600': 'hover:text-orange-600',
  'hover:text-orange-700': 'hover:text-orange-700',
  'hover:text-orange-800': 'hover:text-orange-800',
  'hover:text-orange-900': 'hover:text-orange-900',
  'border-orange-500': 'border-orange-500',
  'focus:ring-orange-500': 'focus:ring-orange-500',
  'focus:border-orange-500': 'focus:border-orange-500',
  'ring-orange-500': 'ring-orange-500',
  'ring-orange-500/30': 'ring-orange-500/30',
  'focus:ring-orange-500/30': 'focus:ring-orange-500/30',
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
    for (const [oldColor, newColor] of Object.entries(colorMappings)) {
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
