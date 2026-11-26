<?php

namespace App\Tests\Dashboard;

use App\Dashboard\RecruitmentQueries;
use App\Entity\Characters;
use App\Entity\GhostRound;
use App\Entity\GhostVote;
use App\Entity\Ranks;
use App\Entity\Warning;
use App\Tests\DatabaseTestCase;

/**
 * Calculs du dashboard recrutement, en particulier la règle de rétention :
 * une recrue n'entre dans un palier que si elle a atteint cet âge (cohorte mature).
 */
class RecruitmentQueriesTest extends DatabaseTestCase
{
    private const NOW = '2026-11-26 12:00:00';

    private RecruitmentQueries $queries;
    private \DateTimeImmutable $now;
    private Ranks $rank;
    private Characters $alice;
    private Characters $bob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queries = static::getContainer()->get(RecruitmentQueries::class);
        $this->now = new \DateTimeImmutable(self::NOW);
        $this->rank = $this->makeRank('Néophyte', 1);
        $enroleur = $this->makeRank('Enroleur', null, false, true);
        $this->alice = $this->makeCharacter('Alice', $enroleur, '2024-01-01');
        $this->bob = $this->makeCharacter('Bob', $enroleur, '2024-01-01');
        $this->em->flush();
    }

    private function recruit(string $pseudo, string $recruitedAt, ?Characters $recruiter, ?string $leftAt = null, bool $undated = false): Characters
    {
        $c = $this->makeCharacter($pseudo, $this->rank, $recruitedAt, $recruiter);
        if ($leftAt !== null || $undated) {
            $c->setIsArchived(true);
            $c->setArchivedAt($undated ? null : new \DateTime($leftAt));
        }

        return $c;
    }

    private function row(array $rows, string $pseudo): array
    {
        return array_values(array_filter($rows, fn ($r) => $r['pseudo'] === $pseudo))[0];
    }

    // ---------------------------------------------------------------- rétention

    public function testRetentionOnlyCountsMatureRecruitsAtEachMilestone(): void
    {
        // Âges au 26/11/2026 : 200 j, 100 j, 30 j, 10 j.
        $this->recruit('Ancienne', '2026-05-10', $this->alice);                         // présente
        $this->recruit('PartieJ20', '2026-08-18', $this->alice, '2026-09-07');          // partie à J+20
        $this->recruit('Recente', '2026-10-27', $this->alice);                          // 30 j : mature J14 seulement
        $this->recruit('Toute', '2026-11-16', $this->alice);                            // 10 j : mature nulle part
        $this->em->flush();

        $alice = $this->row($this->queries->recruiters($this->now, 12, null), 'Alice');

        $this->assertSame(4, $alice['volume']);
        $this->assertSame(['rate' => 100, 'retained' => 3, 'n' => 3], $alice['retention']['d14'], 'Toute (10 j) exclue ; PartieJ20 encore là à J+14');
        $this->assertSame(['rate' => 50, 'retained' => 1, 'n' => 2], $alice['retention']['d61'], 'Recente (30 j) exclue');
        $this->assertSame(['rate' => 100, 'retained' => 1, 'n' => 1], $alice['retention']['d122'], 'seule Ancienne a 122 j');
    }

    public function testMilestoneBoundariesAndUndatedDepartures(): void
    {
        $this->recruit('PilePoil', '2026-07-01', $this->alice, '2026-08-31');           // part exactement à J+61 : retenue
        $this->recruit('LaVeille', '2026-07-01', $this->alice, '2026-08-30');           // J+60 : perdue
        $this->recruit('NonDatee', '2026-07-01', $this->alice, null, true);             // exclue de tout palier
        $this->recruit('Mature61Pile', '2026-09-26', $this->alice);                     // a 61 j aujourd'hui : mature
        $this->em->flush();

        $alice = $this->row($this->queries->recruiters($this->now, 12, null), 'Alice');

        $this->assertSame(['rate' => 67, 'retained' => 2, 'n' => 3], $alice['retention']['d61']);
        $this->assertSame(1, $alice['excluded']);
        $this->assertSame(4, $alice['volume'], 'le volume compte toutes les recrues');
    }

    public function testPeriodWindowAndNoMatureRecruit(): void
    {
        $this->recruit('Vieille', '2025-06-01', $this->alice);
        $this->recruit('Neuve', '2026-11-20', $this->bob);
        $this->em->flush();

        $this->assertSame(0, count(array_filter($this->queries->recruiters($this->now, 3, null), fn ($r) => $r['pseudo'] === 'Alice')), 'hors fenêtre de 3 mois');
        $this->assertSame(1, $this->row($this->queries->recruiters($this->now, 0, null), 'Alice')['volume'], '0 = depuis toujours');

        $bob = $this->row($this->queries->recruiters($this->now, 12, null), 'Bob');
        $this->assertSame(['rate' => null, 'retained' => 0, 'n' => 0], $bob['retention']['d14']);
    }

    // ---------------------------------------------------------------- classement

    public function testLeaderboardRanksOnRecruitsStillPresentAtJ61(): void
    {
        // Alice : 3 recrues matures, 2 présentes à J+61. Bob : 4 recrues, 1 seule présente à J+61.
        $this->recruit('A1', '2026-06-01', $this->alice);
        $this->recruit('A2', '2026-06-01', $this->alice);
        $this->recruit('A3', '2026-06-01', $this->alice, '2026-06-10');
        $this->recruit('B1', '2026-06-01', $this->bob);
        foreach (['B2', 'B3', 'B4'] as $p) {
            $this->recruit($p, '2026-06-01', $this->bob, '2026-06-20');
        }
        $this->em->flush();

        $rows = $this->queries->recruiters($this->now, 12, null);

        $this->assertSame(['Alice', 'Bob'], array_column($rows, 'pseudo'));
        $this->assertSame([1, 2], array_column($rows, 'position'));
        $this->assertSame([2, 2], array_column($rows, 'totalRecruiters'));
    }

    public function testRestrictedScopeReturnsOnlyOwnRowWithGlobalPosition(): void
    {
        $this->recruit('A1', '2026-06-01', $this->alice);
        $this->recruit('B1', '2026-06-01', $this->bob);
        $this->recruit('B2', '2026-06-01', $this->bob);
        $this->em->flush();

        $rows = $this->queries->recruiters($this->now, 12, $this->alice->getId());

        $this->assertCount(1, $rows, 'aucune ligne d\'un autre recruteur');
        $this->assertSame('Alice', $rows[0]['pseudo']);
        $this->assertSame(2, $rows[0]['position'], 'position calculée sur tous les recruteurs');
        $this->assertSame(2, $rows[0]['totalRecruiters']);
        $this->assertSame([], $this->queries->recruiters($this->now, 12, RecruitmentQueries::NOBODY));
    }

    public function testTiesShareThePosition(): void
    {
        $this->recruit('A1', '2026-06-01', $this->alice);
        $this->recruit('B1', '2026-06-01', $this->bob);
        $this->em->flush();

        $this->assertSame([1, 1], array_column($this->queries->recruiters($this->now, 12, null), 'position'));
    }

    // ---------------------------------------------------------------- semaine

    public function testWeekKpis(): void
    {
        $monday = new \DateTimeImmutable('2026-11-23 00:00:00');
        $this->recruit('Lundi', '2026-11-23', $this->alice);
        $this->recruit('Dimanche', '2026-11-29 22:00:00', $this->bob);
        $this->recruit('SemainePrecedente', '2026-11-16', $this->alice);
        $this->recruit('Il7Semaines', '2026-10-05', $this->alice);
        $this->recruit('Il9Semaines', '2026-09-21', $this->alice);                      // hors moyenne 8 sem.
        $this->recruit('PartieCetteSemaine', '2026-10-01', $this->bob, '2026-11-24 20:00:00');
        $this->em->flush();

        $w = $this->queries->week($monday, $this->now, null);

        $this->assertSame('2026-W48', $w['week']);
        $this->assertSame('2026-11-29', $w['end']);
        $this->assertTrue($w['isCurrent']);
        $this->assertSame(2, $w['recruits']);
        $this->assertSame(1, $w['previous']);
        $this->assertSame(1, $w['deltaPrevious']);
        $this->assertSame(0.4, $w['average8'], '(Precedente + 7 sem. + PartieCetteSemaine arrivée le 01/10) / 8');
        $this->assertSame(1, $w['departures']);
        $this->assertSame(1, $w['net']);
        $this->assertSame(['Lundi', 'Dimanche'], array_column($w['list'], 'pseudo'));

        $own = $this->queries->week($monday, $this->now, $this->alice->getId());
        $this->assertSame(1, $own['recruits']);
        $this->assertSame(0, $own['departures'], 'le départ concerne une recrue de Bob');
    }

    // ---------------------------------------------------------------- surveillance

    public function testWatchlistKeepsYoungRecruitsWithWarningsOrGhostVotes(): void
    {
        $warned = $this->recruit('Averti', '2026-11-20', $this->alice);
        $ghosted = $this->recruit('Fantome', '2026-10-28', $this->bob);                // 29 j : inclus
        $this->recruit('Sage', '2026-11-20', $this->alice);                            // rien à signaler
        $old = $this->recruit('TropVieux', '2026-10-27', $this->alice);                // 30 j : exclu
        $voter = $this->makeUser('votant', ['ROLE_ADMIN']);
        $round = (new GhostRound())->setOpenedAt(new \DateTime('2026-11-01'))->setThreshold(4);
        $this->em->persist($round);
        $this->em->persist((new Warning())->setCharacter($warned)->setDescription('absent en défi'));
        $this->em->persist((new Warning())->setCharacter($old)->setDescription('ancien'));
        $this->em->persist((new GhostVote())->setRound($round)->setCharacter($ghosted)->setVoter($voter));
        $this->em->flush();

        $list = $this->queries->watchlist($this->now, null);

        $this->assertSame(['Averti', 'Fantome'], array_column($list, 'pseudo'));
        $this->assertSame('absent en défi', $list[0]['lastWarning']);
        $this->assertSame(1, $list[1]['ghostVotes']);
        $this->assertSame(['Averti'], array_column($this->queries->watchlist($this->now, $this->alice->getId()), 'pseudo'));
    }

    // ---------------------------------------------------------------- cohortes & départs

    public function testMonthlyCohorts(): void
    {
        $this->recruit('Juin1', '2026-06-05', $this->alice);
        $this->recruit('Juin2', '2026-06-20', $this->alice, '2026-07-01');              // J+11
        $this->recruit('Juin3', '2026-06-25', $this->bob, null, true);                  // non datée
        $this->recruit('Nov', '2026-11-20', $this->alice);
        $this->em->flush();

        $cohorts = $this->queries->cohorts($this->now, 12, null);
        $byMonth = array_column($cohorts, null, 'month');

        $this->assertCount(12, $cohorts);
        $this->assertSame('2026-11', $cohorts[0]['month'], 'du plus récent au plus ancien');
        $this->assertSame(3, $byMonth['2026-06']['recruits']);
        $this->assertSame(1, $byMonth['2026-06']['excluded']);
        $this->assertSame(['rate' => 50, 'retained' => 1, 'n' => 2], $byMonth['2026-06']['retention']['d14']);
        $this->assertSame(['rate' => 50, 'retained' => 1, 'n' => 2], $byMonth['2026-06']['retention']['d122']);
        $this->assertSame(['rate' => null, 'retained' => 0, 'n' => 0], $byMonth['2026-11']['retention']['d14']);
        $this->assertSame(0, $byMonth['2026-01']['recruits']);
    }

    public function testDepartureTimeDistribution(): void
    {
        $this->recruit('J3', '2026-10-01', $this->alice, '2026-10-04');
        $this->recruit('J10', '2026-10-01', $this->alice, '2026-10-11');
        $this->recruit('J45', '2026-08-01', $this->bob, '2026-09-15');
        $this->recruit('J400', '2025-08-01', $this->bob, '2026-09-05');
        $this->recruit('Ancien', '2024-01-01', $this->bob, '2025-01-01');               // départ hors fenêtre
        $this->recruit('NonDate', '2025-01-01', $this->bob, null, true);
        $this->em->flush();

        $d = $this->queries->departureTimes($this->now, 12, null);

        $this->assertSame([1, 1, 0, 1, 0, 0, 1], array_column($d['buckets'], 'count'));
        $this->assertSame(4, $d['total']);
        $this->assertSame(27.5, $d['medianDays'], 'médiane de 3, 10, 45, 400');
        $this->assertSame(1, $d['undatedExcluded']);

        $alice = $this->queries->departureTimes($this->now, 12, $this->alice->getId());
        $this->assertSame(2, $alice['total']);
        $this->assertSame(0, $alice['undatedExcluded']);
    }
}
