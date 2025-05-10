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

    public function findByEventOrderedByPosition(Event $event): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.event = :event')
            ->setParameter('event', $event)
            ->leftJoin('p.character', 'c')
            ->addSelect('c')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCharacter(Characters $character): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.character = :character')
            ->setParameter('character', $character)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

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
            AND c.class IS NOT NULL
            GROUP BY c.id, c.pseudo, c.class
            ORDER BY total_points DESC
        ';
        
        dump($sql);
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        dump($result);
        
        return $result->fetchAllAssociative();
    }
}
