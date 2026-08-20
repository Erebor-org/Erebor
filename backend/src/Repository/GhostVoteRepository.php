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

    /**
     * Every vote ever cast, across every round (open and closed), character/round eager-loaded.
     * Used to build the global ghost registry (one query instead of one per character).
     */
    public function findAllWithCharacterAndRound(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.character', 'c')
            ->addSelect('c')
            ->leftJoin('v.round', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getResult();
    }

    /**
     * Total votes ever cast per character, across every round (open and closed) — a simple
     * lifetime "how many times has this character been flagged" count for the Members page.
     * Returns a plain [characterId => total] map, only for characters with at least one vote.
     */
    public function countAllByCharacter(): array
    {
        $rows = $this->createQueryBuilder('v')
            ->select('IDENTITY(v.character) AS characterId', 'COUNT(v.id) AS total')
            ->groupBy('v.character')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['characterId']] = (int) $row['total'];
        }

        return $result;
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
