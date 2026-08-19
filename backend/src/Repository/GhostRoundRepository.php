<?php

namespace App\Repository;

use App\Entity\GhostRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GhostRound|null find($id, $lockMode = null, $lockVersion = null)
 * @method GhostRound|null findOneBy(array $criteria, array $orderBy = null)
 * @method GhostRound[]    findAll()
 * @method GhostRound[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GhostRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GhostRound::class);
    }

    /**
     * There should only ever be one round with no closedAt at a time.
     */
    public function findOpenRound(): ?GhostRound
    {
        return $this->findOneBy(['closedAt' => null]);
    }

    /**
     * Closed rounds, most recent first.
     */
    public function findClosedRounds(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.closedAt IS NOT NULL')
            ->orderBy('r.closedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function remove(GhostRound $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function add(GhostRound $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
