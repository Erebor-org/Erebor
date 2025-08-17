#!/usr/bin/env node

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Déploiement en production Erebor...\n');

// Vérifier que nous sommes dans le bon répertoire
const currentDir = process.cwd();
const scriptsDir = path.join(currentDir, 'scripts');
const frontendDir = path.dirname(currentDir);

if (!fs.existsSync(scriptsDir)) {
  console.error('❌ Ce script doit être exécuté depuis le répertoire frontend/');
  process.exit(1);
}

console.log('✅ Répertoire de travail vérifié');

// Fonction pour exécuter une commande avec gestion d'erreur
function runCommand(command, description) {
  try {
    console.log(`\n🔄 ${description}...`);
    execSync(command, { stdio: 'inherit', cwd: frontendDir });
    console.log(`✅ ${description} terminé`);
    return true;
  } catch (error) {
    console.error(`❌ Erreur lors de ${description}:`, error.message);
    return false;
  }
}

// Fonction principale
async function deployProduction() {
  console.log('📋 Étapes du déploiement :\n');
  
  // 1. Installer les dépendances
  if (!runCommand('npm install', 'Installation des dépendances')) {
    process.exit(1);
  }
  
  // 2. Générer les favicons
  console.log('\n🎨 Génération des favicons...');
  try {
    process.chdir(scriptsDir);
    
    // Essayer d'abord la version optimisée
    try {
      if (fs.existsSync('package.json')) {
        execSync('npm install', { stdio: 'inherit' });
        execSync('npm run generate:optimized', { stdio: 'inherit' });
        console.log('✅ Favicons optimisés générés');
      } else {
        throw new Error('package.json non trouvé');
      }
    } catch (error) {
      console.log('⚠️ Version optimisée non disponible, utilisation de la version de base');
      execSync('node generate-favicons.js', { stdio: 'inherit' });
      console.log('✅ Favicons de base générés');
    }
    
    process.chdir(frontendDir);
  } catch (error) {
    console.error('❌ Erreur lors de la génération des favicons:', error.message);
    process.exit(1);
  }
  
  // 3. Vérifier que les favicons sont présents
  const requiredFiles = [
    'favicon.ico',
    'favicon-32x32.png',
    'favicon-16x16.png',
    'apple-touch-icon.png',
    'android-chrome-192x192.png',
    'android-chrome-512x512.png',
    'og-image.png',
    'site.webmanifest'
  ];
  
  console.log('\n🔍 Vérification des favicons...');
  const missingFiles = [];
  
  requiredFiles.forEach(file => {
    const filePath = path.join(frontendDir, 'public', file);
    if (!fs.existsSync(filePath)) {
      missingFiles.push(file);
    }
  });
  
  if (missingFiles.length > 0) {
    console.error('❌ Fichiers manquants:', missingFiles.join(', '));
    console.log('💡 Régénérez les favicons manuellement');
    process.exit(1);
  }
  
  console.log('✅ Tous les favicons sont présents');
  
  // 4. Build de production
  if (!runCommand('npm run build', 'Build de production')) {
    process.exit(1);
  }
  
  // 5. Vérifier le build
  const distDir = path.join(frontendDir, 'dist');
  if (!fs.existsSync(distDir)) {
    console.error('❌ Le dossier dist/ n\'a pas été créé');
    process.exit(1);
  }
  
  console.log('\n🎉 Déploiement terminé avec succès !');
  console.log('\n📋 Fichiers générés :');
  console.log(`   - Dossier de build: ${distDir}`);
  console.log(`   - Favicons: ${path.join(frontendDir, 'public')}`);
  
  console.log('\n💡 Prochaines étapes :');
  console.log('   1. Testez le build localement');
  console.log('   2. Vérifiez que les favicons s\'affichent');
  console.log('   3. Déployez sur votre serveur');
  console.log('   4. Testez en production');
  
  console.log('\n🚀 Erebor est prêt pour la production !');
}

// Exécuter le déploiement
deployProduction().catch(error => {
  console.error('❌ Erreur fatale lors du déploiement:', error);
  process.exit(1);
});
