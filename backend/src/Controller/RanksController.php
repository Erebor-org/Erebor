<?php

namespace App\Controller;

use App\Repository\RanksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RanksController extends AbstractController
{
    #[Route('/ranks', name: 'ranks_list', methods: ['GET'])]
    public function getAllRanks(RanksRepository $repository): JsonResponse
    {
        $ranks = $repository->findAll();

        $formattedRanks = array_map(function ($rank) {
            return [
                'id' => $rank->getId(),
                'name' => $rank->getName(),
                'requiredDays' => $rank->getRequiredDays(),
                'description' => $rank->getDescription(),
                'lead' => $rank->getLead(),
                'recruiter' => $rank->getRecruiter(),
                'needUpdate' => $rank->getNeedUpdate(),
            ];
        }, $ranks);

        return $this->json($formattedRanks);
    }
}
