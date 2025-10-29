<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\Mule;
use App\Repository\CharactersRepository;
use App\Repository\RanksRepository; // Ensure this is correctly imported
use App\Repository\MuleRepository; // Added for switchWithMule
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class CharactersController extends AbstractController
{    
    private HttpClientInterface $httpClient;
    private NotificationService $notificationService;

    public function __construct(HttpClientInterface $httpClient, NotificationService $notificationService)
    {
        $this->httpClient = $httpClient;
        $this->notificationService = $notificationService;
    }

    #[Route('/characters/', name: 'characters_list', methods: ['GET'])]
    public function getAllCharacters(CharactersRepository $repository): JsonResponse
    {
        // Fetch characters ordered by rank.requiredDays
        $characters = $repository->createQueryBuilder('c')
            ->leftJoin('c.rank', 'r')
            ->leftJoin('c.recruiter', 'recruiter')
            ->leftJoin('c.mules', 'mules')
            ->addSelect('r', 'recruiter', 'mules')
            ->orderBy('r.requiredDays', 'DESC')
            ->addOrderBy('c.recruitedAt', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        $formattedCharacters = array_map(function ($character) {
            $muleList = [];
            foreach ($character->getMules() as $mule) {
                $muleList[] = [
                    'id' => $mule->getId(),
                    'pseudo' => $mule->getPseudo(),
                    'ankamaPseudo' => $mule->getAnkamaPseudo(),
                    'class' => $mule->getClass(),
                    'isArchived' => $mule->isArchived()
                ];
            }

            return [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'ankamaPseudo' => $character->getAnkamaPseudo(),
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
                'mules' => $muleList,
                'notes' => $character->getNotes(),
            ];
        }, $characters);

        return $this->json($formattedCharacters, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => fn($object) => $object->getId(),
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
        // Ensure minimum 1 day difference
        if ($daysDifference === 0) {
            $daysDifference = 1;
        }
        // Trouver le rang correspondant
        $rank = $ranksRepository->findRankForDays($daysDifference);

        if (!$rank || $rank === 0) {
            return $this->json(['error' => 'No suitable rank found'], 404);
        }

        // Créer le personnage principal
        $character = new Characters();
        $character->setUserId($data['userId'] ?? null)
                ->setPseudo($data['pseudo'])
                ->setAnkamaPseudo($data['ankamaPseudo'])
                ->setClass($data['class'])
                ->setRecruitedAt($recruitedAt)
                ->setRank($rank)
                ->setIsArchived($data['isArchived'] ?? false)
                ->setNotes($data['notes'] ?? null);
                
        // Handle the Recruiter
        if (isset($data['recruiterId'])) {
            $recruiter = $charactersRepository->find($data['recruiterId']);
            if (!$recruiter) {
                return $this->json(['error' => 'Recruiter not found'], 404);
            }
            $character->setRecruiter($recruiter);
        }

        // Persister le personnage principal
        $em->persist($character);
        
        // Traiter les mules si elles sont présentes
        if (isset($data['mules']) && is_array($data['mules']) && count($data['mules']) > 0) {
            foreach ($data['mules'] as $muleData) {
                // Créer une nouvelle mule
                $mule = new Mule();
                $mule->setPseudo($muleData['pseudo'])
                     ->setAnkamaPseudo($muleData['ankamaPseudo'])
                     ->setClass($muleData['class'])
                     ->setIsArchived(false)
                     ->setMainCharacter($character);
                
                // Persister la mule
                $em->persist($mule);
                
                // We don't send mule notification here since this is part of a new character creation
                // Mule notifications are only sent when adding mules to existing characters (in MuleController)
            }
        }
        
        // Exécuter les requêtes en base de données
        $em->flush();
        
        // Refresh the character entity to ensure all relationships are loaded
        $em->refresh($character);
        
        // Préparer la réponse avec le personnage principal et ses mules
        $response = [
            'id' => $character->getId(),
            'pseudo' => $character->getPseudo(),
            'ankamaPseudo' => $character->getAnkamaPseudo(),
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
            'mules' => [],
            'notes' => $character->getNotes(),
        ];
        
        // Ajouter les mules à la réponse
        $mules = $character->getMules();
        if ($mules->count() > 0) {
            foreach ($mules as $mule) {
                $response['mules'][] = [
                    'id' => $mule->getId(),
                    'pseudo' => $mule->getPseudo(),
                    'ankamaPseudo' => $mule->getAnkamaPseudo(),
                    'class' => $mule->getClass(),
                    'isArchived' => $mule->isArchived()
                ];
            }
        }
        
        // Send notification after ensuring all relationships are loaded
        $this->notificationService->notify('character_import', $character);

        return $this->json($response, 200, [], [
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

    #[Route('/characters/{id}/mules', name: 'character_mules', methods: ['GET'])]
    public function getCharacterMules(Characters $character): JsonResponse
    {
        $mules = $character->getMules()->toArray();
    
        return $this->json($mules);
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
        $this->notificationService->notify('archivage_added', $character);
    
        return $this->json([
            'message' => 'Character archive status updated successfully.',
            'character' => [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'isArchived' => $character->isArchived(),
            ],
        ], 200);
    }

    #[Route('/characters/{id<\d+>}', name: 'characters_show', methods: ['GET'])]
    public function show(CharactersRepository $repository, int $id): JsonResponse
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

        // Store the old rank to check for recruiter status change
        $oldRank = $character->getRank();
        $wasRecruiter = $oldRank && $oldRank->getRecruiter();

        // Update basic fields
        $character->setUserId($data['userId'] ?? $character->getUserId())
                ->setPseudo($data['pseudo'] ?? $character->getPseudo())
                ->setAnkamaPseudo($data['ankamaPseudo'] ?? $character->getAnkamaPseudo())
                ->setClass($data['class'] ?? $character->getClass())
                ->setIsArchived($data['isArchived'] ?? $character->isArchived())
                ->setNotes($data['notes'] ?? $character->getNotes());
        if (isset($data['recruitedAt'])) {
            $character->setRecruitedAt(new \DateTime($data['recruitedAt']));
        }

        // Handle rank update
        if (isset($data['rankId'])) {
            $ranksRepository = $em->getRepository(\App\Entity\Ranks::class);
            $newRank = $ranksRepository->find($data['rankId']);
            if (!$newRank) {
                return $this->json(['error' => 'Rank not found'], 404);
            }
            $character->setRank($newRank);
        }

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

        // Check if the character is losing recruiter status
        $newRank = $character->getRank();
        $isNowRecruiter = $newRank && $newRank->getRecruiter();
        
        if ($wasRecruiter && !$isNowRecruiter) {
            // Character is losing recruiter status - keep the recruitment stats
            // The recruited characters will keep their recruiter_id pointing to this character
            error_log("[UPDATE] Character (ID={$character->getId()}) is losing recruiter status - keeping recruitment history");
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
            ->orderBy('r.requiredDays', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json($characters, 200, [], [
            'groups' => 'characters_list',
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }
    #[Route('/characters/{id}/update-class', name: 'update_character_class', methods: ['PUT'])]
    public function updateClass(Request $request, CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $repository->find($id);

        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['class'])) {
            return $this->json(['error' => 'Missing class field'], 400);
        }

        $character->setClass($data['class']);
        $em->flush();

        return $this->json(['message' => 'Class updated successfully'], 200);
    }
    #[Route('/characters/{id}/update-pseudo', name: 'update_character_pseudo', methods: ['PUT'])]
    public function updatePseudo(Request $request, CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $character = $repository->find($id);

        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['pseudo'])) {
            return $this->json(['error' => 'Missing class field'], 400);
        }

        $character->setPseudo($data['pseudo']);
        $em->flush();

        return $this->json(['message' => 'Pseudo updated successfully'], 200);
    }
    #[Route('/characters/{id}/unarchive', name: 'character_unarchive', methods: ['PUT'])]
    public function unarchive(CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        // Find the mule by ID
        $character = $repository->find($id);

        // Handle case where mule is not found
        if (!$character) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        // Set isArchived to false
        $character->setIsArchived(false);
        $em->flush();

        return $this->json(['message' => 'Mule unarchived successfully']);
    }

    #[Route('/characters/{characterId}/switch-with-mule/{muleId}', name: 'character_switch_with_mule', methods: ['POST'])]
    public function switchWithMule(
        Request $request,
        int $characterId,
        int $muleId,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $character = $charactersRepository->find($characterId);
        $mule = $muleRepository->find($muleId);
        if (!$character || !$mule) {
            return $this->json(['error' => 'Character or Mule not found'], 404);
        }
        if ($mule->getMainCharacter()->getId() !== $character->getId()) {
            return $this->json(['error' => 'This mule does not belong to the specified main character'], 400);
        }
        
        // Check if the old main is a recruiter (has recruiter rank)
        $isOldMainRecruiter = $character->getRank() && $character->getRank()->getRecruiter();
        error_log("[SWITCH] Old main (ID={$character->getId()}) is recruiter: " . ($isOldMainRecruiter ? 'YES' : 'NO'));
        
        // Backup all mules except the one being promoted
        $allMules = $character->getMules()->toArray();
        $otherMules = array_filter($allMules, fn($m) => $m->getId() !== $mule->getId());
        
        // Create new Character from mule
        $newCharacter = new Characters();
        $newCharacter->setUserId($character->getUserId())
            ->setPseudo($mule->getPseudo())
            ->setAnkamaPseudo($mule->getAnkamaPseudo())
            ->setClass($mule->getClass())
            ->setIsArchived($mule->isArchived())
            ->setRecruitedAt($character->getRecruitedAt())
            ->setRank($character->getRank())
            ->setNotes($character->getNotes())
            ->setRecruiter($character->getRecruiter());
        $em->persist($newCharacter);
        $em->flush(); // Pour que $newCharacter ait un ID
        
        // If the old main is a recruiter, transfer all recruited characters to the new main
        if ($isOldMainRecruiter) {
            $connection = $em->getConnection();
            
            // Get all characters that have the old main as recruiter
            $recruitedCharacters = $charactersRepository->createQueryBuilder('c')
                ->where('c.recruiter = :recruiterId')
                ->setParameter('recruiterId', $character->getId())
                ->getQuery()
                ->getResult();
            
            error_log("[SWITCH] Found " . count($recruitedCharacters) . " characters with old main (ID={$character->getId()}) as recruiter");
            
            // Transfer all recruited characters to the new main
            $transferredCount = $connection->executeStatement(
                'UPDATE characters SET recruiter_id = :newRecruiterId WHERE recruiter_id = :oldRecruiterId',
                ['newRecruiterId' => $newCharacter->getId(), 'oldRecruiterId' => $character->getId()]
            );
            
            error_log("[SWITCH] Transferred $transferredCount characters from old main (ID={$character->getId()}) to new main (ID={$newCharacter->getId()}) as recruiter");
        }
        
        // Réassigner les warnings de l'ancien main vers le nouveau main
        $connection = $em->getConnection();
        // Logguer le nombre de warnings réassignés
        $updatedWarnings = $connection->executeStatement(
            'UPDATE warnings SET character_id = :newId WHERE character_id = :oldId',
            ['newId' => $newCharacter->getId(), 'oldId' => $character->getId()]
        );
        error_log("[SWITCH] Nombre de warnings réassignés de l'ancien main (ID={$character->getId()}) vers le nouveau main (ID={$newCharacter->getId()}): $updatedWarnings");
        // Vérifier combien de warnings pointent encore vers l'ancien main
        $remainingWarnings = $connection->fetchOne(
            'SELECT COUNT(*) FROM warnings WHERE character_id = :oldId',
            ['oldId' => $character->getId()]
        );
        error_log("[SWITCH] Nombre de warnings qui pointent ENCORE vers l'ancien main (ID={$character->getId()}): $remainingWarnings");
        if ($remainingWarnings > 0) {
            throw new \RuntimeException("Il reste $remainingWarnings warning(s) liés à l'ancien main (ID=" . $character->getId() . "). Switch annulé pour éviter la suppression de l'historique.");
        }
        // IMPORTANT : vider le UnitOfWork pour éviter que Doctrine ne tente de réécrire l'ancien état
        $em->clear();
        // Recharger les entités nécessaires
        $newCharacter = $charactersRepository->find($newCharacter->getId());
        $mule = $muleRepository->find($mule->getId());
        $character = $charactersRepository->find($character->getId());
        // Créer la nouvelle mule et réassigner les autres mules
        $newMule = new \App\Entity\Mule();
        $newMule->setPseudo($character->getPseudo())
            ->setAnkamaPseudo($character->getAnkamaPseudo())
            ->setClass($character->getClass())
            ->setIsArchived($character->isArchived())
            ->setMainCharacter($newCharacter);
        $em->persist($newMule);
        foreach ($otherMules as $otherMule) {
            $otherMuleEntity = $em->getRepository(\App\Entity\Mule::class)->find($otherMule->getId());
            if ($otherMuleEntity) {
                $otherMuleEntity->setMainCharacter($newCharacter);
            }
        }
        // Supprimer l'ancien main et la mule
        $em->remove($character);
        $em->remove($mule);
        $em->flush();

        // Notification Discord via NotificationService avec switchedBy (robuste)
        // DEBUG : log le body brut reçu
        $rawBody = $request->getContent();
        $requestBody = json_decode($rawBody, true);
        $switchedBy = $requestBody['switchedBy'] ?? null;
        if (!$switchedBy) {
            $user = $this->getUser();
            if ($user && method_exists($user, 'getUsername')) {
                $switchedBy = $user->getUsername();
            } elseif ($character && $character->getUserId()) {
                $userRepo = $em->getRepository(\App\Entity\User::class);
                $userEntity = $userRepo->find($character->getUserId());
                if ($userEntity && method_exists($userEntity, 'getUsername')) {
                    $switchedBy = $userEntity->getUsername();
                }
            }
        }
        if (!$switchedBy) {
            $switchedBy = 'Un utilisateur inconnu';
        }
        $this->notificationService->notify('switch_main', [
            'oldMain' => $character ? $character->getPseudo() : '',
            'newMain' => $newCharacter ? $newCharacter->getPseudo() : '',
            'switchedBy' => $switchedBy,
        ]);

        return $this->json([
            'message' => 'Switch successful',
            'newMain' => [
                'id' => $newCharacter->getId(),
                'pseudo' => $newCharacter->getPseudo(),
                'ankamaPseudo' => $newCharacter->getAnkamaPseudo(),
                'class' => $newCharacter->getClass(),
            ],
            'newMule' => [
                'id' => $newMule->getId(),
                'pseudo' => $newMule->getPseudo(),
                'ankamaPseudo' => $newMule->getAnkamaPseudo(),
                'class' => $newMule->getClass(),
            ]
        ]);
    }

    #[Route('/api/characters', name: 'api_characters', methods: ['GET'])]
    public function getActiveCharacters(CharactersRepository $repository): JsonResponse
    {
        $characters = $repository->findBy(['isArchived' => false]);
        $data = array_map(fn($c) => [
            'id' => $c->getId(),
            'pseudo' => $c->getPseudo(),
            'class' => $c->getClass(),
        ], $characters);
        return $this->json($data);
    }
}
