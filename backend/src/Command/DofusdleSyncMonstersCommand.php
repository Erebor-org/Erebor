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
    description: 'Synchronise les monstres DofusDB dans la table DofusdleMonster.'
)]
class DofusdleSyncMonstersCommand extends Command
{
    private DofusdleDofusDbClient $client;
    private EntityManagerInterface $em;

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
        $output->writeln('<info>Synchronisation de tous les monstres via pagination $skip/$limit...</info>');
        $limit = 50;
        $skip = 0;
        $total = 0;
        $batchSize = 100;
        // Première page pour debug
        $firstPage = $this->client->request('/monsters', ['$limit' => $limit, '$skip' => 0]);
        $apiTotal = $firstPage['total'] ?? 'inconnu';
        $output->writeln("[DEBUG] Total annoncé par l'API : $apiTotal");
        do {
            $monsters = $this->client->findMonsters(['$limit' => $limit, '$skip' => $skip]);
            $output->writeln("[DEBUG] Page skip=$skip : " . count($monsters) . " monstres récupérés");
            foreach ($monsters as $i => $m) {
                if (empty($m['id'])) {
                    continue;
                }
                // Upsert : cherche si le monstre existe déjà
                $existing = $this->em->getRepository(DofusdleMonster::class)->findOneBy(['dofusdbId' => $m['id']]);
                $monster = $existing ?: new DofusdleMonster();
                $monster->setDofusdbId($m['id']);
                // Remplace la description par le nombre de points de vie du premier grade
                $monster->setPdv(isset($m['grades'][0]['lifePoints']) ? (int)$m['grades'][0]['lifePoints'] : null);
                $monster->setPa(isset($m['grades'][0]['actionPoints']) ? (int)$m['grades'][0]['actionPoints'] : null);
                $monster->setPm(isset($m['grades'][0]['movementPoints']) ? (int)$m['grades'][0]['movementPoints'] : null);
                // Récupère le nom de la race (famille)
                $raceName = null;
                if (isset($m['race']) && $m['race']) {
                    $raceName = $this->client->getMonsterRaceName($m['race']);
                }
                // Exemple pour subareas/areas (à adapter selon ton mapping)
                $subareasData = isset($m['subareas']) ? $this->client->getSubareaNamesAndAreas($m['subareas']) : ['subareas'=>[], 'areas'=>[]];
                $subareas = isset($subareasData['subareas']) ? array_map(fn($s) => $s ? mb_convert_encoding($s, 'UTF-8', 'auto') : $s, $subareasData['subareas']) : null;
                $areas = isset($subareasData['areas']) ? array_map(fn($a) => $a ? mb_convert_encoding($a, 'UTF-8', 'auto') : $a, $subareasData['areas']) : null;
                $monster->setSubareas($subareas);
                $monster->setAreas($areas);
                // Pour les autres champs texte :
                $monster->setFamily($raceName ? mb_convert_encoding($raceName, 'UTF-8', 'auto') : null);
                $monster->setSuperRace(isset($m['superRace']) ? mb_convert_encoding($this->client->getMonsterSuperRaceName($m['superRace']), 'UTF-8', 'auto') : null);
                $monster->setTags(array_map(fn($t) => $t ? mb_convert_encoding($t, 'UTF-8', 'auto') : $t, $m['tags'] ?? []));
                // Initialisation robuste de $miniBossName
                $miniBossName = null;
                if (!empty($m['correspondingMiniBossId'])) {
                    $miniBossName = $this->client->getMiniBossName($m['correspondingMiniBossId']);
                }
                $monster->setCorrespondingMiniBossName($miniBossName ? mb_convert_encoding($miniBossName, 'UTF-8', 'auto') : null);
                // Remplace le nom par le nom de la race
                $monster->setName($m['name'] ? mb_convert_encoding($m['name'], 'UTF-8', 'auto') : '');
                $monster->setLevel($m['grades'][0]['level'] ?? null);
                $monster->setIsBoss($m['isBoss'] ?? false);
                $monster->setElement($m['tags'][0] ?? null);
                $monster->setImage($m['img'] ?? null);
                // Super-race
                $monster->setSuperRace(isset($m['superRace']) ? mb_convert_encoding($this->client->getMonsterSuperRaceName($m['superRace']), 'UTF-8', 'auto') : null);
                // Subareas & Areas
                $subareasData = isset($m['subareas']) ? $this->client->getSubareaNamesAndAreas($m['subareas']) : ['subareas'=>[], 'areas'=>[]];
                $monster->setSubareas(array_map(fn($s) => $s ? mb_convert_encoding($s, 'UTF-8', 'auto') : $s, $subareasData['subareas']));
                $monster->setAreas(array_map(fn($a) => $a ? mb_convert_encoding($a, 'UTF-8', 'auto') : $a, $subareasData['areas']));
                $monster->setFavoriteSubareaId($m['favoriteSubareaId'] ?? null);
                // BossType
                $bossType = 'normal';
                if (!empty($m['isBoss'])) $bossType = 'boss';
                elseif (!empty($m['isMiniBoss'])) $bossType = 'mini-boss';
                $monster->setBossType($bossType ? mb_convert_encoding($bossType, 'UTF-8', 'auto') : null);
                // Tags
                $monster->setTags(array_map(fn($t) => $t ? mb_convert_encoding($t, 'UTF-8', 'auto') : $t, $m['tags'] ?? []));
                // SpellsCount
                $monster->setSpellsCount(isset($m['spells']) ? count($m['spells']) : null);
                // Mini-boss associé
                $monster->setCorrespondingMiniBossId($m['correspondingMiniBossId'] ?? null);
                $miniBossName = null;
                if (!empty($m['correspondingMiniBossId'])) {
                    $miniBossName = $this->client->getMiniBossName($m['correspondingMiniBossId']);
                }
                $monster->setCorrespondingMiniBossName($miniBossName ? mb_convert_encoding($miniBossName, 'UTF-8', 'auto') : null);
                $this->em->persist($monster);
                $total++;
                if (($total % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                    $output->writeln("Flush batch à $total");
                }
            }
            $skip += $limit;
            $output->writeln("Synchronisés : $total");
        } while (count($monsters) === $limit);
        $this->em->flush();
        $this->em->clear();
        $output->writeln("<info>Synchronisation terminée. Total : $total monstres.</info>");
        return Command::SUCCESS;
    }
}
