<?php

namespace App\Repository;

use App\Entity\Blacklist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blacklist>
 *
 * @method Blacklist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blacklist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blacklist[]    findAll()
 * @method Blacklist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlacklistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blacklist::class);
    }

    public function save(Blacklist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Blacklist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}