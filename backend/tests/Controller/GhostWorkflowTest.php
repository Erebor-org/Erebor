<?php

namespace App\Tests\Controller;

use App\Command\DemoMovementsCommand;
use App\Entity\Characters;
use App\Entity\GuildEvent;
use App\Entity\User;
use App\Tests\DatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tri des inactifs (votes fantômes) de bout en bout, par l'API, avec tout ce qui s'y est
 * greffé : date de départ et journal à l'exclusion, accueil (statut de manche, recrues,
 * entrées / sorties), changement de main et données de démonstration.
 */
class GhostWorkflowTest extends DatabaseTestCase
{
    private User $owner;
    /** @var list<User> */
    private array $voters = [];
    private Characters $ghost;
    private Characters $fresh;

    protected function setUp(): void
    {
        parent::setUp();
        $rank = $this->makeRank('Néophyte', 1);
        $this->makeRank('Gardien', 122);
        $this->ghost = $this->makeCharacter('Fantome', $rank, '-200 days');
        $this->makeMule('MuleDuFantome', $this->ghost);
        $this->fresh = $this->makeCharacter('Recrue', $rank, '-3 days');
        $this->em->flush();

        $this->owner = $this->makeUser('meneur', ['ROLE_OWNERS']);
        for ($i = 1; $i <= 4; $i++) {
            $this->voters[] = $this->makeUser("enroleur$i", ['ROLE_ADMIN']);
        }
        $this->em->flush();
    }

    private function voteRound(Characters $target, int $voterCount): void
    {
        foreach (array_slice($this->voters, 0, $voterCount) as $voter) {
            $this->requestAs($voter, 'POST', "/ghost/characters/{$target->getId()}/vote");
            $this->assertResponseIsSuccessful();
        }
    }

