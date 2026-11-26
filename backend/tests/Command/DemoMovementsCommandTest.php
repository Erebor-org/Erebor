<?php

namespace App\Tests\Command;

use App\Command\DemoMovementsCommand;
use App\Dashboard\HomeDashboardQueries;
use App\Tests\DatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DemoMovementsCommandTest extends DatabaseTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['Néophyte' => 1, 'Explorateur' => 14, 'Sentinelle' => 61, 'Gardien' => 122, 'Vétéran' => 365] as $name => $days) {
            $this->makeRank($name, $days);
        }
        $this->recruiter = $this->makeCharacter('VraiRecruteur', $this->makeRank('Enroleur', null, false, true), '2023-01-01');
        $this->makeCharacter('VraiMembre', $this->makeRank('Gardien bis', 122), '2025-01-01', $this->recruiter);
        $this->em->flush();

        $application = new Application(static::$kernel);
        $this->tester = new CommandTester($application->find('app:demo:movements'));
    }

    private $recruiter;

    public function testGeneratesCoherentMovementsOverSeveralMonths(): void
    {
        $this->assertSame(Command::SUCCESS, $this->tester->execute(['--months' => '12', '--per-month' => '8']));

        $db = $this->em->getConnection();
        $demo = $db->fetchAllAssociative('SELECT * FROM characters WHERE notes = ?', [DemoMovementsCommand::MARKER]);
        $this->assertGreaterThan(50, count($demo));

        $now = new \DateTimeImmutable();
        $departed = 0;
        foreach ($demo as $row) {
            $this->assertSame($this->recruiter->getId(), $row['recruiter_id']);
            $this->assertLessThanOrEqual($now->format('Y-m-d H:i:s'), $row['recruited_at']);
            if ($row['is_archived']) {
                $departed++;
                $this->assertNotNull($row['archived_at'], 'chaque départ est daté');
                $this->assertGreaterThan($row['recruited_at'], $row['archived_at']);
                $this->assertLessThanOrEqual($now->format('Y-m-d H:i:s'), $row['archived_at']);
            } else {
                $this->assertNull($row['archived_at']);
            }
        }
        $this->assertGreaterThan(0, $departed);

        // Un événement « left » par départ, à la date du départ.
        $mismatched = (int) $db->fetchOne(<<<'SQL'
            SELECT COUNT(*) FROM characters c
            LEFT JOIN guild_event e ON e.character_id = c.id AND e.type = 'left' AND e.occurred_at = c.archived_at
            WHERE c.notes = ? AND c.is_archived AND e.id IS NULL
        SQL, [DemoMovementsCommand::MARKER]);
        $this->assertSame(0, $mismatched);
        $this->assertSame($departed, count($this->events('left')));

        // La courbe : des entrées tous les mois, des sorties sur plusieurs mois.
        $monthly = static::getContainer()->get(HomeDashboardQueries::class)->monthlyFlow($now);
        $this->assertCount(12, array_filter($monthly, fn ($m) => $m['entries'] > 0));
        $this->assertGreaterThan(3, count(array_filter($monthly, fn ($m) => $m['exits'] > 0)));
    }

    public function testSameSeedGivesSameData(): void
    {
        $this->tester->execute(['--seed' => '3', '--months' => '3']);
        $first = $this->em->getConnection()->fetchFirstColumn('SELECT pseudo || recruited_at || coalesce(archived_at::text, \'\') FROM characters WHERE notes = ? ORDER BY 1', [DemoMovementsCommand::MARKER]);
        $this->tester->execute(['--purge' => true]);
        $this->tester->execute(['--seed' => '3', '--months' => '3']);
        $second = $this->em->getConnection()->fetchFirstColumn('SELECT pseudo || recruited_at || coalesce(archived_at::text, \'\') FROM characters WHERE notes = ? ORDER BY 1', [DemoMovementsCommand::MARKER]);

        $this->assertSame($first, $second);
    }

    public function testRefusesToRunTwiceAndPurgeRemovesOnlyDemoData(): void
    {
        $this->tester->execute([]);
        $this->assertSame(Command::FAILURE, $this->tester->execute([]), 'pas de doublons : purge obligatoire avant de régénérer');

        $this->assertSame(Command::SUCCESS, $this->tester->execute(['--purge' => true]));

        $db = $this->em->getConnection();
        $this->assertSame(0, (int) $db->fetchOne('SELECT COUNT(*) FROM characters WHERE notes = ?', [DemoMovementsCommand::MARKER]));
        $this->assertSame([], $this->events());
        $this->assertSame(['VraiMembre', 'VraiRecruteur'], $db->fetchFirstColumn('SELECT pseudo FROM characters ORDER BY pseudo'));
    }
}
