<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Repository\CharactersRepository;
use App\Repository\RanksRepository; // Ensure this is correctly imported
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CharactersController extends AbstractController
{

    #[Route('/characters/', name: 'characters_list', methods: ['GET'])]
public function getAllCharacters(CharactersRepository $repository): JsonResponse
{
    // Fetch characters ordered by rank.id
    $characters = $repository->createQueryBuilder('c')
        ->join('c.rank', 'r') // Join the rank table
        ->orderBy('r.id', 'ASC') // Order by rank.id ascending
        ->addOrderBy('c.id', 'ASC') // Optionally order by character.id for consistent results within ranks
        ->getQuery()
        ->getResult();

    // Format the response to include recruiter and rank details
    $formattedCharacters = array_map(function ($character) {
        return [
            'id' => $character->getId(),
            'pseudo' => $character->getPseudo(),
            'class' => $character->getClass(),
            'createdAt' => $character->getRecruitedAt()?->format('Y-m-d'),
            'isArchived' => $character->isArchived(),
            'recruiter' => $character->getRecruiter() ? [
                'id' => $character->getRecruiter()->getId(),
                'pseudo' => $character->getRecruiter()->getPseudo(),
                'class' => $character->getRecruiter()->getClass(),
            ] : null,
            'rank' => $character->getRank() ? [
                'id' => $character->getRank()->getId(),
                'name' => $character->getRank()->getName(),
            ] : null,
        ];
    }, $characters);

    return $this->json($formattedCharacters, 200, [], [
        'groups' => 'characters_list',
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
    ]);
}


    #[Route('/characters', name: 'characters_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        RanksRepository $ranksRepository,
        CharactersRepository $charactersRepository
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
                ->setRecruitedAt($recruitedAt)
                ->setRank($rank)
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

        return $this->json($character, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }





    #[Route('/characters/all', name: 'characters_get_all', methods: ['GET'])]
    public function getAll(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->findBy([], ['recruitedAt' => 'DESC']); // Tri par date de création descendante
        return $this->json($characters);
    }


    

    

    #[Route('/characters/recruiters', name: 'characters_with_recruiter_rank', methods: ['GET'])]
    public function getCharactersWithRecruiterRank(CharactersRepository $repository): JsonResponse
    {
        // Query pour trouver tous les personnages avec un rank ayant recruiter = true
        $characters = $repository->createQueryBuilder('c')
            ->join('c.rank', 'r') // Faire une jointure avec la table Rank
            ->where('r.recruiter = :recruiter') // Condition : recruiter = true
            ->setParameter('recruiter', true)
            ->orderBy('c.id', 'ASC') // Tri par ID de personnage en ordre croissant
            ->getQuery()
            ->getResult();

        // Format des données à retourner
        $formattedCharacters = array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'class' => $character->getClass(),
                'rank' => $character->getRank() ? [
                    'id' => $character->getRank()->getId(),
                    'name' => $character->getRank()->getName(),
                    'recruiter' => $character->getRank()->getRecruiter(),
                ] : null,
            ];
        }, $characters);

        return $this->json($formattedCharacters, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }

    #[Route('/characters/{id}/archive', name: 'characters_update_isArchived', methods: ['PUT'])]
    public function updateIsArchived(
        Request $request,
        CharactersRepository $repository,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        // Fetch the character by ID
        $character = $repository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
    
        // Parse the JSON body and validate
        $data = json_decode($request->getContent(), true);
        if (!isset($data['isArchived']) || !is_bool($data['isArchived'])) {
            return $this->json(['error' => 'The "isArchived" field is required and must be a boolean.'], 400);
        }
    
        // Update the isArchived field
        $character->setIsArchived($data['isArchived']);
        $em->flush();
    
        return $this->json([
            'message' => 'Character archive status updated successfully.',
            'character' => [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'isArchived' => $character->isArchived(),
            ],
        ], 200);
    }

    #[Route('/characters/{id}', name: 'characters_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(RanksRepository $repository, int $id): JsonResponse
    {
        $character = $repository->find($id);
    
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
    
        return $this->json($character);
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
                ->setRecruitedAt($recruitedAt) ?? $character->setRecruitedAt()
                ->setClass($data['class'] ?? $character->getClass())
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

        return $this->json($character, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }


    #[Route('/characters/{id}', name: 'characters_delete', methods: ['DELETE'])]
    public function delete(CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $repository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $repository->remove($character, true);
        return $this->json(['message' => 'Character deleted successfully'], 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }


    #[Route('/characters/with-recruiters', name: 'characters_with_recruiters', methods: ['GET'])]
    public function getCharactersWithRecruiters(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->findCharactersWithRecruiters();
        return $this->json($characters, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
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

        return $this->json($characters, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }
}
