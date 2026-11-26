<?php

namespace App\EventListener;

use App\Entity\Characters;
use App\Entity\GuildEvent;
use App\Entity\Mule;
use App\Entity\Ranks;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Alimente guild_event à chaque flush, quel que soit l'endpoint à l'origine du changement
 * (archivage unitaire, archivage depuis le log, cascade sur les mules, édition, rangs de
 * fonction…). Ainsi aucun contrôleur n'a à penser au journal.
 *
 * Journalisé : départs / réintégrations (personnages et mules), changements de rang faits sur
 * le site, nouvelles mules d'un membre existant, suppressions de personnages.
 * Non journalisé : arrivées et montées d'ancienneté (déduites de recruited_at + ranks), mules
 * créées en même temps que leur main, entités créées déjà archivées.
 *
 * La date d'archivage elle-même est tenue par Characters/Mule::setIsArchived().
 */
#[AsDoctrineListener(event: Events::onFlush)]
class GuildEventListener
{
    private bool $muted = false;

    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Exécute $operation sans rien journaliser, pour les opérations qui écrivent elles-mêmes
     * un événement plus parlant (ex. changement de main = suppression + création techniques).
     */
    public function withoutRecording(callable $operation): mixed
    {
        $previous = $this->muted;
        $this->muted = true;
        try {
            return $operation();
        } finally {
            $this->muted = $previous;
        }
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if ($this->muted) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $events = [];

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Mule && !$entity->isArchived()) {
                $main = $entity->getMainCharacter();
                // Mules déclarées à l'arrivée du membre : l'arrivée suffit dans le fil.
                if ($main && !$uow->isScheduledForInsert($main)) {
                    $events[] = (new GuildEvent(GuildEvent::MULE_ADDED, $main))
                        ->setMule($entity)
                        ->setPayload(['class' => $entity->getClass()]);
                }
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $changes = $uow->getEntityChangeSet($entity);

            if ($entity instanceof Characters) {
                if (isset($changes['isArchived']) && $changes['isArchived'][0] !== $changes['isArchived'][1]) {
                    $events[] = $entity->isArchived()
                        ? (new GuildEvent(GuildEvent::LEFT, $entity))->setPayload($this->departurePayload($entity))
                        : (new GuildEvent(GuildEvent::RETURNED, $entity))->setPayload(['rank' => $entity->getRank()->getName()]);
                }
                if (isset($changes['rank']) && $changes['rank'][0] !== $changes['rank'][1]) {
                    $events[] = (new GuildEvent(GuildEvent::RANK_CHANGED, $entity))->setPayload([
                        'from' => $this->rankPayload($changes['rank'][0]),
                        'to' => $this->rankPayload($changes['rank'][1]),
                    ]);
                }
            }

            if ($entity instanceof Mule && $entity->getMainCharacter()
                && isset($changes['isArchived']) && $changes['isArchived'][0] !== $changes['isArchived'][1]) {
                $main = $entity->getMainCharacter();
                $mainChanges = $uow->isScheduledForUpdate($main) ? $uow->getEntityChangeSet($main) : [];
                $events[] = (new GuildEvent($entity->isArchived() ? GuildEvent::MULE_LEFT : GuildEvent::MULE_RETURNED, $main))
                    ->setMule($entity)
                    // Mule entraînée par le départ / retour de son main dans le même flush.
                    ->setPayload(['withMain' => isset($mainChanges['isArchived'])]);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Characters) {
                $events[] = (new GuildEvent(GuildEvent::CHARACTER_DELETED, $entity))
                    ->detachCharacter()
                    ->setPayload(['characterId' => $entity->getId(), 'wasArchived' => $entity->isArchived()]);
            }
        }

        if ($events === []) {
            return;
        }

        $actor = $this->resolveActor($em);
        $metadata = $em->getClassMetadata(GuildEvent::class);
        foreach ($events as $event) {
            $event->setActor($actor);
            $em->persist($event);
            $uow->computeChangeSet($metadata, $event);
        }
    }

    private function departurePayload(Characters $character): array
    {
        $recruitedAt = $character->getRecruitedAt();
        $leftAt = $character->getArchivedAt() ?? new \DateTime();

        return [
            'class' => $character->getClass(),
            'rank' => $character->getRank()->getName(),
            'recruitedAt' => $recruitedAt->format('Y-m-d'),
            'daysInGuild' => (int) $recruitedAt->diff($leftAt)->days,
            'recruiter' => $character->getRecruiter()?->getPseudo(),
        ];
    }

    private function rankPayload(?Ranks $rank): ?array
    {
        return $rank ? ['id' => $rank->getId(), 'name' => $rank->getName()] : null;
    }

    /**
     * Utilisateur connecté, rattaché à l'EntityManager courant : après un $em->clear() l'objet
     * du token est détaché et ne peut pas être référencé tel quel.
     */
    public function resolveActor(EntityManagerInterface $em): ?User
    {
        $user = $this->security->getUser();
        if (!$user instanceof User || $user->getId() === null) {
            return null;
        }
        if ($em->contains($user)) {
            return $user;
        }
        return $em->getReference(User::class, $user->getId());
    }
}
