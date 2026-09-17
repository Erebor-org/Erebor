<?php

namespace App\Repository;

use App\Entity\Mule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mule::class);
    }

    /**
     * Batch lookup by pseudo, case-insensitive. Returns all matching Mules in one query,
     * with their main character eagerly loaded to avoid N+1 lookups when displaying results.
     *
     * @param string[] $pseudos
     * @return Mule[]
     */
    public function findByPseudosCaseInsensitive(array $pseudos): array
    {
        if (empty($pseudos)) {
            return [];
        }

        $lowered = array_map('mb_strtolower', $pseudos);

        return $this->createQueryBuilder('m')
            ->leftJoin('m.mainCharacter', 'mc')
            ->addSelect('mc')
            ->where('LOWER(m.pseudo) IN (:pseudos)')
            ->setParameter('pseudos', $lowered)
            ->getQuery()
            ->getResult();
    }
}
