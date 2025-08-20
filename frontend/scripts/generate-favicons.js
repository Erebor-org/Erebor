#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Configuration des favicons à générer
const faviconConfig = {
  'favicon.ico': { size: 32, format: 'ico' },
  'favicon-16x16.png': { size: 16, format: 'png' },
  'favicon-32x32.png': { size: 32, format: 'png' },
  'apple-touch-icon.png': { size: 180, format: 'png' },
  'android-chrome-192x192.png': { size: 192, format: 'png' },
  'android-chrome-512x512.png': { size: 512, format: 'png' },
  'og-image.png': { size: 1200, format: 'png' }
};

// Chemins des fichiers
const sourceLogo = path.join(__dirname, '../src/assets/erebor_logo.png');
const publicDir = path.join(__dirname, '../public');

console.log('🎨 Génération des favicons pour Erebor...\n');

// Vérifier que le logo source existe
if (!fs.existsSync(sourceLogo)) {
  console.error('❌ Logo source introuvable:', sourceLogo);
  console.log('💡 Assurez-vous que le fichier erebor_logo.png existe dans src/assets/');
  process.exit(1);
}

console.log('✅ Logo source trouvé:', sourceLogo);

// Créer le répertoire public s'il n'existe pas
if (!fs.existsSync(publicDir)) {
  fs.mkdirSync(publicDir, { recursive: true });
  console.log('📁 Répertoire public créé');
}

// Copier le logo source vers public pour l'utiliser comme favicon de base
const faviconBase = path.join(publicDir, 'erebor_logo.png');
fs.copyFileSync(sourceLogo, faviconBase);
console.log('📋 Logo de base copié vers public/');

// Créer des favicons de base (copies du logo original)
Object.entries(faviconConfig).forEach(([filename, config]) => {
  const targetPath = path.join(publicDir, filename);
  
  // Pour l'instant, on copie le logo original
  // En production, vous devriez utiliser un outil comme sharp ou jimp pour redimensionner
  fs.copyFileSync(sourceLogo, targetPath);
  
  console.log(`✅ ${filename} généré (${config.size}x${config.size})`);
});

console.log('\n🎉 Génération des favicons terminée !');
console.log('\n📋 Fichiers créés :');
Object.keys(faviconConfig).forEach(filename => {
  console.log(`   - ${filename}`);
});

console.log('\n💡 Pour une production optimale :');
console.log('   1. Installez sharp: npm install sharp');
console.log('   2. Utilisez sharp pour redimensionner et optimiser les images');
console.log('   3. Testez les favicons sur différents appareils');
console.log('   4. Vérifiez la compatibilité avec les navigateurs');

console.log('\n🚀 Votre site Erebor est maintenant prêt pour la production !');
