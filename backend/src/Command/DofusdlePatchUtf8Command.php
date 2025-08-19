<?php

namespace App\Command;

use App\Entity\DofusdleMonster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erebor:dofusdle:patch:utf8',
    description: 'Force l’encodage UTF-8 sur tous les champs texte des monstres Dofusdle.'
)]
class DofusdlePatchUtf8Command extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(DofusdleMonster::class);
        $monsters = $repo->findAll();
        $count = 0;
        foreach ($monsters as $monster) {
            $monster->setFamily($monster->getFamily() ? mb_convert_encoding($monster->getFamily(), 'UTF-8', 'auto') : null);
            $monster->setSuperRace($monster->getSuperRace() ? mb_convert_encoding($monster->getSuperRace(), 'UTF-8', 'auto') : null);
            $monster->setSubareas(array_map(fn($s) => $s ? mb_convert_encoding($s, 'UTF-8', 'auto') : $s, $monster->getSubareas() ?? []));
            $monster->setAreas(array_map(fn($a) => $a ? mb_convert_encoding($a, 'UTF-8', 'auto') : $a, $monster->getAreas() ?? []));
            $monster->setTags(array_map(fn($t) => $t ? mb_convert_encoding($t, 'UTF-8', 'auto') : $t, $monster->getTags() ?? []));
            $monster->setCorrespondingMiniBossName($monster->getCorrespondingMiniBossName() ? mb_convert_encoding($monster->getCorrespondingMiniBossName(), 'UTF-8', 'auto') : null);
            $count++;
        }
        $this->em->flush();
        $output->writeln("Patch UTF-8 appliqué à $count monstres.");
        return Command::SUCCESS;
    }
}

