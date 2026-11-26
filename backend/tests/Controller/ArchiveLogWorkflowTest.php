<?php

namespace App\Tests\Controller;

use App\Entity\GuildEvent;
use App\Tests\DatabaseTestCase;

/**
 * « Tri des inactifs » (archivage depuis le journal de guilde Dofus) de bout en bout :
 * l'archivage en lot doit dater les départs et alimenter le journal, puis se refléter sur
 * l'accueil et le dashboard recrutement, comme un archivage unitaire.
 */
class ArchiveLogWorkflowTest extends DatabaseTestCase
{
    public function testExecutingAGuildLogDatesDeparturesAndFeedsTheDashboards(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        $recruiter = $this->makeCharacter('Recruteur', $this->makeRank('Enroleur', null, false, true), '-900 days');
        $leaver = $this->makeCharacter('Red-Huissier', $rank, '-30 days', $recruiter);
        $this->makeMule('MuleDeRed', $leaver);
        $muleOnly = $this->makeCharacter('Fidele', $rank, '-300 days');
        $mule = $this->makeMule('Worldeater', $muleOnly);
        $this->makeCharacter('Reste', $rank, '-30 days', $recruiter);
        $this->em->flush();
        $owner = $this->makeUser('meneur', ['ROLE_OWNERS']);
        $this->em->flush();

        $log = "[20:39] Red-Huissier ne fait plus partie de la guilde.\n[20:40] Worldeater ne fait plus partie de la guilde.";

        // L'analyse n'écrit rien.
        $this->requestAs($owner, 'POST', '/archive-log/analyze', ['log' => $log]);
        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->events());

        $result = $this->requestAs($owner, 'POST', '/archive-log/execute', ['log' => $log]);
        $this->assertResponseIsSuccessful();

        $db = $this->em->getConnection();
        $this->assertNotNull($db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$leaver->getId()]));
        $this->assertNotNull($db->fetchOne('SELECT archived_at FROM mules WHERE pseudo = ?', ['MuleDeRed']), 'mule archivée en cascade, datée');
        $this->assertNotNull($db->fetchOne('SELECT archived_at FROM mules WHERE id = ?', [$mule->getId()]));
        $this->assertNull($db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$muleOnly->getId()]), 'le main d\'une mule partie reste');

        $left = $this->events(GuildEvent::LEFT);
        $this->assertCount(1, $left);
        $this->assertSame('Red-Huissier', $left[0]['character_pseudo']);
        $this->assertSame($owner->getId(), $left[0]['actor_user_id']);
        $this->assertSame(30, $left[0]['payload']['daysInGuild']);
        $this->assertSame('Recruteur', $left[0]['payload']['recruiter']);
        $muleEvents = array_column($this->events(GuildEvent::MULE_LEFT), 'payload', 'mule_pseudo');
        $this->assertTrue($muleEvents['MuleDeRed']['withMain']);
        $this->assertFalse($muleEvents['Worldeater']['withMain'], 'mule partie seule');

        // Rejouer le même journal ne crée ni doublon ni nouvelle date.
        $firstDate = $db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$leaver->getId()]);
        $this->requestAs($owner, 'POST', '/archive-log/execute', ['log' => $log]);
        $this->assertCount(1, $this->events(GuildEvent::LEFT));
        $this->assertSame($firstDate, $db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$leaver->getId()]));

        // Accueil : départ dans les mouvements, sortie comptée.
        $home = $this->requestAs($owner, 'GET', '/dashboard/home');
        $this->assertSame('left', $home['movements'][0]['type']);
        $this->assertSame(1, $home['counters']['flow30']['exits']);

        // Dashboard recrutement : départ de la semaine et répartition du temps avant départ.
        $recruitment = $this->requestAs($owner, 'GET', '/recruitment/dashboard');
        $this->assertSame(1, $recruitment['week']['departures']);
        $this->assertSame(1, $recruitment['departures']['buckets'][2]['count'], '30 j → tranche 15-30 j');
        $this->assertSame(1, $recruitment['recruiters'][0]['stillHere']);
    }
}
