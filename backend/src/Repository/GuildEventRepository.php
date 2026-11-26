<?php

namespace App\Repository;

use App\Entity\GuildEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GuildEvent>
 */
class GuildEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuildEvent::class);
    }

    /**
     * Repointe l'historique d'un personnage vers un autre (changement de main), en SQL direct
     * pour ne pas réveiller le listener.
     */
    public function reassignCharacter(int $fromCharacterId, int $toCharacterId): int
    {
        return $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE guild_event SET character_id = :to WHERE character_id = :from',
            ['to' => $toCharacterId, 'from' => $fromCharacterId]
        );
    }
}