    public function testFullCycleFromVotesToExclusion(): void
    {
        // Manche 1 : le seuil (4) est atteint, une recrue reçoit aussi un vote.
        $current = $this->requestAs($this->owner, 'GET', '/ghost/current');
        $this->assertSame(4, $current['round']['threshold']);
        $this->voteRound($this->ghost, 4);
        $this->voteRound($this->fresh, 1);

        $home = $this->requestAs($this->voters[0], 'GET', '/dashboard/home');
        $this->assertSame(2, $home['admin']['ghost']['myVotes']);
        $this->assertTrue($home['admin']['ghost']['hasVoted']);
        $this->assertSame(4, $home['admin']['ghost']['voters']);
        $this->assertSame(1, $home['admin']['ghost']['flagged']);
        $recrue = array_values(array_filter($home['admin']['freshRecruits'], fn ($r) => $r['pseudo'] === 'Recrue'))[0];
        $this->assertSame(1, $recrue['ghostVotes'], 'la recrue signalée apparaît avec son vote');

        $closed = $this->requestAs($this->owner, 'POST', '/ghost/close');
        $this->assertResponseIsSuccessful();
        $this->assertSame(['Fantome'], array_column($closed['reachedThreshold'], 'pseudo'));

        // Nouvelle manche : le statut de l'accueil repart de zéro.
        $home = $this->requestAs($this->voters[0], 'GET', '/dashboard/home');
        $this->assertSame($closed['newRound']['id'], $home['admin']['ghost']['roundId']);
        $this->assertSame(0, $home['admin']['ghost']['totalVotes']);
        $this->assertFalse($home['admin']['ghost']['hasVoted']);

        // Manche 2 : second signalement → éligible à l'exclusion.
        $this->voteRound($this->ghost, 4);
        $this->requestAs($this->owner, 'POST', '/ghost/close');
        $this->voteRound($this->ghost, 2); // manche 3 en cours
        $registry = $this->requestAs($this->owner, 'GET', '/ghost/registry');
        $entry = array_values(array_filter($registry, fn ($e) => $e['pseudo'] === 'Fantome'))[0];
        $this->assertTrue($entry['eligibleExclusion']);
        $this->assertSame(2, $entry['timesThresholdReached']);

        // Exclusion depuis la page Fantômes (même appel que le bouton « Archiver »).
        $this->requestAs($this->owner, 'PUT', "/characters/{$this->ghost->getId()}/archive", ['isArchived' => true]);
        $this->assertResponseIsSuccessful();

        $db = $this->em->getConnection();
        $this->assertNotNull($db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$this->ghost->getId()]), 'date de départ posée');
        $this->assertNotNull($db->fetchOne('SELECT archived_at FROM mules WHERE main_character_id = ?', [$this->ghost->getId()]));

        $left = $this->events(GuildEvent::LEFT);
        $this->assertCount(1, $left);
        $this->assertSame($this->owner->getId(), $left[0]['actor_user_id']);
        $this->assertSame(200, $left[0]['payload']['daysInGuild']);
        $this->assertTrue($this->events(GuildEvent::MULE_LEFT)[0]['payload']['withMain']);

        // L'exclu disparaît de la manche en cours, ne peut plus être signalé, reste au registre.
        $current = $this->requestAs($this->owner, 'GET', '/ghost/current');
        $this->assertNotContains('Fantome', array_column($current['nominees'], 'pseudo'));
        $this->requestAs($this->voters[3], 'POST', "/ghost/characters/{$this->ghost->getId()}/vote");
        $this->assertResponseStatusCodeSame(400);
        $registry = $this->requestAs($this->owner, 'GET', '/ghost/registry');
        $this->assertTrue(array_values(array_filter($registry, fn ($e) => $e['pseudo'] === 'Fantome'))[0]['isArchived']);

        // Accueil : même vision que la page Fantômes, et le départ est dans les mouvements.
        $home = $this->requestAs($this->voters[0], 'GET', '/dashboard/home');
        $this->assertSame(0, $home['admin']['ghost']['totalVotes'], 'votes sur un exclu non comptés');
        $this->assertTrue($home['admin']['ghost']['hasVoted'], 'mais ma participation reste');
        $this->assertSame('left', $home['movements'][0]['type']);
        $this->assertSame('Fantome', $home['movements'][0]['pseudo']);
        $this->assertSame(1, $home['counters']['flow30']['exits']);
        $this->assertSame(1, $home['counters']['active']['value']);
    }

    public function testSwitchingTheMainOfANomineeKeepsItsGhostHistory(): void
    {
        $this->voteRound($this->ghost, 4);
        $this->requestAs($this->owner, 'POST', '/ghost/close');
        $this->voteRound($this->ghost, 3);

        $mule = $this->ghost->getMules()->first();
        $switch = $this->requestAs($this->owner, 'POST', "/characters/{$this->ghost->getId()}/switch-with-mule/{$mule->getId()}");
        $this->assertResponseIsSuccessful();
        $newId = $switch['newMain']['id'];

        $history = $this->requestAs($this->owner, 'GET', "/ghost/characters/$newId/history");
        $this->assertSame(1, $history['timesThresholdReached'], 'le signalement de la manche close suit le membre');

        $current = $this->requestAs($this->owner, 'GET', '/ghost/current');
        $nominee = array_values(array_filter($current['nominees'], fn ($n) => $n['id'] === $newId))[0];
        $this->assertSame('MuleDuFantome', $nominee['pseudo']);
        $this->assertSame(3, $nominee['voteCount'], 'les votes de la manche en cours suivent aussi');
    }

    public function testDemoPurgeLeavesRealGhostVotesUntouched(): void
    {
        $this->makeCharacter('Recruteur', $this->makeRank('Enroleur', null, false, true), '-900 days');
        $this->em->flush();
        $this->voteRound($this->ghost, 2);

        $tester = new CommandTester((new Application(static::$kernel))->find('app:demo:movements'));
        $tester->execute(['--months' => '3']);
        $tester->execute(['--purge' => true]);

        $this->assertSame(2, (int) $this->em->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM ghost_vote WHERE character_id = ?',
            [$this->ghost->getId()]
        ));
        $this->assertSame(0, (int) $this->em->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM characters WHERE notes = ?',
            [DemoMovementsCommand::MARKER]
        ));
    }
}
