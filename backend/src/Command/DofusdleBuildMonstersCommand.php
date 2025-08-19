<?php

namespace App\Command;

use App\Service\DofusdleDofusDbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erebor:dofusdle:build:monsters',
    description: 'Construit le fichier NDJSON.gz de tous les monstres Dofus-dle Classic.'
)]
class DofusdleBuildMonstersCommand extends Command
{
    public function __construct(
        private DofusdleDofusDbClient $client,
        private string $projectDir = __DIR__ . '/../../..' // KernelProjectDir si tu préfères l'injecter
    ) { parent::__construct(); }

    protected function configure(): void
    {
        $this
            ->addOption('lang', null, InputOption::VALUE_OPTIONAL, 'Langue (fr par défaut)', 'fr')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Taille de page', 500)
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Numéro de page à traiter (0 = toutes)', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lang   = (string)($input->getOption('lang') ?? 'fr');
        $limit  = (int)($input->getOption('limit') ?? 500);
        $pageOpt= (int)($input->getOption('page') ?? 0);

        // 1) Répertoire de sortie persistant
        $outDir = $this->projectDir . '/var/dofusdle';
        if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
            $output->writeln("<error>Impossible de créer $outDir</error>");
            return Command::FAILURE;
        }

        $outputPath = $outDir . '/monsters.ndjson.gz';
        $tmpPath    = tempnam($outDir, 'monsters_'); // fichier temporaire "normal"
        if ($tmpPath === false) {
            $output->writeln("<error>tempnam() a échoué dans $outDir</error>");
            return Command::FAILURE;
        }
        $gzPath = $tmpPath . '.gz'; // on crée un gzip à partir du temp

        $output->writeln("[INFO] Ecriture gzip -> $gzPath");
        // 2) IMPORTANT: 'wb9' (binaire)
        $gz = @gzopen($gzPath, 'wb9');
        if (!$gz) {
            $output->writeln("<error>gzopen($gzPath, 'wb9') a échoué (vérifie ext/zlib & permissions)</error>");
            @unlink($tmpPath);
            return Command::FAILURE;
        }

        // 3) Pré-chargement des subareas/areas (boucles paginées)
        $output->writeln('[INFO] Préchargement subareas/areas…');
        $subareaToArea = [];
        $areaIdSet = [];

        // Feathers → pagine: lis jusqu’à total
        $saSkip = 0; $saTotal = 1;
        while ($saSkip < $saTotal) {
            $saPage = $this->client->request('/subareas', ['$limit' => 1000, '$skip' => $saSkip, '$select[]' => 'id', '$select[]' => 'areaId']);
            $saTotal = (int)($saPage['total'] ?? 0);
            foreach ($saPage['data'] ?? [] as $sub) {
                $subareaToArea[(int)$sub['id']] = $sub['areaId'] ?? null;
            }
            $saSkip += 1000;
        }

        $aSkip = 0; $aTotal = 1;
        while ($aSkip < $aTotal) {
            $aPage = $this->client->request('/areas', ['$limit' => 1000, '$skip' => $aSkip, '$select[]' => 'id']);
            $aTotal = (int)($aPage['total'] ?? 0);
            foreach ($aPage['data'] ?? [] as $area) {
                $areaIdSet[(int)$area['id']] = true;
            }
            $aSkip += 1000;
        }

        // 4) Extraction des monstres
        $output->writeln('[INFO] Extraction des monstres…');
        $skip = 0; $total = 0; $pageNum = 0;
        $mFirst = $this->client->request('/monsters', ['$limit' => $limit, '$skip' => 0,
            '$select[]' => 'id',
            '$select[]' => "name.$lang",
            '$select[]' => 'grades',
            '$select[]' => 'race',
            '$select[]' => 'subareas',
            '$select[]' => 'isBoss',
            '$select[]' => 'isMiniBoss',
            '$select[]' => 'tags',
            '$select[]' => 'img',
            '$select[]' => 'scaleGradeRef',
        ]);
        $grandTotal = (int)($mFirst['total'] ?? 0);
        $output->writeln("[INFO] Total annoncé par l’API: $grandTotal");
        // on traitera aussi la première page dans la boucle
        $skip = 0;

