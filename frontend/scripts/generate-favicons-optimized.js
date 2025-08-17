#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Vérifier si Sharp est installé
let sharp;
try {
  sharp = require('sharp');
} catch (error) {
  console.error('❌ Sharp n\'est pas installé. Installez-le avec : npm install sharp');
  console.log('💡 Utilisez le script de base : node generate-favicons.js');
  process.exit(1);
}

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

console.log('🎨 Génération des favicons optimisés pour Erebor...\n');

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

// Fonction pour générer un favicon optimisé
async function generateFavicon(filename, config) {
  try {
    const targetPath = path.join(publicDir, filename);
    
    if (config.format === 'ico') {
      // Pour les .ico, on génère un PNG 32x32
      await sharp(sourceLogo)
        .resize(config.size, config.size, {
          fit: 'contain',
          background: { r: 255, g: 255, b: 255, alpha: 0 }
        })
        .png()
        .toFile(targetPath);
    } else {
      // Pour les PNG, on génère avec la taille spécifiée
      await sharp(sourceLogo)
        .resize(config.size, config.size, {
          fit: 'contain',
          background: { r: 255, g: 255, b: 255, alpha: 0 }
        })
        .png({ quality: 90, compressionLevel: 9 })
        .toFile(targetPath);
    }
    
    console.log(`✅ ${filename} généré (${config.size}x${config.size})`);
    return true;
  } catch (error) {
    console.error(`❌ Erreur lors de la génération de ${filename}:`, error.message);
    return false;
  }
}

// Fonction principale
async function main() {
  console.log('🚀 Début de la génération des favicons...\n');
  
  const results = [];
  
  // Générer tous les favicons
  for (const [filename, config] of Object.entries(faviconConfig)) {
    const success = await generateFavicon(filename, config);
    results.push({ filename, success });
  }
  
  // Résumé
  console.log('\n📊 Résumé de la génération :');
  const successful = results.filter(r => r.success).length;
  const total = results.length;
  
  console.log(`✅ Succès: ${successful}/${total}`);
  
  if (successful === total) {
    console.log('\n🎉 Tous les favicons ont été générés avec succès !');
    console.log('\n📋 Fichiers créés :');
    results.forEach(({ filename }) => {
      console.log(`   - ${filename}`);
    });
    
    console.log('\n🚀 Votre site Erebor est maintenant prêt pour la production !');
    console.log('\n💡 Prochaines étapes :');
    console.log('   1. Testez les favicons sur différents navigateurs');
    console.log('   2. Vérifiez l\'affichage sur mobile et desktop');
    console.log('   3. Testez le partage sur les réseaux sociaux');
    console.log('   4. Déployez en production !');
  } else {
    console.log('\n⚠️ Certains favicons n\'ont pas pu être générés.');
    console.log('💡 Vérifiez les erreurs ci-dessus et réessayez.');
    process.exit(1);
  }
}

// Exécuter le script
main().catch(error => {
  console.error('❌ Erreur fatale:', error);
  process.exit(1);
});
