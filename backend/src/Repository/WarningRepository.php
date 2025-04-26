<?php

namespace App\Repository;

use App\Entity\Warning;
use App\Entity\Characters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Warning|null find($id, $lockMode = null, $lockVersion = null)
 * @method Warning|null findOneBy(array $criteria, array $orderBy = null)
 * @method Warning[]    findAll()
 * @method Warning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warning::class);
    }

    /**
     * Find all warnings for a specific character
     */
    public function findByCharacter(Characters $character)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.character = :character')
            ->setParameter('character', $character)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function remove(Warning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function add(Warning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
