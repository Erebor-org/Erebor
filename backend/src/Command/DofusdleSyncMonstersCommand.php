<?php

namespace App\Command;

use App\Entity\DofusdleMonster;
use App\Service\DofusdleDofusDbClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(
    name: 'erebor:dofusdle:sync:monsters',
    description: 'Synchronise les monstres DofusDB dans la table DofusdleMonster selon les nouvelles spécifications.'
)]
class DofusdleSyncMonstersCommand extends Command
{
    private DofusdleDofusDbClient $client;
    private EntityManagerInterface $em;
    private array $cache = []; // Cache simple pour éviter les appels doublons

    public function __construct(DofusdleDofusDbClient $client, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->client = $client;
        $this->em = $em;
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('min-id', null, InputOption::VALUE_OPTIONAL, 'ID minimum du monstre à synchroniser', null)
            ->addOption('max-id', null, InputOption::VALUE_OPTIONAL, 'ID maximum du monstre à synchroniser', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>Astuce : pour éviter les erreurs mémoire, lance la commande avec : php -d memory_limit=-1 bin/console erebor:dofusdle:sync:monsters</comment>');
        @ini_set('memory_limit', '-1');
        
        $output->writeln('<info>Synchronisation des monstres selon les nouvelles spécifications...</info>');
        $output->writeln('<info>Filtre : isQuestMonster = false</info>');
        $output->writeln('<info>Filtre : Doit avoir au moins une des 3 zones (superArea, area, subarea)</info>');
        
        $limit = 50;
        $skip = 0;
        $total = 0;
        $batchSize = 100;
        
        // Première page pour debug
        $firstPage = $this->client->request('/monsters', ['$limit' => $limit, '$skip' => 0]);
        $apiTotal = $firstPage['total'] ?? 'inconnu';
        $output->writeln("[DEBUG] Total annoncé par l'API : $apiTotal");

        // Pagination : récupérer toutes les pages
        do {
            $output->writeln("[DEBUG] Page skip=$skip : récupération de $limit monstres...");
            $monsters = $this->client->findMonsters(['$limit' => $limit, '$skip' => $skip]);
            $output->writeln("[DEBUG] Page skip=$skip : " . count($monsters) . " monstres récupérés");

            if (empty($monsters)) {
                break;
            }
            
            foreach ($monsters as $i => $m) {
                if (empty($m['id'])) {
                    continue;
                }
                
                // FILTRE : Ne traite que les monstres non-quest
                if (isset($m['isQuestMonster']) && $m['isQuestMonster'] === true) {
                    $output->writeln("  [DEBUG] Monstre {$m['id']} ignoré: isQuestMonster=true");
                    continue;
                }
                
                // FILTRE : Ne traite que les monstres qui ont des subareas (nécessaire pour avoir des zones)
                if (empty($m['subareas'])) {
                    $output->writeln("  [DEBUG] Monstre {$m['id']} ignoré: pas de subareas");
                    continue;
                }
                
                $output->writeln("  [DEBUG] Traitement du monstre {$m['id']} - {$m['name']}");
                
                // Upsert : cherche si le monstre existe déjà
                $existing = $this->em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $m['id']]);
                $monster = $existing ?: new DofusdleMonster();
                
                $monster->setDofusdbId($m['id']);
                
                // Nom : monster.name.fr si dispo, sinon monster.name.en
                $name = $m['name']['fr'] ?? $m['name']['en'] ?? $m['name'] ?? 'Inconnu';
                $monster->setName(mb_convert_encoding($name, 'UTF-8', 'auto'));
                
                // ImageUrl : monster.img si présent, sinon fallback
                $imageUrl = $m['img'] ?? "https://api.dofusdb.fr/img/monsters/{$m['gfxId']}.png";
                $monster->setImageUrl($imageUrl);
                
                // Extraction des min/max depuis les grades
                if (isset($m['grades']) && is_array($m['grades']) && !empty($m['grades'])) {
                    $this->extractGradeStats($monster, $m['grades']);
                }
                
                // Résolution de la race
                if (isset($m['race'])) {
                    $raceName = $this->resolveRaceName($m['race']);
                    $monster->setRace($raceName);
                }
                
                // Construction des zones (première zone trouvée)
                if (isset($m['subareas']) && !empty($m['subareas'])) {
                    $output->writeln("  [DEBUG] Monstre {$m['id']} - subareas: " . json_encode($m['subareas']));
                    $zoneData = $this->resolveFirstZone($m['subareas'][0]);
                    $output->writeln("  [DEBUG] zoneData récupérée: " . json_encode($zoneData));
                    if ($zoneData) {
                        $monster->setSuperArea($zoneData['superAreaName']);
                        $monster->setArea($zoneData['areaName']);
                        $monster->setSubarea($zoneData['subareaName']);
                        $output->writeln("  [DEBUG] Zones assignées: {$zoneData['superAreaName']} > {$zoneData['areaName']} > {$zoneData['subareaName']}");
                    } else {
                        $output->writeln("  [DEBUG] Aucune zone récupérée pour subareaId: {$m['subareas'][0]}");
                    }
                } else {
                    $output->writeln("  [DEBUG] Monstre {$m['id']} - Pas de subareas trouvées");
                }
                
                // FILTRE FINAL : Ne pas importer si les 3 zones sont NULL
                if (!$monster->getSuperArea() && !$monster->getArea() && !$monster->getSubarea()) {
                    $output->writeln("  [DEBUG] Monstre {$m['id']} ignoré: toutes les zones sont NULL");
                    continue;
                }
                
                $this->em->persist($monster);
                $total++;
                
                // Batch processing pour éviter les problèmes de mémoire
                if (($total % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                    $this->em->detach($monster);
                    $output->writeln("Progression : $total monstres...");
                }
            }
            
            $skip += $limit;
            
        } while (count($monsters) === $limit);
        
        // Final flush
        $this->em->flush();
        $this->em->clear();
        
        $output->writeln("<info>Synchronisation terminée. Total : $total monstres non-quest avec zones synchronisés.</info>");
        
        return Command::SUCCESS;
    }

