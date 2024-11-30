<?php

namespace App\Controller;

use App\Entity\Ranks;
use App\Repository\RanksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RanksController extends AbstractController
{

    #[Route('/ranks', name: 'ranks_get_all', methods: ['GET'])]
    public function getAll(RanksRepository $repository): JsonResponse
    {
        $ranks = $repository->findBy([], ['id' => 'ASC']); // Tri par ID croissant
        return $this->json($ranks);
    }

    #[Route('/ranks/', name: 'ranks_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $rank = new Ranks();
        $rank->setName($data['name'])
            ->setRequiredDays($data['requiredDays'] ?? null) // Handle nullable requiredDays
            ->setDescription($data['description'])
            ->setLead($data['lead'] ?? false) // Default to false if not provided
            ->setRecruiter($data['recruiter'] ?? false); // Default to false if not provided

        // Check if both Lead and Recruiter are true
        if ($rank->getLead() && $rank->getRecruiter()) {
            $rank->setNeedUpdate(false);
        } else {
            $rank->setNeedUpdate(true);
        }

        $em->persist($rank);
        $em->flush();

        return $this->json($rank);
    }


    #[Route('/ranks/{id}', name: 'ranks_show', methods: ['GET'])]
    public function show(RanksRepository $repository, int $id): JsonResponse
    {
        $rank = $repository->find($id);
        if (!$rank) {
            return $this->json(['error' => 'Rank not found'], 404);
        }
        return $this->json($rank);
    }

    #[Route('/ranks/{id}', name: 'ranks_update', methods: ['PUT'])]
    public function update(Request $request, RanksRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $rank = $repository->find($id);
        if (!$rank) {
            return $this->json(['error' => 'Rank not found'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
    
        $rank->setName($data['name'] ?? $rank->getName())
             ->setRequiredDays($data['requiredDays'] ?? $rank->getRequiredDays())
             ->setDescription($data['description'] ?? $rank->getDescription())
             ->setLead($data['lead'] ?? $rank->getLead()) // Removed extra semicolon
             ->setRecruiter($data['recruiter'] ?? $rank->getRecruiter()); // Removed extra semicolon
    
        // Check if both Lead and Recruiter are true
        if ($rank->getLead() && $rank->getRecruiter()) {
            $rank->setNeedUpdate(false);
        } else {
            $rank->setNeedUpdate(true);
        }
        $em->flush();
    
        return $this->json($rank);
    }
    

    #[Route('/ranks/{id}', name: 'ranks_delete', methods: ['DELETE'])]
    public function delete(RanksRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $rank = $repository->find($id);
        if (!$rank) {
            return $this->json(['error' => 'Rank not found'], 404);
        }

        $repository->remove($rank, true);
        return $this->json(['message' => 'Rank deleted successfully']);
    }
    #[Route('/ranks/lead', name: 'ranks_with_lead', methods: ['GET'])]
    public function getRanksWithLead(RanksRepository $repository): JsonResponse
    {
        $ranks = $repository->findByLead();
        return $this->json($ranks);
    }

    #[Route('/ranks/recruiter', name: 'ranks_with_recruiter', methods: ['GET'])]
    public function getRanksWithRecruiter(RanksRepository $repository): JsonResponse
    {
        $ranks = $repository->findByRecruiter();
        return $this->json($ranks);
    }
}
