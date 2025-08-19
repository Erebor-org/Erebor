<?php

namespace App\Command;

use App\Entity\DofusdleMonster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'erebor:dofusdle:export:monsters',
    description: 'Exporte tous les monstres DofusdleMonster en JSON et YAML.'
)]
class DofusdleExportMonstersCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(DofusdleMonster::class);
        $monsters = $repo->findAll();
        $data = array_map(function (DofusdleMonster $m) {
            return [
                'id' => $m->getId(),
                'dofusdb_id' => $m->getDofusdbId(),
                'name' => $m->getName(),
                'pdv' => $m->getPdv(),
                'pa' => $m->getPa(),
                'pm' => $m->getPm(),
                'family' => $m->getFamily(),
                'superRace' => $m->getSuperRace(),
                'subareas' => $m->getSubareas(),
                'areas' => $m->getAreas(),
                'favoriteSubareaId' => $m->getFavoriteSubareaId(),
                'level' => $m->getLevel(),
                'bossType' => $m->getBossType(),
                'isBoss' => $m->isBoss(),
                'element' => $m->getElement(),
                'tags' => $m->getTags(),
                'spellsCount' => $m->getSpellsCount(),
                'correspondingMiniBossId' => $m->getCorrespondingMiniBossId(),
                'correspondingMiniBossName' => $m->getCorrespondingMiniBossName(),
                'image' => $m->getImage(),
            ];
        }, $monsters);

        $jsonPath = __DIR__ . '/../../../../monsters.json';
        $yamlPath = __DIR__ . '/../../../../monsters.yaml';
        file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($yamlPath, Yaml::dump($data, 4, 2));

        $output->writeln("Exporté : " . count($data) . " monstres → $jsonPath et $yamlPath");
        return Command::SUCCESS;
    }
}
