<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.eventDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.eventDate > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.eventDate <= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.eventDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompletedEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isCompleted = :completed')
            ->setParameter('completed', true)
            ->orderBy('e.eventDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
