<?php

namespace App\Tests\Dashboard;

use App\Dashboard\HomeDashboardQueries;
use App\Entity\Characters;
use App\Entity\GhostRound;
use App\Entity\GhostVote;
use App\Entity\GuildEvent;
use App\Entity\Ranks;
use App\Entity\Warning;
use App\Tests\DatabaseTestCase;

/**
 * Agrégations de l'accueil, exécutées sur PostgreSQL avec une date « maintenant » figée.
 */
class HomeDashboardQueriesTest extends DatabaseTestCase
{
    private const NOW = '2026-11-26 12:00:00';

    private HomeDashboardQueries $queries;
    private \DateTimeImmutable $now;
    /** @var array<string, Ranks> */
    private array $ranks = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->queries = static::getContainer()->get(HomeDashboardQueries::class);
        $this->now = new \DateTimeImmutable(self::NOW);
        foreach (['Néophyte' => 1, 'Explorateur' => 14, 'Sentinelle' => 61, 'Gardien' => 122, 'Vétéran' => 365] as $name => $days) {
            $this->ranks[$name] = $this->makeRank($name, $days);
        }
        $this->ranks['Enroleur'] = $this->makeRank('Enroleur', null, false, true);
        $this->ranks['Conseiller'] = $this->makeRank('Conseiller', null, true, true);
    }

    private function member(string $pseudo, string $recruitedOn, string $rank = 'Néophyte', ?Characters $recruiter = null): Characters
    {
        return $this->makeCharacter($pseudo, $this->ranks[$rank], $recruitedOn, $recruiter);
    }

    private function leave(Characters $character, ?string $on): void
    {
        $character->setIsArchived(true);
        $character->setArchivedAt($on ? new \DateTime($on) : null);
    }

    // ---------------------------------------------------------------- compteurs

    public function testCountersAndThirtyDayFlow(): void
    {
        $me = $this->member('Moi', '2024-01-01', 'Vétéran');
        $this->member('Ancien', '2025-01-01', 'Gardien', $me);
        $this->member('Nouveau', '2026-11-20', 'Néophyte', $me);          // entrée < 30 j
        $this->leave($this->member('PartiRecemment', '2026-06-01', 'Gardien'), '2026-11-10'); // sortie < 30 j
        $this->leave($this->member('PartiAvant', '2026-01-01', 'Gardien'), '2026-09-01');
        $this->leave($this->member('PartiNonDate', '2025-01-01', 'Gardien'), null);
        $this->em->flush();

        $c = $this->queries->counters($this->now, $me->getId());

        $this->assertSame(['value' => 3, 'delta30' => 0], $c['active'], 'il y a 30 j : Moi, Ancien, PartiRecemment ; aujourd\'hui : Moi, Ancien, Nouveau');
        $this->assertSame(['value' => 3, 'delta30' => 1], $c['archived']);
        $this->assertSame(['value' => 2, 'delta30' => 1], $c['recruitedByMe']);
        $this->assertSame(['entries' => 1, 'exits' => 1, 'net' => 0], $c['flow30']);
        $this->assertSame(1, $c['undatedDepartures']);
    }

    public function testCountersWithoutLinkedCharacter(): void
    {
        $this->member('Quelqu\'un', '2026-01-01');
        $this->em->flush();

        $this->assertSame(['value' => 0, 'delta30' => 0], $this->queries->counters($this->now, null)['recruitedByMe']);
    }

    public function testMonthlyFlowCoversTwelveMonthsIncludingTheCurrentOne(): void
    {
        $this->member('Novembre', '2026-11-02');
        $this->member('Decembre2025', '2025-12-15');
        $this->member('TropVieux', '2025-11-30');                        // hors fenêtre
        $this->leave($this->member('Parti', '2026-01-01', 'Gardien'), '2026-11-05 18:00:00');
        $this->em->flush();

        $monthly = $this->queries->monthlyFlow($this->now);

        $this->assertCount(12, $monthly);
        $this->assertSame('2025-12', $monthly[0]['month']);
        $this->assertSame(['month' => '2025-12', 'entries' => 1, 'exits' => 0, 'net' => 1], $monthly[0]);
        $this->assertSame('2026-01', $monthly[1]['month']);
        $this->assertSame(1, $monthly[1]['entries']);
        $this->assertSame(['month' => '2026-11', 'entries' => 1, 'exits' => 1, 'net' => 0], $monthly[11]);
    }

    public function testClassDistributionCountsActiveMainsOnly(): void
    {
        $this->member('A', '2026-01-01')->setClass('cra');
        $this->member('B', '2026-01-01')->setClass('cra');
        $this->member('C', '2026-01-01')->setClass('iop');
        $parti = $this->member('D', '2026-01-01');
        $parti->setClass('iop');
        $this->leave($parti, '2026-10-01');
        $this->em->flush();

        $this->assertSame([['class' => 'cra', 'count' => 2], ['class' => 'iop', 'count' => 1]], $this->queries->classDistribution());
    }

    // ---------------------------------------------------------------- fidélité / ancienneté

    public function testRetention61CountsOnlyMatureCohortsAndExcludesUndatedDepartures(): void
    {
        // Fenêtre (NOW = 26/11/2026) : arrivées entre le 26/03 (exclu) et le 26/09/2026.
        $this->member('Reste', '2026-06-01');
        $this->leave($this->member('PartiApresJ61', '2026-05-01'), '2026-07-15');   // J+75 : compté présent
        $this->leave($this->member('PartiAvantJ61', '2026-06-01'), '2026-06-20');   // J+19 : parti
        $this->leave($this->member('PartiPileJ61', '2026-07-01'), '2026-08-31');    // J+61 pile : présent
        $this->leave($this->member('NonDate', '2026-06-10'), null);                 // exclu
        $this->member('PasMature', '2026-10-01');                                   // J+56 aujourd'hui
        $this->member('TropAncien', '2026-03-01');                                  // hors fenêtre
        $this->em->flush();

        $r = $this->queries->retention61($this->now);

        $this->assertSame(4, $r['n'], 'Reste, PartiApresJ61, PartiAvantJ61, PartiPileJ61');
        $this->assertSame(3, $r['retained']);
        $this->assertSame(75, $r['rate']);
        $this->assertSame(1, $r['excluded']);
        $this->assertSame(6, $r['cohortMonths']);
    }

    public function testRetention61DeltaComparesWithThirtyDaysAgo(): void
    {
        // Il y a 30 j (27/10) : seules les arrivées jusqu'au 27/08 sont matures.
        $this->member('Ancien', '2026-07-01');                                      // mature avant et maintenant
        $this->leave($this->member('NouveauMature', '2026-09-10'), '2026-09-15');   // mature seulement maintenant
        $this->em->flush();

        $r = $this->queries->retention61($this->now);

        $this->assertSame(50, $r['rate']);
        $this->assertSame(-50, $r['deltaPoints'], '100 % il y a 30 j → 50 % aujourd\'hui');
    }

    public function testRetention61WithoutMatureRecruits(): void
    {
        $this->member('Recent', '2026-11-01');
        $this->em->flush();

        $r = $this->queries->retention61($this->now);

        $this->assertNull($r['rate']);
        $this->assertSame(0, $r['n']);
        $this->assertNull($r['deltaPoints']);
    }

    public function testMySeniorityRank(): void
    {
        $this->member('Doyen', '2020-01-01', 'Vétéran');
        $this->member('ExAequo1', '2024-05-01', 'Vétéran');
        $this->member('ExAequo2', '2024-05-01', 'Vétéran');
        $me = $this->member('Moi', '2025-02-01', 'Vétéran');
        $this->member('Jeune', '2026-11-01');
        $this->leave($this->member('PlusAncienParti', '2023-01-01', 'Vétéran'), '2026-11-15'); // parti il y a 11 j
        $this->leave($this->member('PartiDepuisLongtemps', '2022-01-01', 'Vétéran'), '2026-01-01');
        $this->em->flush();

        $rank = $this->queries->mySeniorityRank($this->now, $me->getId());

        $this->assertSame(4, $rank['position'], 'Doyen + 2 ex aequo avant moi');
        $this->assertSame(5, $rank['total']);
        $this->assertSame(80, $rank['topPercent']);
        $this->assertSame(1, $rank['placesGained'], 'PlusAncienParti était devant il y a 30 j');

        $this->assertSame(2, $this->queries->mySeniorityRank($this->now, $this->em->getRepository(Characters::class)->findOneBy(['pseudo' => 'ExAequo2'])->getId())['position']);
    }

    public function testMySeniorityRankIsNullForNewcomersDeltaAndArchivedCharacters(): void
    {
        $newcomer = $this->member('Arrivant', '2026-11-20');
        $gone = $this->member('Parti', '2025-01-01', 'Vétéran');
        $this->leave($gone, '2026-10-01');
        $this->em->flush();

        $this->assertNull($this->queries->mySeniorityRank($this->now, $newcomer->getId())['placesGained'], 'pas encore membre il y a 30 j');
        $this->assertNull($this->queries->mySeniorityRank($this->now, $gone->getId()));
        $this->assertNull($this->queries->mySeniorityRank($this->now, null));
    }

    // ---------------------------------------------------------------- rangs

    public function testUpcomingPromotionsWindowIsTodayToSevenDays(): void
    {
        $this->member('Aujourdhui', '2026-11-12');                    // J+14 = 26/11
        $this->member('DansSept', '2026-09-27', 'Explorateur');       // J+61 = 27/11…
        $this->member('LimiteHaute', '2026-10-01', 'Explorateur');    // J+61 = 01/12
        $this->member('Hier', '2026-11-11');                          // J+14 = 25/11 : passé
        $this->member('DansHuit', '2026-11-20');                      // J+14 = 04/12 : trop loin
        $this->member('Fonction', '2026-11-12', 'Enroleur');          // rang de fonction : exclu
        $this->leave($this->member('Parti', '2026-11-12'), '2026-11-20');
        $this->member('Arrivant', '2026-11-26');                      // J+1 = Néophyte : pas une montée
        $this->em->flush();

        $upcoming = $this->queries->upcomingPromotions($this->now);

        $this->assertSame(
            [['Aujourdhui', 'Explorateur', '2026-11-26'], ['DansSept', 'Sentinelle', '2026-11-27'], ['LimiteHaute', 'Sentinelle', '2026-12-01']],
            array_map(fn ($p) => [$p['pseudo'], $p['rank'], $p['date']], $upcoming)
        );
    }

    public function testAnniversariesOfTheMonth(): void
    {
        $this->member('UnAn', '2025-11-03', 'Vétéran');
        $this->member('DeuxAns', '2024-11-28', 'Vétéran');
        $this->member('CetteAnnee', '2026-11-01', 'Explorateur');     // 0 an : exclu
        $this->member('AutreMois', '2024-10-28', 'Vétéran');
        $this->leave($this->member('Parti', '2024-11-10', 'Vétéran'), '2026-05-01');
        $this->em->flush();

        $anniversaries = $this->queries->anniversaries($this->now);

        $this->assertSame(
            [['UnAn', 1, '2026-11-03', true], ['DeuxAns', 2, '2026-11-28', false]],
            array_map(fn ($a) => [$a['pseudo'], $a['years'], $a['date'], $a['isPast']], $anniversaries)
        );
    }

    public function testLeapDayAnniversaryFallsOnFebruary28th(): void
    {
        $this->member('Bissextile', '2024-02-29', 'Vétéran');
        $this->em->flush();

        $anniversaries = $this->queries->anniversaries(new \DateTimeImmutable('2027-02-10'));

        $this->assertSame('2027-02-28', $anniversaries[0]['date']);
        $this->assertSame(3, $anniversaries[0]['years']);
    }

    public function testMyProgressTowardsTheNextSeniorityRank(): void
    {
        $me = $this->member('Moi', '2026-10-06', 'Explorateur');      // 51 jours
        $this->em->flush();

        $progress = $this->queries->myProgress($this->now, $me->getId());

        $this->assertSame(51, $progress['seniorityDays']);
        $this->assertSame(['name' => 'Explorateur', 'requiredDays' => 14], $progress['seniorityRank']);
        $this->assertSame(['name' => 'Sentinelle', 'requiredDays' => 61, 'daysLeft' => 10, 'date' => '2026-12-06'], $progress['nextRank']);
        $this->assertSame(78, $progress['progressPercent'], '(51 - 14) / (61 - 14) = 78 %');
        $this->assertFalse($progress['storedRankIsSpecial']);
    }

    public function testMyProgressForAFunctionRankAndTopRank(): void
    {
        $me = $this->member('Doyen', '2024-01-01', 'Conseiller');
        $this->em->flush();

        $progress = $this->queries->myProgress($this->now, $me->getId());

        $this->assertTrue($progress['storedRankIsSpecial']);
        $this->assertSame('Conseiller', $progress['storedRank']);
        $this->assertSame('Vétéran', $progress['seniorityRank']['name']);
        $this->assertNull($progress['nextRank']);
        $this->assertSame(100, $progress['progressPercent']);
        $this->assertNull($this->queries->myProgress($this->now, null));
    }

    // ---------------------------------------------------------------- fil

    public function testActivityMergesArrivalsPromotionsAndJournal(): void
    {
        $recruiter = $this->member('Recruteur', '2024-01-01', 'Vétéran');
        $newbie = $this->member('Nouveau', '2026-11-10', 'Explorateur', $recruiter);
        $this->em->flush();
        $this->makeMule('SaMule', $newbie);
        $this->em->flush();                                           // mule_added (date réelle)
        $newbie->setIsArchived(true);
        $this->em->flush();                                           // left
        $this->em->getConnection()->executeStatement(
            "UPDATE guild_event SET occurred_at = '2026-11-25 20:00:00' WHERE type = 'left'"
        );
        $this->em->getConnection()->executeStatement(
            "UPDATE guild_event SET occurred_at = '2026-11-20 09:00:00' WHERE type = 'mule_added'"
        );
        $newbie->setArchivedAt(new \DateTime('2026-11-25 20:00:00'));
        $this->em->flush();

        $movements = $this->queries->activity($this->now, HomeDashboardQueries::FEED_MOVEMENTS, 5);
        $promotions = $this->queries->activity($this->now, HomeDashboardQueries::FEED_PROMOTIONS, 5);

        $this->assertSame(
            [['left', 'Nouveau'], ['joined', 'Nouveau'], ['joined', 'Recruteur']],
            array_map(fn ($i) => [$i['type'], $i['pseudo']], $movements),
            'les nouvelles mules ne font pas partie des entrées / sorties'
        );
        $this->assertSame('Recruteur', $movements[1]['details']['recruiter']);

        $this->assertSame(
            [
                ['promoted', 'Nouveau', 'Explorateur'],   // J+14 = 24/11, encore membre à cette date
                ['promoted', 'Recruteur', 'Vétéran'],     // 31/12/2024
                ['promoted', 'Recruteur', 'Gardien'],
                ['promoted', 'Recruteur', 'Sentinelle'],
                ['promoted', 'Recruteur', 'Explorateur'],
            ],
            array_map(fn ($i) => [$i['type'], $i['pseudo'], $i['details']['rank']], $promotions)
        );
    }

    public function testRankChangesMadeOnTheSiteAppearWithPromotions(): void
    {
        $member = $this->member('Promu', '2026-11-20');
        $this->em->flush();
        $member->setRank($this->ranks['Enroleur']);
        $this->em->flush();
        $this->em->getConnection()->executeStatement("UPDATE guild_event SET occurred_at = '2026-11-25 10:00:00'");

        $promotions = $this->queries->activity($this->now, HomeDashboardQueries::FEED_PROMOTIONS, 5);

        $this->assertSame('rank_changed', $promotions[0]['type']);
        $this->assertSame('Enroleur', $promotions[0]['details']['to']['name']);
        $this->assertSame([], array_filter(
            $this->queries->activity($this->now, HomeDashboardQueries::FEED_MOVEMENTS, 5),
            fn ($i) => $i['type'] === 'rank_changed'
        ));
    }

    public function testActivityHidesPromotionsAfterDepartureAndCascadeMuleEvents(): void
    {
        $member = $this->member('Parti', '2026-10-01', 'Explorateur');
        $this->makeMule('Mule', $member);
        $this->em->flush();
        $member->setIsArchived(true);
        foreach ($member->getMules() as $mule) {
            $mule->setIsArchived(true);
        }
        $this->em->flush();
        $member->setArchivedAt(new \DateTime('2026-10-20'));         // parti avant J+61 (01/12)
        $this->em->flush();
        // Le journal horodate à l'heure réelle : on le recale sur la date de départ simulée.
        $this->em->getConnection()->executeStatement(
            "UPDATE guild_event SET occurred_at = '2026-10-20 00:00:00' WHERE type IN ('left', 'mule_left')"
        );

        $all = array_merge(HomeDashboardQueries::FEED_MOVEMENTS, HomeDashboardQueries::FEED_PROMOTIONS, [GuildEvent::MULE_LEFT]);
        $types = array_column($this->queries->activity($this->now, $all, 20), 'type');

        // left et mule_left ont le même horodatage : leur ordre relatif est indifférent.
        $this->assertEqualsCanonicalizing(['left', 'mule_left'], array_slice($types, 0, 2));
        $this->assertSame(['promoted', 'joined'], array_slice($types, 2), 'Explorateur (15/10) affiché, pas les rangs suivants');
        $this->assertNotContains(
            GuildEvent::MULE_LEFT,
            array_column($this->queries->activity($this->now, HomeDashboardQueries::FEED_MOVEMENTS, 20), 'type')
        );
    }

    public function testActivityPagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->member("Membre$i", sprintf('2026-11-%02d', 20 + $i));
        }
        $this->em->flush();

        $page1 = array_column($this->queries->activity($this->now, HomeDashboardQueries::FEED_MOVEMENTS, 2, 0), 'pseudo');
        $page2 = array_column($this->queries->activity($this->now, HomeDashboardQueries::FEED_MOVEMENTS, 2, 2), 'pseudo');

        $this->assertSame(['Membre5', 'Membre4'], $page1);
        $this->assertSame(['Membre3', 'Membre2'], $page2);
    }

    // ---------------------------------------------------------------- blocs admin

    public function testGhostRoundStatusAndFreshRecruits(): void
    {
        $admin = $this->makeUser('admin', ['ROLE_ADMIN']);
        $other = $this->makeUser('autre', ['ROLE_ADMIN']);
        $fresh = $this->member('Frais', '2026-11-20');
        $border = $this->member('Limite', '2026-10-28');              // 29 jours : inclus (moins de 30)
        $this->member('TropVieux', '2026-10-27');                     // 30 jours : exclu
        $closed = (new GhostRound())->setOpenedAt(new \DateTime('2026-10-01'))->setThreshold(2)->setClosedAt(new \DateTime('2026-10-31'));
        $open = (new GhostRound())->setOpenedAt(new \DateTime('2026-11-01'))->setThreshold(2);
        $this->em->persist($closed);
        $this->em->persist($open);
        foreach ([[$open, $fresh, $admin], [$open, $fresh, $other], [$open, $border, $other], [$closed, $border, $admin]] as [$round, $target, $voter]) {
            $this->em->persist((new GhostVote())->setRound($round)->setCharacter($target)->setVoter($voter));
        }
        $this->em->persist((new Warning())->setCharacter($border)->setDescription('absent'));
        $this->em->flush();

        $ghost = $this->queries->ghostRoundStatus($admin->getId());
        $this->assertSame($open->getId(), $ghost['roundId']);
        $this->assertSame(1, $ghost['myVotes']);
        $this->assertTrue($ghost['hasVoted']);
        $this->assertSame(3, $ghost['totalVotes']);
        $this->assertSame(2, $ghost['voters']);
        $this->assertSame(1, $ghost['flagged'], 'Frais atteint le seuil de 2');

        $recruits = $this->queries->freshRecruits($this->now);
        $this->assertSame(
            [['Frais', 6, 0, 2], ['Limite', 29, 1, 1]],
            array_map(fn ($r) => [$r['pseudo'], $r['days'], $r['warnings'], $r['ghostVotes']], $recruits)
        );
    }

    public function testNoOpenGhostRound(): void
    {
        $admin = $this->makeUser('admin', ['ROLE_ADMIN']);
        $this->em->flush();

        $this->assertNull($this->queries->ghostRoundStatus($admin->getId()));
    }
}
