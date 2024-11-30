<?php

namespace App\Repository;

use App\Entity\Ranks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ranks>
 */
class RanksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ranks::class);
    }

    public function save(Ranks $rank, bool $flush = false): void
    {
        $this->_em->persist($rank);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Ranks $rank, bool $flush = false): void
    {
        $this->_em->remove($rank);
        if ($flush) {
            $this->_em->flush();
        }
    }
    public function findByLead(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.lead = :lead')
            ->setParameter('lead', true)
            ->getQuery()
            ->getResult();
    }

    public function findByRecruiter(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.recruiter = :recruiter')
            ->setParameter('recruiter', true)
            ->getQuery()
            ->getResult();

    }
        
}
