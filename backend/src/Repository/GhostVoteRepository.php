<?php

namespace App\Repository;

use App\Entity\Characters;
use App\Entity\GhostRound;
use App\Entity\GhostVote;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GhostVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method GhostVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method GhostVote[]    findAll()
 * @method GhostVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GhostVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GhostVote::class);
    }

    public function findOneVote(GhostRound $round, Characters $character, User $voter): ?GhostVote
    {
        return $this->findOneBy(['round' => $round, 'character' => $character, 'voter' => $voter]);
    }

    /**
     * All votes cast in a round, characters/voters eager-loaded (small guild scale).
     */
    public function findByRound(GhostRound $round): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.round = :round')
            ->setParameter('round', $round)
            ->leftJoin('v.character', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }

    /**
     * Votes cast for a given character across every closed round it was nominated in.
     */
    public function findClosedVotesByCharacter(Characters $character): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.character = :character')
            ->setParameter('character', $character)
            ->leftJoin('v.round', 'r')
            ->addSelect('r')
            ->andWhere('r.closedAt IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function remove(GhostVote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function add(GhostVote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
