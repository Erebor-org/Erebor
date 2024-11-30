<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Repository\CharactersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CharactersController extends AbstractController
{

    #[Route('/characters/get', name: 'characters_list', methods: ['GET'])]
    public function getAllCharacters(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->findAll();

        // Optionally, format the response to include recruiter details if needed
        $formattedCharacters = array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'class' => $character->getClass(),
                'level' => $character->getLevel(),
                'createdAt' => $character->getCreatedAt()->format('Y-m-d'),
                'isArchived' => $character->isArchived(),
                'recruiter' => $character->getRecruiter() ? [
                    'id' => $character->getRecruiter()->getId(),
                    'pseudo' => $character->getRecruiter()->getPseudo(),
                    'class' => $character->getRecruiter()->getClass(),
                ] : null,
            ];
        }, $characters);

        return $this->json($formattedCharacters);
    }


    #[Route('/characters/post', name: 'characters_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        RanksRepository $ranksRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        try {
            $recruitedAt = isset($data['recruitedAt']) ? new \DateTime($data['recruitedAt']) : new \DateTime();
    
            if ($recruitedAt > new \DateTime()) {
                return $this->json(['error' => 'The creation date cannot be in the future'], 400);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid creation date format'], 400);
        }

        // Calcul de la différence de jours
        $now = new \DateTime();
        $daysDifference = $recruitedAt->diff($now)->days;

        // Trouver le rang correspondant
        $rank = $ranksRepository->findRankForDays($daysDifference);


        if (!$rank) {
            return $this->json(['error' => 'No suitable rank found'], 404);
        }

        $character = new Characters();
        $character->setUserId($data['userId'] ?? null)
                ->setPseudo($data['pseudo'])
                ->setAnkamaPseudo($data['ankamaPseudo'])
                ->setClass($data['class'])
                ->setLevel($data['level'])
                ->setrecruitedAt($recruitedAt)
                ->setRank($rank['id'])
                ->setIsArchived($data['isArchived'] ?? false);
        // Handle the Recruiter
        if (isset($data['recruiterId'])) {
            $recruiter = $charactersRepository->find($data['recruiterId']);
            if (!$recruiter) {
                return $this->json(['error' => 'Recruiter not found'], 404);
            }
            $character->setRecruiter($recruiter);
        }

        $em->persist($character);
        $em->flush();

        return $this->json($character);
    }


    #[Route('/characters/{id}', name: 'characters_show', methods: ['GET'])]
    public function show(CharactersRepository $repository, int $id): JsonResponse
        {
            $character = $repository->find($id);
            if (!$character) {
                return $this->json(['error' => 'Character not found'], 404);
            }
            return $this->json($character);
        }
        #[Route('/characters/all', name: 'characters_get_all', methods: ['GET'])]
        public function getAll(CharactersRepository $repository): JsonResponse
        {
            $characters = $repository->findBy([], ['recruitedAt' => 'DESC']); // Tri par date de création descendante
            return $this->json($characters);
        }
        #[Route('/characters/{id}', name: 'characters_update', methods: ['PUT'])]
        public function update(
            Request $request,
            CharactersRepository $repository,
            EntityManagerInterface $em,
            int $id
        ): JsonResponse {
            // Find the character to update
            $character = $repository->find($id);
            if (!$character) {
                return $this->json(['error' => 'Character not found'], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Update basic fields
            $character->setUserId($data['userId'] ?? $character->getUserId())
                    ->setPseudo($data['pseudo'] ?? $character->getPseudo())
                    ->setAnkamaPseudo($data['ankamaPseudo'] ?? $character->getAnkamaPseudo())
                    ->setClass($data['class'] ?? $character->getClass())
                    ->setLevel($data['level'] ?? $character->getLevel())
                    ->setIsArchived($data['isArchived'] ?? $character->isArchived());

            // Handle recruiter assignment
            if (isset($data['recruiterId'])) {
                $recruiter = $repository->find($data['recruiterId']);
                if (!$recruiter) {
                    return $this->json(['error' => 'Recruiter not found'], 404);
                }

                // Prevent self-assignment as recruiter
                if ($recruiter->getId() === $character->getId()) {
                    return $this->json(['error' => 'A character cannot be their own recruiter'], 400);
                }

                $character->setRecruiter($recruiter);
            } else {
                $character->setRecruiter(null); // Remove recruiter if not provided
            }

            // Persist changes
            $em->flush();

            return $this->json($character);
    }

    #[Route('/characters/{id}', name: 'characters_delete', methods: ['DELETE'])]
    public function delete(CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $repository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $repository->remove($character, true);
        return $this->json(['message' => 'Character deleted successfully']);
    }
    #[Route('/characters/with-recruiters', name: 'characters_with_recruiters', methods: ['GET'])]
    public function getCharactersWithRecruiters(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->findCharactersWithRecruiters();
        return $this->json($characters);
    }
    #[Route('/characters/lead', name: 'characters_with_lead', methods: ['GET'])]
    public function getCharactersWithLead(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->createQueryBuilder('c')
            ->join('c.rank', 'r')
            ->where('r.lead = :lead')
            ->setParameter('lead', true)
            ->getQuery()
            ->getResult();

        return $this->json($characters);
    }

    #[Route('/characters/recruiter', name: 'characters_recruiter', methods: ['GET'])]
    public function getCharactersWithRankRange(CharactersRepository $repository): JsonResponse
    {
        // Query to fetch characters with rank ID between 1 and 4
        $characters = $repository->createQueryBuilder('c')
            ->join('c.rank', 'r') // Assuming the rank relationship is defined as 'rank' in Characters entity
            ->where('r.id BETWEEN :minRank AND :maxRank')
            ->setParameter('minRank', 1)
            ->setParameter('maxRank', 4)
            ->getQuery()
            ->getResult();

        // Format the response if needed
        $formattedCharacters = array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'class' => $character->getClass(),
                'level' => $character->getLevel(),
                'createdAt' => $character->getCreatedAt()->format('Y-m-d'),
                'rank' => $character->getRank() ? [
                    'id' => $character->getRank()->getId(),
                    'name' => $character->getRank()->getName(),
                ] : null,
            ];
        }, $characters);

        return $this->json($formattedCharacters);
    }
}
