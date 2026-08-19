<?php

namespace App\Repository;

use App\Entity\BlacklistCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlacklistCharacter>
 *
 * @method BlacklistCharacter|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlacklistCharacter|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlacklistCharacter[]    findAll()
 * @method BlacklistCharacter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlacklistCharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlacklistCharacter::class);
    }

    public function save(BlacklistCharacter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlacklistCharacter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
