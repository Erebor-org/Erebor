<?php

namespace App\Command;

use App\Service\DofusdleDofusDbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erebor:dofusdle:test:zones',
    description: 'Test spécifique pour vérifier la récupération des zones depuis l\'API DofusDB.'
)]
class DofusdleTestZonesCommand extends Command
{
    private DofusdleDofusDbClient $client;

    public function __construct(DofusdleDofusDbClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Test de récupération des zones depuis l\'API DofusDB...</info>');

        try {
            // Test 1: Récupérer un monstre et ses subareas
            $output->writeln('<comment>1. Récupération d\'un monstre avec ses subareas :</comment>');
            $monsters = $this->client->findMonsters(['$limit' => 10]);
            
            foreach ($monsters as $monster) {
                if (isset($monster['subareas']) && !empty($monster['subareas'])) {
                    $output->writeln("Monstre trouvé: {$monster['name']} (ID: {$monster['id']})");
                    $output->writeln("  Subareas: " . json_encode($monster['subareas']));
                    
                    // Test de résolution de zone pour le premier subarea
                    $subareaId = $monster['subareas'][0];
                    $output->writeln("  Test de résolution pour subareaId: $subareaId");
                    
                    // Test 2: Récupérer la subarea
                    $output->writeln('<comment>2. Test récupération subarea :</comment>');
                    try {
                        $subareaData = $this->client->request("/subareas/$subareaId", ['lang' => 'fr']);
                        $output->writeln("  Subarea data: " . json_encode($subareaData, JSON_UNESCAPED_UNICODE));
                        
                        $subareaName = $subareaData['name']['fr'] ?? $subareaData['name']['en'] ?? 'Nom inconnu';
                        $areaId = $subareaData['areaId'] ?? null;
                        $output->writeln("  Subarea name: $subareaName");
                        $output->writeln("  Area ID: $areaId");
                        
                        if ($areaId) {
                            // Test 3: Récupérer l'area
                            $output->writeln('<comment>3. Test récupération area :</comment>');
                            try {
                                $areaData = $this->client->request("/areas/$areaId", ['lang' => 'fr']);
                                $output->writeln("  Area data: " . json_encode($areaData, JSON_UNESCAPED_UNICODE));
                                
                                $areaName = $areaData['name']['fr'] ?? $areaData['name']['en'] ?? 'Nom inconnu';
                                $superAreaId = $areaData['superAreaId'] ?? null;
                                $output->writeln("  Area name: $areaName");
                                $output->writeln("  Super Area ID: $superAreaId");
                                
                                if ($superAreaId) {
                                    // Test 4: Récupérer la super-area
                                    $output->writeln('<comment>4. Test récupération super-area :</comment>');
                                    try {
                                        $superAreaData = $this->client->request("/super-areas/$superAreaId", ['lang' => 'fr']);
                                        $output->writeln("  Super Area data: " . json_encode($superAreaData, JSON_UNESCAPED_UNICODE));
                                        
                                        $superAreaName = $superAreaData['name']['fr'] ?? $superAreaData['name']['en'] ?? 'Nom inconnu';
                                        $output->writeln("  Super Area name: $superAreaName");
                                        
                                        // Résumé final
                                        $output->writeln('<info>RÉSULTAT FINAL :</info>');
                                        $output->writeln("  Super Area: $superAreaName");
                                        $output->writeln("  Area: $areaName");
                                        $output->writeln("  Subarea: $subareaName");
                                        
                                    } catch (\Exception $e) {
                                        $output->writeln("  <error>Erreur super-area: {$e->getMessage()}</error>");
                                    }
                                }
                                
                            } catch (\Exception $e) {
                                $output->writeln("  <error>Erreur area: {$e->getMessage()}</error>");
                            }
                        }
                        
                    } catch (\Exception $e) {
                        $output->writeln("  <error>Erreur subarea: {$e->getMessage()}</error>");
                    }
                    
                    break; // On teste seulement le premier monstre avec des subareas
                }
            }
            
        } catch (\Exception $e) {
            $output->writeln("<error>Erreur générale: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Test terminé.</info>");
        return Command::SUCCESS;
    }
}