    /**
     * Extrait les statistiques min/max depuis les grades
     */
    private function extractGradeStats(DofusdleMonster $monster, array $grades): void
    {
        $levels = [];
        $hps = [];
        $aps = [];
        $mps = [];
        $resistances = [
            'earth' => [],
            'air' => [],
            'fire' => [],
            'water' => [],
            'neutral' => []
        ];

        foreach ($grades as $grade) {
            // Level
            if (isset($grade['level']) && is_numeric($grade['level'])) {
                $levels[] = (int)$grade['level'];
            }
            
            // HP (lifePoints)
            if (isset($grade['lifePoints']) && is_numeric($grade['lifePoints'])) {
                $hps[] = (int)$grade['lifePoints'];
            }
            
            // AP (actionPoints)
            if (isset($grade['actionPoints']) && is_numeric($grade['actionPoints'])) {
                $aps[] = (int)$grade['actionPoints'];
            }
            
            // MP (movementPoints)
            if (isset($grade['movementPoints']) && is_numeric($grade['movementPoints'])) {
                $mps[] = (int)$grade['movementPoints'];
            }
            
            // Résistances
            foreach (['earthResistance', 'airResistance', 'fireResistance', 'waterResistance', 'neutralResistance'] as $resistanceKey) {
                $element = str_replace('Resistance', '', $resistanceKey);
                if (isset($grade[$resistanceKey]) && is_numeric($grade[$resistanceKey])) {
                    $resistances[$element][] = (int)$grade[$resistanceKey];
                }
            }
        }

        // Définir les min/max
        if (!empty($levels)) {
            $monster->setLevelMin(min($levels));
            $monster->setLevelMax(max($levels));
        }
        
        if (!empty($hps)) {
            $monster->setHpMin(min($hps));
            $monster->setHpMax(max($hps));
        }
        
        if (!empty($aps)) {
            $monster->setApMin(min($aps));
            $monster->setApMax(max($aps));
        }
        
        if (!empty($mps)) {
            $monster->setMpMin(min($mps));
            $monster->setMpMax(max($mps));
        }

        // Résistances max
        $resistancesMax = [];
        foreach ($resistances as $element => $values) {
            if (!empty($values)) {
                $resistancesMax[$element] = max($values);
            } else {
                $resistancesMax[$element] = 0;
            }
        }
        $monster->setResistancesMax($resistancesMax);
    }

    /**
     * Résout le nom de la race
     */
    private function resolveRaceName(int $raceId): ?string
    {
        $cacheKey = "race_$raceId";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            $raceData = $this->client->request("/monster-races/$raceId", ['lang' => 'fr']);
            $raceName = $raceData['name']['fr'] ?? $raceData['name']['en'] ?? null;
            $this->cache[$cacheKey] = $raceName;
            return $raceName;
        } catch (\Exception $e) {
            $this->cache[$cacheKey] = null;
            return null;
        }
    }

    /**
     * Résout la hiérarchie complète d'une zone
     */
    private function resolveAreaHierarchy(int $subareaId): ?array
    {
        $cacheKey = "subarea_$subareaId";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        try {
            // 1. Récupérer la subarea
            $subareaData = $this->client->request("/subareas/$subareaId", ['lang' => 'fr']);
            $subareaName = $subareaData['name']['fr'] ?? $subareaData['name']['en'] ?? null;
            $areaId = $subareaData['areaId'] ?? null;

            if (!$areaId) {
                $this->cache[$cacheKey] = null;
                return null;
            }

            // 2. Récupérer l'area
            $areaData = $this->client->request("/areas/$areaId", ['lang' => 'fr']);
            $areaName = $areaData['name']['fr'] ?? $areaData['name']['en'] ?? null;
            $superAreaId = $areaData['superAreaId'] ?? null;

            // 3. Récupérer la super-area
            $superAreaName = null;
            if ($superAreaId) {
                $superAreaData = $this->client->request("/super-areas/$superAreaId", ['lang' => 'fr']);
                $superAreaName = $superAreaData['name']['fr'] ?? $superAreaData['name']['en'] ?? null;
            }

            $result = [
                'superAreaName' => mb_convert_encoding($superAreaName ?? 'Inconnu', 'UTF-8', 'auto'),
                'areaName' => mb_convert_encoding($areaName ?? 'Inconnu', 'UTF-8', 'auto'),
                'subareaName' => mb_convert_encoding($subareaName ?? 'Inconnu', 'UTF-8', 'auto')
            ];

            $this->cache[$cacheKey] = $result;
            return $result;

        } catch (\Exception $e) {
            // Debug: afficher l'erreur pour comprendre le problème
            error_log("Erreur lors de la résolution de la zone pour subareaId $subareaId: " . $e->getMessage());
            $this->cache[$cacheKey] = null;
            return null;
        }
    }

    /**
     * Résout la première zone trouvée parmi les subareas
     */
    private function resolveFirstZone(int $subareaId): ?array
    {
        return $this->resolveAreaHierarchy($subareaId);
    }
}
