<?php

namespace App\Repository;

use App\Entity\Gw2LegendaryTracking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gw2LegendaryTracking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gw2LegendaryTracking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gw2LegendaryTracking[]    findAll()
 * @method Gw2LegendaryTracking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Gw2LegendaryTrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gw2LegendaryTracking::class);
    }

    /** @return int[] item ids the user follows, oldest first */
    public function findItemIdsForUser(User $user): array
    {
        return array_map(
            fn (Gw2LegendaryTracking $t) => $t->getItemId(),
            $this->findBy(['user' => $user], ['createdAt' => 'ASC', 'id' => 'ASC']),
        );
    }
}
