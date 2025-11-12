<?php

namespace App\Repository;

use App\Entity\EventParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventParticipant>
 */
class EventParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventParticipant::class);
    }

    /**
     * Find participants by event ID, ordered by rank
     */
    public function findByEventOrderedByRank(int $eventId): array
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('ep.rank', 'ASC')
            ->addOrderBy('ep.subscribedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participant by event and user
     */
    public function findByEventAndUser(int $eventId, int $userId): ?EventParticipant
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.event = :eventId')
            ->andWhere('ep.userId = :userId')
            ->setParameter('eventId', $eventId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get total points for a user across all events
     */
    public function getTotalPointsForUser(int $userId): float
    {
        $result = $this->createQueryBuilder('ep')
            ->select('SUM(ep.pointsEarned) as total')
            ->where('ep.userId = :userId')
            ->andWhere('ep.pointsEarned IS NOT NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Get leaderboard with weighted points (points weighted by number of participants)
     */
    public function getLeaderboard(): array
    {
        // This query gets all users with their total points
        // Points are already stored as weighted values
        $qb = $this->createQueryBuilder('ep')
            ->select('ep.userId as userId')
            ->addSelect('SUM(ep.pointsEarned) as totalPoints')
            ->addSelect('COUNT(DISTINCT ep.event) as eventsCount')
            ->where('ep.pointsEarned IS NOT NULL')
            ->groupBy('ep.userId')
            ->orderBy('totalPoints', 'DESC');

        return $qb->getQuery()->getResult();
    }
}




