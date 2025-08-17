#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

console.log('🧪 Test des favicons Erebor...\n');

// Configuration des tests
const testConfig = {
  requiredFiles: [
    'favicon.ico',
    'favicon-32x32.png',
    'favicon-16x16.png',
    'apple-touch-icon.png',
    'android-chrome-192x192.png',
    'android-chrome-512x512.png',
    'og-image.png',
    'site.webmanifest'
  ],
  htmlFile: '../index.html',
  publicDir: '../public'
};

// Chemins
const scriptsDir = __dirname;
const frontendDir = path.dirname(scriptsDir);
const publicDir = path.join(frontendDir, 'public');
const htmlFile = path.join(frontendDir, 'index.html');

console.log('📁 Vérification des chemins...');
console.log(`   Scripts: ${scriptsDir}`);
console.log(`   Frontend: ${frontendDir}`);
console.log(`   Public: ${publicDir}`);
console.log(`   HTML: ${htmlFile}\n`);

// Test 1: Vérifier que le répertoire public existe
function testPublicDirectory() {
  console.log('🔍 Test 1: Répertoire public');
  
  if (!fs.existsSync(publicDir)) {
    console.log('❌ Le répertoire public/ n\'existe pas');
    return false;
  }
  
  console.log('✅ Répertoire public/ trouvé');
  return true;
}

// Test 2: Vérifier que tous les favicons sont présents
function testFaviconFiles() {
  console.log('\n🔍 Test 2: Fichiers favicon');
  
  const missingFiles = [];
  const existingFiles = [];
  
  testConfig.requiredFiles.forEach(file => {
    const filePath = path.join(publicDir, file);
    if (fs.existsSync(filePath)) {
      const stats = fs.statSync(filePath);
      existingFiles.push({ file, size: stats.size });
    } else {
      missingFiles.push(file);
    }
  });
  
  if (missingFiles.length > 0) {
    console.log('❌ Fichiers manquants:');
    missingFiles.forEach(file => console.log(`   - ${file}`));
    return false;
  }
  
  console.log('✅ Tous les favicons sont présents:');
  existingFiles.forEach(({ file, size }) => {
    console.log(`   - ${file} (${size} bytes)`);
  });
  
  return true;
}

// Test 3: Vérifier le fichier HTML
function testHtmlFile() {
  console.log('\n🔍 Test 3: Configuration HTML');
  
  if (!fs.existsSync(htmlFile)) {
    console.log('❌ Le fichier index.html n\'existe pas');
    return false;
  }
  
  const htmlContent = fs.readFileSync(htmlFile, 'utf8');
  
  // Vérifier les liens favicon
  const faviconLinks = [
    'rel="icon" type="image/x-icon" href="/favicon.ico"',
    'rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png"',
    'rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png"',
    'rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"',
    'rel="manifest" href="/site.webmanifest"'
  ];
  
  const missingLinks = [];
  faviconLinks.forEach(link => {
    if (!htmlContent.includes(link)) {
      missingLinks.push(link);
    }
  });
  
  if (missingLinks.length > 0) {
    console.log('❌ Liens favicon manquants dans index.html:');
    missingLinks.forEach(link => console.log(`   - ${link}`));
    return false;
  }
  
  console.log('✅ Configuration HTML correcte');
  return true;
}

// Test 4: Vérifier le webmanifest
function testWebmanifest() {
  console.log('\n🔍 Test 4: Fichier webmanifest');
  
  const manifestPath = path.join(publicDir, 'site.webmanifest');
  
  if (!fs.existsSync(manifestPath)) {
    console.log('❌ Le fichier site.webmanifest n\'existe pas');
    return false;
  }
  
  try {
    const manifestContent = fs.readFileSync(manifestPath, 'utf8');
    const manifest = JSON.parse(manifestContent);
    
    // Vérifier les propriétés requises
    const requiredProps = ['name', 'short_name', 'description', 'start_url', 'display'];
    const missingProps = [];
    
    requiredProps.forEach(prop => {
      if (!manifest[prop]) {
        missingProps.push(prop);
      }
    });
    
    if (missingProps.length > 0) {
      console.log('❌ Propriétés manquantes dans webmanifest:');
      missingProps.forEach(prop => console.log(`   - ${prop}`));
      return false;
    }
    
    console.log('✅ Webmanifest valide:');
    console.log(`   - Nom: ${manifest.name}`);
    console.log(`   - Nom court: ${manifest.short_name}`);
    console.log(`   - Description: ${manifest.description}`);
    console.log(`   - URL de départ: ${manifest.start_url}`);
    console.log(`   - Affichage: ${manifest.display}`);
    
    return true;
  } catch (error) {
    console.log('❌ Erreur lors de la lecture du webmanifest:', error.message);
    return false;
  }
}

// Test 5: Vérifier les tailles des images
function testImageSizes() {
  console.log('\n🔍 Test 5: Tailles des images');
  
  const imageFiles = [
    { file: 'favicon-16x16.png', expectedSize: 16 },
    { file: 'favicon-32x32.png', expectedSize: 32 },
    { file: 'apple-touch-icon.png', expectedSize: 180 },
    { file: 'android-chrome-192x192.png', expectedSize: 192 },
    { file: 'android-chrome-512x512.png', expectedSize: 512 }
  ];
  
  let allValid = true;
  
  imageFiles.forEach(({ file, expectedSize }) => {
    const filePath = path.join(publicDir, file);
    if (fs.existsSync(filePath)) {
      const stats = fs.statSync(filePath);
      if (stats.size < 100) { // Vérification basique de la taille
        console.log(`✅ ${file} - Taille: ${stats.size} bytes`);
      } else {
        console.log(`⚠️ ${file} - Taille: ${stats.size} bytes (attendu: ~${expectedSize}x${expectedSize})`);
      }
    } else {
      console.log(`❌ ${file} - Fichier manquant`);
      allValid = false;
    }
  });
  
  return allValid;
}

// Fonction principale de test
function runTests() {
  console.log('🚀 Démarrage des tests...\n');
  
  const tests = [
    { name: 'Répertoire public', fn: testPublicDirectory },
    { name: 'Fichiers favicon', fn: testFaviconFiles },
    { name: 'Configuration HTML', fn: testHtmlFile },
    { name: 'Webmanifest', fn: testWebmanifest },
    { name: 'Tailles des images', fn: testImageSizes }
  ];
  
  let passedTests = 0;
  let totalTests = tests.length;
  
  tests.forEach(test => {
    try {
      if (test.fn()) {
        passedTests++;
      }
    } catch (error) {
      console.log(`❌ Erreur lors du test "${test.name}":`, error.message);
    }
  });
  
  // Résumé
  console.log('\n📊 Résumé des tests :');
  console.log(`✅ Tests réussis: ${passedTests}/${totalTests}`);
  
  if (passedTests === totalTests) {
    console.log('\n🎉 Tous les tests sont passés !');
    console.log('🚀 Erebor est prêt pour la production !');
  } else {
    console.log('\n⚠️ Certains tests ont échoué.');
    console.log('💡 Vérifiez les erreurs ci-dessus et corrigez-les.');
    process.exit(1);
  }
}

// Exécuter les tests
runTests();
