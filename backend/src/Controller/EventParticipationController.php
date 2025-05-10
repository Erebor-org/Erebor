<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipation;
use App\Entity\Characters;
use App\Repository\CharactersRepository;
use App\Repository\EventParticipationRepository;
use App\Repository\EventRepository;
use App\Service\EventScoringService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventParticipationController extends AbstractController
{
    private EventScoringService $scoringService;
    private NotificationService $notificationService;

    public function __construct(
        EventScoringService $scoringService,
        NotificationService $notificationService
    ) {
        $this->scoringService = $scoringService;
        $this->notificationService = $notificationService;
    }

    #[Route('/event-participations', methods: ['GET'])]
    public function getAllParticipations(EventParticipationRepository $repository): JsonResponse
    {
        $participations = $repository->findAll();
        return $this->json($participations, Response::HTTP_OK, [], ['groups' => 'participation:read']);
    }

    #[Route('/event-participations/event/{id}', methods: ['GET'])]
    public function getParticipationsByEvent(Event $event, EventParticipationRepository $repository): JsonResponse
    {
        $participations = $repository->findByEventOrderedByPosition($event);
        return $this->json($participations, Response::HTTP_OK, [], ['groups' => 'participation:read']);
    }

    #[Route('/event-participations/ladder', methods: ['GET'])]
    public function getLadderStandings(EventParticipationRepository $repository): JsonResponse
    {
        $standings = $repository->getLadderStandings();
        return $this->json($standings, Response::HTTP_OK);
    }

    #[Route('/event-participations', methods: ['POST'])]
    public function createParticipation(
        Request $request, 
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        CharactersRepository $charactersRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        if (!isset($data['eventId']) || !isset($data['characterId']) || !isset($data['position'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
        
        // Get event and character
        $event = $eventRepository->find($data['eventId']);
        $character = $charactersRepository->find($data['characterId']);
        
        if (!$event || !$character) {
            return $this->json(['error' => 'Event or character not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if participation already exists
        $existingParticipation = $entityManager->getRepository(EventParticipation::class)->findOneBy([
            'event' => $event,
            'character' => $character
        ]);
        
        if ($existingParticipation) {
            return $this->json(['error' => 'Character already participating in this event'], Response::HTTP_CONFLICT);
        }
        
        // Calculate points based on position
        $position = (int) $data['position'];
        $points = $this->scoringService->getPointsForPosition($position);
        
        // Create new participation
        $participation = new EventParticipation();
        $participation->setEvent($event);
        $participation->setCharacter($character);
        $participation->setPosition($position);
        $participation->setPoints($points);
        
        $entityManager->persist($participation);
        $entityManager->flush();
        
        return $this->json($participation, Response::HTTP_CREATED, [], ['groups' => 'participation:read']);
    }

    #[Route('/event-participations/batch', methods: ['POST'])]
    public function createBatchParticipations(
        Request $request, 
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        CharactersRepository $charactersRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        if (!isset($data['eventId']) || !isset($data['participations']) || !is_array($data['participations'])) {
            return $this->json(['error' => 'Invalid request format'], Response::HTTP_BAD_REQUEST);
        }
        
        // Get event
        $event = $eventRepository->find($data['eventId']);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Remove existing participations for this event
        $existingParticipations = $entityManager->getRepository(EventParticipation::class)->findBy(['event' => $event]);
        foreach ($existingParticipations as $existingParticipation) {
            $entityManager->remove($existingParticipation);
        }
        
        $createdParticipations = [];
        
        // Create new participations
        foreach ($data['participations'] as $participationData) {
            if (!isset($participationData['characterId']) || !isset($participationData['position'])) {
                continue; // Skip invalid entries
            }
            
            $character = $charactersRepository->find($participationData['characterId']);
            if (!$character) {
                continue; // Skip if character not found
            }
            
            $position = (int) $participationData['position'];
            $points = $this->scoringService->getPointsForPosition($position);
            
            $participation = new EventParticipation();
            $participation->setEvent($event);
            $participation->setCharacter($character);
            $participation->setPosition($position);
            $participation->setPoints($points);
            
            $entityManager->persist($participation);
            $createdParticipations[] = $participation;
        }
        
        $entityManager->flush();
        
        return $this->json($createdParticipations, Response::HTTP_CREATED, [], ['groups' => 'participation:read']);
    }

    #[Route('/event-participations/{id}', methods: ['DELETE'])]
    public function deleteParticipation(
        EventParticipation $participation, 
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $entityManager->remove($participation);
        $entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/event-participations/character/{id}', methods: ['GET'])]
    public function getParticipationsByCharacter(
        int $id, 
        CharactersRepository $charactersRepository,
        EventParticipationRepository $participationRepository
    ): JsonResponse
    {
        $character = $charactersRepository->find($id);
        
        if (!$character) {
            return $this->json(['error' => 'Character not found'], Response::HTTP_NOT_FOUND);
        }
        
        $participations = $participationRepository->findByCharacter($character);
        
        return $this->json($participations, Response::HTTP_OK, [], ['groups' => 'participation:read']);
    }
}
