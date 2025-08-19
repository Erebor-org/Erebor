<?php

namespace App\Command;

use App\Service\DofusdleDofusDbClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'erebor:dofusdle:test:fetch',
    description: 'Test de récupération des données DofusDB pour Dofusdle.'
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
        $output->writeln('<info>Test récupération DofusDB</info>');

        $output->writeln("\n<comment>Monstres :</comment>");
        $monsters = $this->client->findMonsters(['$limit' => 1]);
        foreach ($monsters as $monster) {
            $output->writeln('Nom : ' . ($monster['name'] ?? ''));
            $output->writeln('Description : ' . ($monster['description'] ?? ''));
        }

        $output->writeln("\n<comment>Sorts :</comment>");
        $spells = $this->client->findSpells(['$limit' => 1]);
        foreach ($spells as $spell) {
            $output->writeln('Nom : ' . ($spell['name'] ?? ''));
            $output->writeln('Description : ' . ($spell['description'] ?? ''));
        }

        $output->writeln("\n<comment>Spell-levels :</comment>");
        $spellLevels = $this->client->findSpellLevels(['$limit' => 1]);
        foreach ($spellLevels as $spellLevel) {
            $output->writeln('Nom : ' . ($spellLevel['name'] ?? ''));
            $output->writeln('Description : ' . ($spellLevel['description'] ?? ''));
        }

        $output->writeln("\n<comment>Donjons :</comment>");
        $dungeons = $this->client->findDungeons(['$limit' => 1]);
        foreach ($dungeons as $dungeon) {
            $output->writeln('Nom : ' . ($dungeon['name'] ?? ''));
            $output->writeln('Description : ' . ($dungeon['description'] ?? ''));
        }

        $output->writeln("\n<comment>Items :</comment>");
        $items = $this->client->findItems(['$limit' => 1, 'craftable' => 'true']);
        foreach ($items as $item) {
            $output->writeln('Nom : ' . ($item['name'] ?? ''));
            $output->writeln('Description : ' . ($item['description'] ?? ''));
        }

        $output->writeln("\n<comment>Recettes :</comment>");
        $recipes = $this->client->findRecipes(['$limit' => 1]);
        $output->writeln(print_r($recipes, true));

        $output->writeln("\n<info>Fin du test.</info>");
        return Command::SUCCESS;
    }
}
