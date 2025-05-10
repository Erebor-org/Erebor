<?php

namespace App\Repository;

use App\Entity\Characters;
use App\Entity\Event;
use App\Entity\EventParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventParticipation>
 *
 * @method EventParticipation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventParticipation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventParticipation[]    findAll()
 * @method EventParticipation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventParticipation::class);
    }

    /**
     * Find all participations for a specific event ordered by position
     */
    public function findByEventOrderedByPosition(Event $event): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.event = :event')
            ->setParameter('event', $event)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all participations for a specific character
     */
    public function findByCharacter(Characters $character): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.character = :character')
            ->setParameter('character', $character)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get ladder standings (total points per character)
     */
    public function getLadderStandings(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                c.id as character_id,
                c.pseudo as character_name,
                c.class as character_class,
                SUM(ep.points) as total_points,
                COUNT(ep.id) as participation_count,
                MIN(ep.position) as best_position
            FROM event_participation ep
            JOIN characters c ON c.id = ep.character_id
            WHERE c.is_archived = false
            GROUP BY c.id, c.pseudo, c.class
            ORDER BY total_points DESC
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        
        return $result->fetchAllAssociative();
    }

    /**
     * Get character's best result
     */
    public function getCharacterBestResult(Characters $character): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.character = :character')
            ->setParameter('character', $character)
            ->orderBy('p.points', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get total points for a character
     */
    public function getTotalPointsForCharacter(Characters $character): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.points) as total_points')
            ->andWhere('p.character = :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getSingleScalarResult();
            
        return (int)$result;
    }
}
