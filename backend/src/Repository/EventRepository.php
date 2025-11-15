<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Find all events ordered by date (upcoming first, then past)
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming events (date >= today)
     */
    public function findUpcoming(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('e')
            ->where('e.date >= :now')
            ->andWhere('e.isFinished = false')
            ->setParameter('now', $now)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find past events (date < today or isFinished = true)
     */
    public function findPast(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('e')
            ->where('e.date < :now OR e.isFinished = true')
            ->setParameter('now', $now)
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by owner
     */
    public function findByOwner(int $ownerId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.ownerId = :ownerId')
            ->setParameter('ownerId', $ownerId)
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}





