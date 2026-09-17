<?php

namespace App\Repository;

use App\Entity\Characters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Characters>
 */
class CharactersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Characters::class);
    }

    public function save(Characters $character, bool $flush = false): void
    {
        $this->getEntityManager()->persist($character);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Characters $character, bool $flush = false): void
    {
        $this->getEntityManager()->remove($character);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findCharactersWithRecruiters(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.recruiter', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getResult();
    }

    /**
     * Batch lookup by pseudo, case-insensitive. Returns all matching Characters in one query.
     *
     * @param string[] $pseudos
     * @return Characters[]
     */
    public function findByPseudosCaseInsensitive(array $pseudos): array
    {
        if (empty($pseudos)) {
            return [];
        }

        $lowered = array_map('mb_strtolower', $pseudos);

        return $this->createQueryBuilder('c')
            ->where('LOWER(c.pseudo) IN (:pseudos)')
            ->setParameter('pseudos', $lowered)
            ->getQuery()
            ->getResult();
    }
}