        while ($skip < $grandTotal) {
            if ($pageOpt && $pageNum !== $pageOpt) {
                $skip += $limit; $pageNum++; continue;
            }

            $page = ($skip === 0) ? $mFirst
                : $this->client->request('/monsters', [
                    '$limit' => $limit, '$skip' => $skip,
                    '$select[]' => 'id', '$select[]' => "name.$lang", '$select[]' => 'grades',
                    '$select[]' => 'race', '$select[]' => 'subareas',
                    '$select[]' => 'isBoss', '$select[]' => 'isMiniBoss',
                    '$select[]' => 'tags', '$select[]' => 'img', '$select[]' => 'scaleGradeRef',
                ]);

            foreach (($page['data'] ?? []) as $m) {
                $id = $m['id'] ?? null;
                $name = $m['name'][$lang] ?? null;
                $grades = $m['grades'] ?? [];
                if (!$id || !$name || !$grades) { continue; }

                // grade de référence
                $refGrade = null;
                if (!empty($m['scaleGradeRef'])) {
                    foreach ($grades as $g) {
                        if (($g['grade'] ?? null) == $m['scaleGradeRef']) { $refGrade = $g; break; }
                    }
                }
                if (!$refGrade) {
                    $refGrade = $grades[(int)floor((count($grades)-1)/2)];
                }

                $subareaIds = array_map('intval', $m['subareas'] ?? []);
                $areaIds = [];
                foreach ($subareaIds as $sid) {
                    $aid = $subareaToArea[$sid] ?? null;
                    if ($aid !== null && isset($areaIdSet[(int)$aid])) { $areaIds[] = (int)$aid; }
                }
                $areaIds = array_values(array_unique($areaIds));

                $row = [
                    'id' => (int)$id,
                    'name' => $name,
                    'raceId' => (int)($m['race'] ?? 0),
                    'subareaIds' => $subareaIds,
                    'areaIds' => $areaIds,
                    'isBoss' => (bool)($m['isBoss'] ?? false),
                    'isMiniBoss' => (bool)($m['isMiniBoss'] ?? false),
                    'tags' => array_values($m['tags'] ?? []),
                    'img' => $m['img'] ?? null,
                    'ref' => [
                        'level' => (int)($refGrade['level'] ?? 0),
                        'ap'    => (int)($refGrade['actionPoints'] ?? 0),
                        'mp'    => (int)($refGrade['movementPoints'] ?? 0),
                        'hp'    => (int)($refGrade['lifePoints'] ?? 0),
                        'res'   => [
                            'earth'   => (int)($refGrade['earthResistance'] ?? 0),
                            'fire'    => (int)($refGrade['fireResistance'] ?? 0),
                            'water'   => (int)($refGrade['waterResistance'] ?? 0),
                            'air'     => (int)($refGrade['airResistance'] ?? 0),
                            'neutral' => (int)($refGrade['neutralResistance'] ?? 0),
                        ],
                    ],
                ];

                $written = gzwrite($gz, json_encode($row, JSON_UNESCAPED_UNICODE) . "\n");
                if ($written === false) {
                    $output->writeln("<error>gzwrite a échoué pour l’id $id</error>");
                } else {
                    $total++;
                }
            }

            $skip += $limit; $pageNum++;
            $output->writeln("[INFO] Progression: $total / $grandTotal");
            if ($pageOpt && $pageNum > $pageOpt) { break; }
        }

        gzclose($gz);

        // 5) Swap atomique
        if (!@rename($gzPath, $outPath = $this->projectDir . '/var/dofusdle/monsters.ndjson.gz')) {
            // si rename cross-FS échoue, copie/efface
            if (!@copy($gzPath, $outPath)) {
                $output->writeln("<error>Impossible de déplacer $gzPath vers $outPath</error>");
                @unlink($gzPath); @unlink($tmpPath);
                return Command::FAILURE;
            }
            @unlink($gzPath);
        }
        @unlink($tmpPath); // le stub temp de tempnam ne sert plus

        if (!file_exists($outPath)) {
            $output->writeln("<error>Le fichier final n’existe pas: $outPath</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Export NDJSON.gz terminé ($total) → $outPath</info>");
        return Command::SUCCESS;
    }
}
