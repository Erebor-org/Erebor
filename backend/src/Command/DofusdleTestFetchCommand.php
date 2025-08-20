<?php

namespace App\Command;

use App\Service\DofusdleDofusDbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erebor:dofusdle:test:fetch',
    description: 'Test de récupération des données depuis l\'API DofusDB pour analyser la structure.'
)]
class DofusdleTestFetchCommand extends Command
{
    private DofusdleDofusDbClient $client;

    public function __construct(DofusdleDofusDbClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Test de récupération des données depuis l\'API DofusDB...</info>');

        try {
            // Test 1: Récupérer quelques monstres pour analyser la structure
            $output->writeln('<comment>1. Structure des monstres :</comment>');
            $monsters = $this->client->findMonsters(['$limit' => 3]);
            
            foreach ($monsters as $i => $monster) {
                $output->writeln("\n<info>Monstre $i (ID: {$monster['id']}):</info>");
                $output->writeln("  Nom: {$monster['name']}");
                
                // Analyser les grades
                if (isset($monster['grades']) && is_array($monster['grades'])) {
                    $output->writeln("  Grades: " . count($monster['grades']) . " grades trouvés");
                    foreach ($monster['grades'] as $j => $grade) {
                        $output->writeln("    Grade $j: Level {$grade['level']}, PV {$grade['lifePoints']}, PA {$grade['actionPoints']}, PM {$grade['movementPoints']}");
                    }
                }
                
                // Analyser les résistances
                if (isset($monster['resistances'])) {
                    $output->writeln("  Résistances: " . json_encode($monster['resistances']));
                } else {
                    $output->writeln("  Résistances: NON TROUVÉES");
                }
                
                // Analyser les tags
                if (isset($monster['tags']) && is_array($monster['tags'])) {
                    $output->writeln("  Tags: " . implode(', ', $monster['tags']));
                } else {
                    $output->writeln("  Tags: NON TROUVÉS");
                }
                
                // Analyser la couleur
                if (isset($monster['color'])) {
                    $output->writeln("  Couleur: {$monster['color']}");
                } else {
                    $output->writeln("  Couleur: NON TROUVÉE");
                }
                
                // Analyser les subareas et areas
                if (isset($monster['subareas']) && !empty($monster['subareas'])) {
                    $output->writeln("  Subareas: " . count($monster['subareas']) . " trouvés");
                } else {
                    $output->writeln("  Subareas: NON TROUVÉS");
                }
                
                if (isset($monster['areas']) && !empty($monster['areas'])) {
                    $output->writeln("  Areas: " . count($monster['areas']) . " trouvés");
                } else {
                    $output->writeln("  Areas: NON TROUVÉS");
                }
                
                // Analyser isQuestMonster
                if (isset($monster['isQuestMonster'])) {
                    $output->writeln("  isQuestMonster: " . ($monster['isQuestMonster'] ? 'true' : 'false'));
                } else {
                    $output->writeln("  isQuestMonster: NON TROUVÉ");
                }
                
                // Analyser characRatios (potentiellement les résistances)
                if (isset($monster['characRatios'])) {
                    $output->writeln("  characRatios: " . json_encode($monster['characRatios']));
                } else {
                    $output->writeln("  characRatios: NON TROUVÉ");
                }
                
                // Analyser look (potentiellement les couleurs)
                if (isset($monster['look'])) {
                    $output->writeln("  look: " . json_encode($monster['look']));
                } else {
                    $output->writeln("  look: NON TROUVÉ");
                }
                
                // Analyser race (pour comprendre la structure)
                if (isset($monster['race'])) {
                    $output->writeln("  race: " . json_encode($monster['race']));
                } else {
                    $output->writeln("  race: NON TROUVÉ");
                }
                
                // Analyser gfxId (potentiellement lié à l'apparence)
                if (isset($monster['gfxId'])) {
                    $output->writeln("  gfxId: " . json_encode($monster['gfxId']));
                } else {
                    $output->writeln("  gfxId: NON TROUVÉ");
                }
                
                // Analyser look plus en détail
                if (isset($monster['look'])) {
                    $output->writeln("  look (raw): " . json_encode($monster['look']));
                    if (is_string($monster['look'])) {
                        $output->writeln("  look (string): " . $monster['look']);
                        // Essayer de parser le format
                        if (preg_match('/\{(\d+)\|\|\|(\d+)\}/', $monster['look'], $matches)) {
                            $output->writeln("    Parsed: spriteId={$matches[1]}, variantId={$matches[2]}");
                        }
                    }
                }
                
                // Analyser characRatios plus en détail
                if (isset($monster['characRatios'])) {
                    $output->writeln("  characRatios (raw): " . json_encode($monster['characRatios']));
                    if (is_array($monster['characRatios'])) {
                        $output->writeln("    Structure:");
                        foreach ($monster['characRatios'] as $i => $ratio) {
                            if (is_array($ratio) && count($ratio) >= 2) {
                                $output->writeln("      [$i]: ID={$ratio[0]}, Valeur={$ratio[1]}");
                            } else {
                                $output->writeln("      [$i]: " . json_encode($ratio));
                            }
                        }
                    }
                }
                
                // Analyser d'autres clés potentielles
                $potentialKeys = ['stats', 'characteristics', 'properties', 'appearance', 'visual', 'skin'];
                foreach ($potentialKeys as $key) {
                    if (isset($monster[$key])) {
                        $output->writeln("  $key: " . json_encode($monster[$key]));
                    }
                }
            }
            
            // Test 2: Analyser un monstre spécifique avec plus de détails
            $output->writeln("\n<comment>2. Analyse détaillée du premier monstre :</comment>");
            if (!empty($monsters)) {
                $firstMonster = $monsters[0];
                $output->writeln("Clés disponibles: " . implode(', ', array_keys($firstMonster)));
                
                // Chercher des clés qui pourraient contenir les résistances
                $possibleResistanceKeys = ['resistances', 'resistance', 'stats', 'characteristics', 'properties'];
                foreach ($possibleResistanceKeys as $key) {
                    if (isset($firstMonster[$key])) {
                        $output->writeln("  $key: " . json_encode($firstMonster[$key]));
                    }
                }
                
                // Chercher des clés qui pourraient contenir la couleur
                $possibleColorKeys = ['color', 'colour', 'appearance', 'skin', 'visual'];
                foreach ($possibleColorKeys as $key) {
                    if (isset($firstMonster[$key])) {
                        $output->writeln("  $key: " . json_encode($firstMonster[$key]));
                    }
                }
            }
            
            // Test 3: Analyser un monstre de niveau plus élevé (potentiellement plus d'informations)
            $output->writeln("\n<comment>3. Test d'un monstre de niveau élevé :</comment>");
            try {
                $highLevelMonsters = $this->client->findMonsters(['$limit' => 1, '$skip' => 1000]);
                if (!empty($highLevelMonsters)) {
                    $highLevelMonster = $highLevelMonsters[0];
                    $output->writeln("Monstre de niveau élevé (ID: {$highLevelMonster['id']}):");
                    $output->writeln("  Nom: {$highLevelMonster['name']}");
                    $output->writeln("  Clés: " . implode(', ', array_keys($highLevelMonster)));
                    
                    // Analyser les clés spécifiques
                    foreach (['characRatios', 'look', 'stats', 'characteristics', 'properties'] as $key) {
                        if (isset($highLevelMonster[$key])) {
                            $output->writeln("  $key: " . json_encode($highLevelMonster[$key]));
                        }
                    }
                }
            } catch (\Exception $e) {
                $output->writeln("  Erreur lors de la récupération: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $output->writeln("<error>Erreur: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Test terminé.</info>");
        return Command::SUCCESS;
    }
}
