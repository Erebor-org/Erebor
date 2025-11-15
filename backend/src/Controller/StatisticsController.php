<?php

namespace App\Controller;

use App\Repository\CharactersRepository;
use App\Repository\MuleRepository;
use App\Repository\RanksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function getStatistics(
        Request $request,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository,
        RanksRepository $ranksRepository
    ): JsonResponse {
        // Get filter parameters
        $filter = $request->query->get('filter', 'global');
        $roleId = $request->query->get('roleId');
        $recruiterId = $request->query->get('recruiterId');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        // Prepare date range DateTime objects if provided
        $startDateTime = null;
        $endDateTime = null;
        if ($startDate) {
            $startDateTime = new \DateTime($startDate);
            $startDateTime->setTime(0, 0, 0);
        }
        if ($endDate) {
            $endDateTime = new \DateTime($endDate);
            $endDateTime->setTime(23, 59, 59);
        }

        // Base query to get active characters
        $charactersQueryBuilder = $charactersRepository->createQueryBuilder('c')
            ->where('c.isArchived = :isArchived')
            ->setParameter('isArchived', false);

        // Apply date range filter for recruitment stats
        if ($startDateTime) {
            $charactersQueryBuilder->andWhere('c.recruitedAt >= :startDate')
                ->setParameter('startDate', $startDateTime);
        }
        
        if ($endDateTime) {
            $charactersQueryBuilder->andWhere('c.recruitedAt <= :endDate')
                ->setParameter('endDate', $endDateTime);
        }

        // Apply filters
        if ($filter === 'byRole' && $roleId) {
            $charactersQueryBuilder->andWhere('c.rank = :rankId')
                ->setParameter('rankId', $roleId);
        } elseif ($filter === 'byRecruiter' && $recruiterId) {
            $charactersQueryBuilder->andWhere('c.recruiter = :recruiterId')
                ->setParameter('recruiterId', $recruiterId);
        }

        // Execute query
        $characters = $charactersQueryBuilder
            ->leftJoin('c.rank', 'r')
            ->leftJoin('c.recruiter', 'recruiter')
            ->leftJoin('c.mules', 'mules')
            ->addSelect('r', 'recruiter', 'mules')
            ->getQuery()
            ->getResult();

        // Get all mules for active characters
        $mulesQueryBuilder = $muleRepository->createQueryBuilder('m')
            ->join('m.mainCharacter', 'c')
            ->where('c.isArchived = :isArchived')
            ->andWhere('m.isArchived = :muleIsArchived')
            ->setParameter('isArchived', false)
            ->setParameter('muleIsArchived', false);

        // Apply date range filter to mules query (based on main character's recruitment date)
        if ($startDateTime) {
            $mulesQueryBuilder->andWhere('c.recruitedAt >= :muleStartDate')
                ->setParameter('muleStartDate', $startDateTime);
        }
        
        if ($endDateTime) {
            $mulesQueryBuilder->andWhere('c.recruitedAt <= :muleEndDate')
                ->setParameter('muleEndDate', $endDateTime);
        }

        // Count active mules
        $totalMules = count($mulesQueryBuilder->getQuery()->getResult());

        // Apply filters to mules query
        if ($filter === 'byRole' && $roleId) {
            $mulesQueryBuilder->andWhere('c.rank = :rankId')
                ->setParameter('rankId', $roleId);
        } elseif ($filter === 'byRecruiter' && $recruiterId) {
            $mulesQueryBuilder->andWhere('c.recruiter = :recruiterId')
                ->setParameter('recruiterId', $recruiterId);
        }

        $mules = $mulesQueryBuilder->getQuery()->getResult();

        // Calculate statistics
        $totalCharacters = count($characters);
        $totalMules = count($mules);
        $totalCharactersIncludingMules = $totalCharacters + $totalMules;

        // Calculate booty counts (B2, B3, B4, etc.)
        $bootyCounts = [];
        foreach ($characters as $character) {
            // Count active mules for booty distribution
            $activeMules = $character->getMules()->filter(function($mule) {
                return !$mule->isArchived();
            });
            $muleCount = count($activeMules);
            
            $bootyLevel = 'B' . ($muleCount + 1);
            if (!isset($bootyCounts[$bootyLevel])) {
                $bootyCounts[$bootyLevel] = 0;
            }
            $bootyCounts[$bootyLevel]++;
        }
        
        // Sort booty counts by key
        ksort($bootyCounts);

        // Calculate class distribution
        $classDistribution = [];
        foreach ($characters as $character) {
            $class = $character->getClass();
            if (!isset($classDistribution[$class])) {
                $classDistribution[$class] = 0;
            }
            $classDistribution[$class]++;
        }

        // Convert to percentages
        if ($totalCharacters > 0) {
            foreach ($classDistribution as $class => $count) {
                $classDistribution[$class] = round(($count / $totalCharacters) * 100);
            }
        }

        // Sort class distribution by class name
        ksort($classDistribution);

        // Calculate recruiter performance with full recruiter info
        $recruiterPerformance = [];
        foreach ($characters as $character) {
            $recruiter = $character->getRecruiter();
            if ($recruiter) {
                $recruiterName = $recruiter->getPseudo();
                if (!isset($recruiterPerformance[$recruiterName])) {
                    $recruiterPerformance[$recruiterName] = [
                        'count' => 0,
                        'class' => $recruiter->getClass(),
                    ];
                }
                $recruiterPerformance[$recruiterName]['count']++;
            }
        }

        // Sort recruiter performance by count (descending)
        uasort($recruiterPerformance, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Calculate member roles distribution
        $memberRolesDistribution = [];
        foreach ($characters as $character) {
            $rank = $character->getRank();
            if ($rank) {
                $rankName = $rank->getName();
                if (!isset($memberRolesDistribution[$rankName])) {
                    $memberRolesDistribution[$rankName] = 0;
                }
                $memberRolesDistribution[$rankName]++;
            }
        }

        // Sort member roles distribution by count (descending)
        arsort($memberRolesDistribution);

        // Prepare response
        $statistics = [
            'totalCharacters' => $totalCharacters,
            'totalCharactersIncludingMules' => $totalCharactersIncludingMules,
            'bootyCounts' => $bootyCounts,
            'classDistribution' => $classDistribution,
            'recruiterPerformance' => $recruiterPerformance,
            'memberRolesDistribution' => $memberRolesDistribution,
        ];

        return $this->json($statistics);
    }
}
