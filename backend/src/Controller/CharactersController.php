<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\GuildEvent;
use App\Entity\Mule;
use App\EventListener\GuildEventListener;
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
    public function getAllCharacters(CharactersRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        // Force refresh from database by clearing entity manager cache
        $em->clear();
        
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
            ->setCacheable(false) // Disable query cache
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
                    'requiredDays' => $character->getRank()->getRequiredDays(),
                    'lead' => $character->getRank()->getLead(),
                    'recruiter' => $character->getRank()->getRecruiter(),
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
                'requiredDays' => $character->getRank()->getRequiredDays(),
                'lead' => $character->getRank()->getLead(),
                'recruiter' => $character->getRank()->getRecruiter(),
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
                    'requiredDays' => $character->getRank()->getRequiredDays(),
                    'lead' => $character->getRank()->getLead(),
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
        
        // Archive/unarchive all associated mules
        foreach ($character->getMules() as $mule) {
            $mule->setIsArchived($data['isArchived']);
        }
        
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
    public function show(CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        // Force refresh from database
        $em->clear();
        
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

        // Store the old archived status to check if it changed
        $oldIsArchived = $character->isArchived();
        
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
        
        // If the archived status changed, update all associated mules
        $newIsArchived = $character->isArchived();
        if ($oldIsArchived !== $newIsArchived) {
            foreach ($character->getMules() as $mule) {
                $mule->setIsArchived($newIsArchived);
            }
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

    #[Route('/characters/{id}/update-recruitment', name: 'update_character_recruitment', methods: ['PUT'])]
    public function updateRecruitment(
        Request $request,
        CharactersRepository $repository,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $character = $repository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Update recruitment date if provided
        if (isset($data['recruitedAt'])) {
            try {
                $recruitedAt = new \DateTime($data['recruitedAt']);
                
                // Validate that the date is not in the future
                if ($recruitedAt > new \DateTime()) {
                    return $this->json(['error' => 'The recruitment date cannot be in the future'], 400);
                }
                
                $character->setRecruitedAt($recruitedAt);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format. Expected format: Y-m-d'], 400);
            }
        }

        // Update recruiter if provided
        if (isset($data['recruiterId'])) {
            if ($data['recruiterId'] === null) {
                // Remove recruiter
                $character->setRecruiter(null);
            } else {
                $recruiter = $repository->find($data['recruiterId']);
                if (!$recruiter) {
                    return $this->json(['error' => 'Recruiter not found'], 404);
                }

                // Prevent self-assignment as recruiter
                if ($recruiter->getId() === $character->getId()) {
                    return $this->json(['error' => 'A character cannot be their own recruiter'], 400);
                }

                $character->setRecruiter($recruiter);
            }
        }

        $em->flush();

        // Return updated character data
        return $this->json([
            'message' => 'Recruitment information updated successfully',
            'character' => [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'recruitedAt' => $character->getRecruitedAt()?->format('Y-m-d'),
                'recruiter' => $character->getRecruiter() ? [
                    'id' => $character->getRecruiter()->getId(),
                    'pseudo' => $character->getRecruiter()->getPseudo(),
                    'class' => $character->getRecruiter()->getClass(),
                ] : null,
            ],
        ], 200);
    }
    #[Route('/characters/{id}/unarchive', name: 'character_unarchive', methods: ['PUT'])]
    public function unarchive(CharactersRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        // Find the character by ID
        $character = $repository->find($id);

        // Handle case where character is not found
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        // Set isArchived to false for character and all associated mules
        $character->setIsArchived(false);
        foreach ($character->getMules() as $mule) {
            $mule->setIsArchived(false);
        }
        $em->flush();

        return $this->json(['message' => 'Character unarchived successfully']);
    }

    /**
     * Une mule devient le personnage principal. Techniquement : création d'un nouveau
     * personnage depuis la mule, l'ancien main devient une mule, puis suppression des anciennes
     * lignes. Tout l'historique rattaché à l'ancien main est transféré AVANT la suppression
     * (sinon les ON DELETE CASCADE / SET NULL l'effacent) : recrues, avertissements (cible et
     * auteur), votes fantômes, journal, lien utilisateur. Le tout dans une transaction, sans
     * journalisation automatique (un seul événement main_switched est écrit à la place).
     */
    #[Route('/characters/{characterId}/switch-with-mule/{muleId}', name: 'character_switch_with_mule', methods: ['POST'])]
    public function switchWithMule(
        Request $request,
        int $characterId,
        int $muleId,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository,
        EntityManagerInterface $em,
        GuildEventListener $guildEventListener
    ): JsonResponse {
        $character = $charactersRepository->find($characterId);
        $mule = $muleRepository->find($muleId);
        if (!$character || !$mule) {
            return $this->json(['error' => 'Character or Mule not found'], 404);
        }
        if ($mule->getMainCharacter()->getId() !== $character->getId()) {
            return $this->json(['error' => 'This mule does not belong to the specified main character'], 400);
        }

        $connection = $em->getConnection();
        $connection->beginTransaction();
        try {
            [$character, $newCharacter, $newMule] = $guildEventListener->withoutRecording(
                fn () => $this->performSwitch($character, $mule, $charactersRepository, $muleRepository, $em)
            );

            // Événement unique, écrit hors du mode muet.
            $event = (new GuildEvent(GuildEvent::MAIN_SWITCHED, $newCharacter))
                ->setMule($newMule)
                ->setPayload([
                    'oldMain' => $character->getPseudo(),
                    'newMain' => $newCharacter->getPseudo(),
                    'class' => $newCharacter->getClass(),
                ])
                ->setActor($guildEventListener->resolveActor($em));
            $em->persist($event);
            $em->flush();

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

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

    /**
     * @return array{0: Characters, 1: Characters, 2: Mule} ancien main (détaché, pour son pseudo),
     *         nouveau main, nouvelle mule (l'ancien main)
     */
    private function performSwitch(
        Characters $character,
        Mule $mule,
        CharactersRepository $charactersRepository,
        MuleRepository $muleRepository,
        EntityManagerInterface $em
    ): array {
        $oldId = $character->getId();
        $otherMuleIds = array_values(array_map(
            fn (Mule $m) => $m->getId(),
            array_filter($character->getMules()->toArray(), fn (Mule $m) => $m->getId() !== $mule->getId())
        ));

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
        // Le membre est le même : on garde sa date de départ éventuelle, pas « maintenant ».
        $newCharacter->setArchivedAt($mule->isArchived() ? ($mule->getArchivedAt() ?? $character->getArchivedAt()) : null);
        $em->persist($newCharacter);
        $em->flush(); // pour obtenir l'ID du nouveau main

        $newId = $newCharacter->getId();
        $connection = $em->getConnection();
        $ids = ['newId' => $newId, 'oldId' => $oldId];
        // Toutes les recrues, que l'ancien main ait encore un rang recruteur ou non.
        $connection->executeStatement('UPDATE characters SET recruiter_id = :newId WHERE recruiter_id = :oldId', $ids);
        $connection->executeStatement('UPDATE warnings SET character_id = :newId WHERE character_id = :oldId', $ids);
        $connection->executeStatement('UPDATE warnings SET author_character_id = :newId WHERE author_character_id = :oldId', $ids);
        $connection->executeStatement('UPDATE ghost_vote SET character_id = :newId WHERE character_id = :oldId', $ids);
        $connection->executeStatement('UPDATE guild_event SET character_id = :newId WHERE character_id = :oldId', $ids);
        $connection->executeStatement('UPDATE "user" SET character_id = :newId WHERE character_id = :oldId', $ids);

        // Vider le UnitOfWork : les UPDATE ci-dessus ont rendu les entités en mémoire obsolètes.
        $em->clear();
        $newCharacter = $charactersRepository->find($newId);
        $mule = $muleRepository->find($mule->getId());
        $character = $charactersRepository->find($oldId);

        $newMule = new Mule();
        $newMule->setPseudo($character->getPseudo())
            ->setAnkamaPseudo($character->getAnkamaPseudo())
            ->setClass($character->getClass())
            ->setIsArchived($character->isArchived())
            ->setMainCharacter($newCharacter);
        $newMule->setArchivedAt($character->getArchivedAt());
        // L'ancien main n'est pas une « nouvelle mule » : il existe depuis l'arrivée du membre.
        $newMule->setCreatedAt($character->getRecruitedAt());
        $em->persist($newMule);

        // Réassigner les autres mules AVANT de supprimer l'ancien main (ON DELETE CASCADE).
        foreach ($otherMuleIds as $otherMuleId) {
            $muleRepository->find($otherMuleId)?->setMainCharacter($newCharacter);
        }
        $em->flush();

        $em->remove($character);
        $em->remove($mule);
        $em->flush();

        return [$character, $newCharacter, $newMule];
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
