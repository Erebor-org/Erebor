<?php

namespace App\Tests\Controller;

use App\Entity\GhostRound;
use App\Entity\GhostVote;
use App\Entity\GuildEvent;
use App\Entity\Warning;
use App\Tests\DatabaseTestCase;

/**
 * Le changement de main supprime l'ancien personnage : tout ce qui y est rattaché doit être
 * transféré avant, sinon les ON DELETE CASCADE / SET NULL effacent l'historique.
 */
class SwitchMainTest extends DatabaseTestCase
{
    public function testSwitchTransfersTheWholeHistoryToTheNewMain(): void
    {
        $classic = $this->makeRank('Sentinelle', 61); // pas un rang recruteur
        $oldMain = $this->makeCharacter('AncienMain', $classic, '-200 days');
        $mule = $this->makeMule('FuturMain', $oldMain);
        $otherMule = $this->makeMule('AutreMule', $oldMain);
        $recruit = $this->makeCharacter('Recrue', $classic, '-10 days', $oldMain);
        $this->em->flush();

        $owner = $this->makeUser('proprio', ['ROLE_ADMIN'], $oldMain);
        $voter = $this->makeUser('votant', ['ROLE_ADMIN']);
        $targetWarning = (new Warning())->setCharacter($oldMain)->setDescription('visé');
        $authoredWarning = (new Warning())->setCharacter($recruit)->setDescription('rédigé')->setAuthorCharacter($oldMain);
        $round = (new GhostRound())->setOpenedAt(new \DateTime())->setThreshold(3);
        $vote = (new GhostVote())->setRound($round)->setCharacter($oldMain)->setVoter($voter);
        foreach ([$targetWarning, $authoredWarning, $round, $vote] as $entity) {
            $this->em->persist($entity);
        }
        $this->em->flush();

        $oldMain->setRank($this->makeRank('Gardien', 122)); // un événement rattaché à l'ancien main
        $this->em->flush();
        $oldId = $oldMain->getId();

        $response = $this->requestAs($owner, 'POST', "/characters/$oldId/switch-with-mule/{$mule->getId()}");
        $this->assertResponseIsSuccessful();
        $newId = $response['newMain']['id'];

        $db = $this->em->getConnection();
        $this->assertFalse($db->fetchOne('SELECT 1 FROM characters WHERE id = ?', [$oldId]));
        $this->assertSame($newId, $db->fetchOne('SELECT recruiter_id FROM characters WHERE id = ?', [$recruit->getId()]),
            'les recrues suivent même si l\'ancien main n\'a pas de rang recruteur');
        $this->assertSame($newId, $db->fetchOne('SELECT character_id FROM warnings WHERE id = ?', [$targetWarning->getId()]));
        $this->assertSame($newId, $db->fetchOne('SELECT author_character_id FROM warnings WHERE id = ?', [$authoredWarning->getId()]));
        $this->assertSame($newId, $db->fetchOne('SELECT character_id FROM ghost_vote WHERE id = ?', [$vote->getId()]));
        $this->assertSame($newId, $db->fetchOne('SELECT character_id FROM "user" WHERE id = ?', [$owner->getId()]));
        $this->assertSame($newId, $db->fetchOne('SELECT main_character_id FROM mules WHERE id = ?', [$otherMule->getId()]));

        $rankEvent = $this->events(GuildEvent::RANK_CHANGED)[0];
        $this->assertSame($newId, $rankEvent['character_id'], 'le journal suit le membre');

        // Un seul événement pour le switch, pas de bruit (suppression, nouvelle mule…).
        $types = array_column($this->events(), 'type');
        $this->assertSame([GuildEvent::RANK_CHANGED, GuildEvent::MAIN_SWITCHED], $types);
        $switch = $this->events(GuildEvent::MAIN_SWITCHED)[0];
        $this->assertSame('AncienMain', $switch['payload']['oldMain']);
        $this->assertSame('FuturMain', $switch['payload']['newMain']);
        $this->assertSame($owner->getId(), $switch['actor_user_id']);

        // L'ancien main devenu mule n'est pas une « nouvelle mule ».
        $newMuleCreatedAt = $db->fetchOne('SELECT created_at FROM mules WHERE id = ?', [$response['newMule']['id']]);
        $this->assertSame($oldMain->getRecruitedAt()->format('Y-m-d'), substr($newMuleCreatedAt, 0, 10));
    }

    public function testSwitchOfAnArchivedMemberKeepsTheOriginalDepartureDate(): void
    {
        $rank = $this->makeRank('Néophyte', 1);
        $oldMain = $this->makeCharacter('Parti', $rank, '-100 days');
        $mule = $this->makeMule('SaMule', $oldMain);
        $this->em->flush();
        $oldMain->setIsArchived(true);
        $mule->setIsArchived(true);
        $this->em->flush();
        $departure = new \DateTime('2026-03-01 12:00:00');
        $this->em->getConnection()->executeStatement(
            'UPDATE characters SET archived_at = :d WHERE id = :id; ',
            ['d' => $departure->format('Y-m-d H:i:s'), 'id' => $oldMain->getId()]
        );
        $this->em->getConnection()->executeStatement('UPDATE mules SET archived_at = NULL WHERE id = ?', [$mule->getId()]);
        $this->em->clear();
        $admin = $this->makeUser('admin', ['ROLE_ADMIN']);
        $this->em->flush();

        $response = $this->requestAs($admin, 'POST', "/characters/{$oldMain->getId()}/switch-with-mule/{$mule->getId()}");
        $this->assertResponseIsSuccessful();

        $db = $this->em->getConnection();
        $this->assertSame('2026-03-01 12:00:00', $db->fetchOne('SELECT archived_at FROM characters WHERE id = ?', [$response['newMain']['id']]));
        $this->assertSame('2026-03-01 12:00:00', $db->fetchOne('SELECT archived_at FROM mules WHERE id = ?', [$response['newMule']['id']]));
    }
}
