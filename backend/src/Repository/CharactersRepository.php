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
        $this->_em->persist($character);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Characters $character, bool $flush = false): void
    {
        $this->_em->remove($character);
        if ($flush) {
            $this->_em->flush();
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
}
