<?php

namespace App\Tests\EventListener;

use App\Entity\GuildEvent;
use App\Entity\Warning;
use App\EventListener\GuildEventListener;
use App\Tests\DatabaseTestCase;

/**
 * Le journal et les dates de départ doivent être justes quel que soit le chemin d'écriture :
 * flush direct (archive-log, cascade) ou endpoint HTTP.
 */
class GuildEventListenerTest extends DatabaseTestCase
{
    public function testArchivingRecordsDepartureWithDateAndContext(): void
    {
        $rank = $this->makeRank('Explorateur', 14);
        $recruiter = $this->makeCharacter('Recruteur', $rank, '-400 days');
        $member = $this->makeCharacter('Partant', $rank, '-20 days', $recruiter);
        $this->em->flush();

        $member->setIsArchived(true);
        $this->em->flush();

        $archivedAt = $this->em->getConnection()->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$member->getId()]);
        $this->assertNotNull($archivedAt);

        $events = $this->events(GuildEvent::LEFT);
        $this->assertCount(1, $events);
        $this->assertSame($member->getId(), $events[0]['character_id']);
        $this->assertSame('Partant', $events[0]['character_pseudo']);
        $this->assertSame(20, $events[0]['payload']['daysInGuild']);
        $this->assertSame('Recruteur', $events[0]['payload']['recruiter']);
        $this->assertSame('Explorateur', $events[0]['payload']['rank']);
    }

    public function testSavingWithoutStateChangeRecordsNothing(): void
    {
        $member = $this->makeCharacter('Stable', $this->makeRank('Néophyte', 1));
        $this->em->flush();

        $member->setIsArchived(false)->setNotes('rien à signaler');
        $this->em->flush();

        $this->assertSame([], $this->events());
    }

    public function testReturnClearsDepartureDateAndRecordsReturn(): void
    {
        $member = $this->makeCharacter('Revenant', $this->makeRank('Néophyte', 1));
        $this->em->flush();
        $member->setIsArchived(true);
        $this->em->flush();

        $member->setIsArchived(false);
        $this->em->flush();

        $this->assertNull($this->em->getConnection()->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$member->getId()]));
        $this->assertSame([GuildEvent::LEFT, GuildEvent::RETURNED], array_column($this->events(), 'type'));
    }

    public function testRankChangeRecordsFromAndTo(): void
    {
        $neophyte = $this->makeRank('Néophyte', 1);
        $enroleur = $this->makeRank('Enroleur', null, true, true);
        $member = $this->makeCharacter('Promu', $neophyte);
        $this->em->flush();

        $member->setRank($enroleur);
        $this->em->flush();

        $events = $this->events(GuildEvent::RANK_CHANGED);
        $this->assertCount(1, $events);
        $this->assertSame('Néophyte', $events[0]['payload']['from']['name']);
        $this->assertSame('Enroleur', $events[0]['payload']['to']['name']);
    }

    public function testNewMuleOfExistingMemberIsRecordedButNotMulesDeclaredOnArrival(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        $newcomer = $this->makeCharacter('Arrivant', $rank);
        $this->makeMule('MuleArrivee', $newcomer);
        $this->em->flush();
        $this->assertSame([], $this->events(), 'les mules déclarées à l\'arrivée ne sont pas des « nouvelles mules »');

        $mule = $this->makeMule('NouvelleMule', $newcomer);
        $this->em->flush();

        $events = $this->events(GuildEvent::MULE_ADDED);
        $this->assertCount(1, $events);
        $this->assertSame($mule->getId(), $events[0]['mule_id']);
        $this->assertSame('NouvelleMule', $events[0]['mule_pseudo']);
        $this->assertSame('Arrivant', $events[0]['character_pseudo']);
        $this->assertNotNull($this->em->getConnection()->fetchOne('SELECT created_at FROM mules WHERE id = ?', [$mule->getId()]));
    }

    public function testMulesArchivedWithTheirMainAreFlaggedAsCascade(): void
    {
        $member = $this->makeCharacter('Main', $this->makeRank('Néophyte', 1));
        $mule = $this->makeMule('SaMule', $member);
        $this->em->flush();

        // Ce que font PUT /characters/{id}/archive et archive-log.
        $member->setIsArchived(true);
        $mule->setIsArchived(true);
        $this->em->flush();

        $muleEvents = $this->events(GuildEvent::MULE_LEFT);
        $this->assertCount(1, $muleEvents);
        $this->assertTrue($muleEvents[0]['payload']['withMain']);
        $this->assertNotNull($this->em->getConnection()->fetchOne('SELECT archived_at FROM mules WHERE id = ?', [$mule->getId()]));
    }

    public function testDeletingACharacterKeepsAReadableEventAndUnlinksAuthoredWarnings(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        $author = $this->makeCharacter('Auteur', $rank);
        $target = $this->makeCharacter('Cible', $rank);
        $warning = (new Warning())->setCharacter($target)->setDescription('test')->setAuthorCharacter($author);
        $this->em->persist($warning);
        $this->em->flush();
        $authorId = $author->getId();

        // Échouait avant : author_character_id n'avait pas de ON DELETE.
        $this->em->remove($author);
        $this->em->flush();

        $events = $this->events(GuildEvent::CHARACTER_DELETED);
        $this->assertCount(1, $events);
        $this->assertNull($events[0]['character_id']);
        $this->assertSame('Auteur', $events[0]['character_pseudo']);
        $this->assertSame($authorId, $events[0]['payload']['characterId']);
        $this->assertNull($this->em->getConnection()->fetchOne('SELECT author_character_id FROM warnings WHERE id = ?', [$warning->getId()]));
    }

    public function testMutedOperationsRecordNothing(): void
    {
        $member = $this->makeCharacter('Silencieux', $this->makeRank('Néophyte', 1));
        $this->em->flush();

        static::getContainer()->get(GuildEventListener::class)->withoutRecording(function () use ($member) {
            $member->setIsArchived(true);
            $this->em->flush();
        });

        $this->assertSame([], $this->events());
    }

    public function testArchiveEndpointRecordsTheActingUser(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        $member = $this->makeCharacter('ViaApi', $rank);
        $admin = $this->makeUser('enroleur', ['ROLE_ADMIN']);
        $this->em->flush();

        $this->requestAs($admin, 'PUT', '/characters/' . $member->getId() . '/archive', ['isArchived' => true]);
        $this->assertResponseIsSuccessful();

        $events = $this->events(GuildEvent::LEFT);
        $this->assertCount(1, $events);
        $this->assertSame($admin->getId(), $events[0]['actor_user_id']);
    }
}
