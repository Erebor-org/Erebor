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

        // Base query to get active characters
        $charactersQueryBuilder = $charactersRepository->createQueryBuilder('c')
            ->where('c.isArchived = :isArchived')
            ->setParameter('isArchived', false);

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
            $muleCount = count(array_filter($character->getMules()->toArray(), function($mule) {
                return !$mule->isArchived();
            }));
            
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

        // Calculate recruiter performance
        $recruiterPerformance = [];
        foreach ($characters as $character) {
            $recruiter = $character->getRecruiter();
            if ($recruiter) {
                $recruiterName = $recruiter->getPseudo();
                if (!isset($recruiterPerformance[$recruiterName])) {
                    $recruiterPerformance[$recruiterName] = 0;
                }
                $recruiterPerformance[$recruiterName]++;
            }
        }

        // Sort recruiter performance by count (descending)
        arsort($recruiterPerformance);

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
