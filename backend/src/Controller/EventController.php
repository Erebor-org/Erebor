<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Repository\EventRepository;
use App\Repository\EventParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    #[Route('/events', name: 'events_list', methods: ['GET'])]
    public function list(EventRepository $repository): JsonResponse
    {
        $events = $repository->findAllOrderedByDate();

        $formattedEvents = array_map(function ($event) {
            return $this->formatEvent($event);
        }, $events);

        return $this->json($formattedEvents);
    }

    #[Route('/events/upcoming', name: 'events_upcoming', methods: ['GET'])]
    public function upcoming(EventRepository $repository): JsonResponse
    {
        $events = $repository->findUpcoming();

        $formattedEvents = array_map(function ($event) {
            return $this->formatEvent($event);
        }, $events);

        return $this->json($formattedEvents);
    }

    #[Route('/events/past', name: 'events_past', methods: ['GET'])]
    public function past(EventRepository $repository): JsonResponse
    {
        $events = $repository->findPast();

        $formattedEvents = array_map(function ($event) {
            return $this->formatEvent($event);
        }, $events);

        return $this->json($formattedEvents);
    }

    #[Route('/events/{id}', name: 'event_show', methods: ['GET'])]
    public function show(int $id, EventRepository $repository, EventParticipantRepository $participantRepository, UserRepository $userRepository): JsonResponse
    {
        $event = $repository->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $eventData = $this->formatEvent($event, true);

        // Get participants with user information
        $participants = $participantRepository->findByEventOrderedByRank($id);
        $participantsData = [];

        foreach ($participants as $participant) {
            $user = $userRepository->find($participant->getUserId());
            $participantsData[] = [
                'id' => $participant->getId(),
                'userId' => $participant->getUserId(),
                'username' => $user ? $user->getUsername() : 'Unknown',
                'rank' => $participant->getRank(),
                'pointsEarned' => $participant->getPointsEarned(),
                'prizeReceived' => $participant->getPrizeReceived(),
                'subscribedAt' => $participant->getSubscribedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $eventData['participants'] = $participantsData;
        $eventData['participantsCount'] = count($participants);

        // Check if current user is subscribed
        $currentUser = $this->getUser();
        $isSubscribed = false;
        if ($currentUser) {
            $userParticipant = $participantRepository->findByEventAndUser($id, $currentUser->getId());
            $isSubscribed = $userParticipant !== null;
            $eventData['isSubscribed'] = $isSubscribed;
            if ($isSubscribed) {
                $eventData['userParticipant'] = [
                    'rank' => $userParticipant->getRank(),
                    'pointsEarned' => $userParticipant->getPointsEarned(),
                    'prizeReceived' => $userParticipant->getPrizeReceived(),
                ];
            }
        }

        return $this->json($eventData);
    }

    #[Route('/events', name: 'event_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return $this->json(['error' => 'User not authenticated'], 401);
            }
            
            // Check if user has role other than just ROLE_USER
            $roles = $user->getRoles();
            // Filter out ROLE_USER to check if user has any other role
            $otherRoles = array_filter($roles, fn($role) => $role !== 'ROLE_USER');
            
            if (empty($otherRoles)) {
                return $this->json(['error' => 'Only users with roles other than ROLE_USER can create events'], 403);
            }

            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'Invalid JSON data: ' . json_last_error_msg()], 400);
            }

            if (!isset($data['title']) || !isset($data['description']) || !isset($data['date'])) {
                return $this->json(['error' => 'Missing required fields: title, description, date'], 400);
            }

            // Validate title
            if (empty(trim($data['title']))) {
                return $this->json(['error' => 'Title cannot be empty'], 400);
            }

            // Validate description
            if (empty(trim($data['description']))) {
                return $this->json(['error' => 'Description cannot be empty'], 400);
            }

            // Validate and parse date
            try {
                $eventDate = new \DateTime($data['date']);
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format: ' . $e->getMessage()], 400);
            }

            // Handle cashPrize - convert empty string to null
            $cashPrize = $data['cashPrize'] ?? null;
            if ($cashPrize === '' || $cashPrize === null || $cashPrize === false) {
                $cashPrize = null;
            } else {
                // Validate cashPrize is numeric if provided
                // Remove any non-numeric characters except decimal point
                $cashPrize = (string) $cashPrize;
                $cashPrize = preg_replace('/[^0-9.]/', '', $cashPrize);
                
                if (empty($cashPrize)) {
                    $cashPrize = null;
                } else {
                    // Validate it's a valid number
                    if (!is_numeric($cashPrize)) {
                        return $this->json(['error' => 'Cash prize must be a valid number'], 400);
                    }
                    // Ensure it doesn't exceed database precision
                    if ((float) $cashPrize > 99999999.99) {
                        return $this->json(['error' => 'Cash prize is too large'], 400);
                    }
                }
            }

            // Handle image - convert empty string to null
            $image = $data['image'] ?? null;
            if ($image === '') {
                $image = null;
            }

            $event = new Event();
            $event->setTitle(trim($data['title']))
                ->setDescription(trim($data['description']))
                ->setDate($eventDate)
                ->setCashPrize($cashPrize)
                ->setImage($image)
                ->setOwnerId($user->getId())
                ->setIsFinished(false);

            $em->persist($event);
            $em->flush();

            return $this->json($this->formatEvent($event, true), 201);
        } catch (\Exception $e) {
            // Log the full exception for debugging
            error_log('Error creating event: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'error' => 'Failed to create event: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    #[Route('/events/{id}', name: 'event_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EventRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $event = $repository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $user = $this->getUser();
        
        // Check if user is owner or has ROLE_OWNERS
        $isOwner = $event->getOwnerId() === $user->getId();
        $isOwnerRole = in_array('ROLE_OWNERS', $user->getRoles());
        
        if (!$isOwner && !$isOwnerRole) {
            return $this->json(['error' => 'You can only update your own events'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $event->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }
        if (isset($data['date'])) {
            try {
                $event->setDate(new \DateTime($data['date']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format'], 400);
            }
        }
        if (isset($data['cashPrize'])) {
            $event->setCashPrize($data['cashPrize']);
        }
        if (isset($data['image'])) {
            $event->setImage($data['image']);
        }

        $em->flush();

        return $this->json($this->formatEvent($event, true));
    }

    #[Route('/events/{id}', name: 'event_delete', methods: ['DELETE'])]
    public function delete(int $id, EventRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $event = $repository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $user = $this->getUser();
        $isOwner = $event->getOwnerId() === $user->getId();
        $isOwnerRole = in_array('ROLE_OWNERS', $user->getRoles());

        if (!$isOwner && !$isOwnerRole) {
            return $this->json(['error' => 'You can only delete your own events'], 403);
        }

        $em->remove($event);
        $em->flush();

        return $this->json(['message' => 'Event deleted successfully']);
    }

    #[Route('/events/{id}/subscribe', name: 'event_subscribe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function subscribe(int $id, EventRepository $repository, EventParticipantRepository $participantRepository, EntityManagerInterface $em): JsonResponse
    {
        $event = $repository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->isFinished()) {
            return $this->json(['error' => 'Cannot subscribe to finished events'], 400);
        }

        $user = $this->getUser();
        $userId = $user->getId();

        // Check if already subscribed
        $existingParticipant = $participantRepository->findByEventAndUser($id, $userId);
        if ($existingParticipant) {
            return $this->json(['error' => 'Already subscribed to this event'], 400);
        }

        $participant = new EventParticipant();
        $participant->setEvent($event)
            ->setUserId($userId);

        $em->persist($participant);
        $em->flush();

        return $this->json(['message' => 'Successfully subscribed to event', 'participant' => [
            'id' => $participant->getId(),
            'userId' => $participant->getUserId(),
            'eventId' => $event->getId(),
        ]]);
    }

    #[Route('/events/{id}/unsubscribe', name: 'event_unsubscribe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unsubscribe(int $id, EventRepository $repository, EventParticipantRepository $participantRepository, EntityManagerInterface $em): JsonResponse
    {
        $event = $repository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->isFinished()) {
            return $this->json(['error' => 'Cannot unsubscribe from finished events'], 400);
        }

        $user = $this->getUser();
        $userId = $user->getId();

        $participant = $participantRepository->findByEventAndUser($id, $userId);
        if (!$participant) {
            return $this->json(['error' => 'Not subscribed to this event'], 400);
        }

        $em->remove($participant);
        $em->flush();

        return $this->json(['message' => 'Successfully unsubscribed from event']);
    }

    #[Route('/events/{id}/finalize', name: 'event_finalize', methods: ['POST'])]
    public function finalize(int $id, Request $request, EventRepository $repository, EventParticipantRepository $participantRepository, EntityManagerInterface $em): JsonResponse
    {
        $event = $repository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $user = $this->getUser();
        $isOwner = $event->getOwnerId() === $user->getId();
        $isOwnerRole = in_array('ROLE_OWNERS', $user->getRoles());

        if (!$isOwner && !$isOwnerRole) {
            return $this->json(['error' => 'Only event owners or ROLE_OWNERS can finalize events'], 403);
        }

        $data = json_decode($request->getContent(), true);

        // Mark event as finished
        $event->setIsFinished(true);
        if (isset($data['note'])) {
            $event->setNote($data['note']);
        }

        // Get participants
        $participants = $participantRepository->findByEventOrderedByRank($id);
        $participantsCount = count($participants);

        if ($participantsCount === 0) {
            $em->flush();
            return $this->json($this->formatEvent($event, true));
        }

        // Calculate weighted points base (1000 base points, weighted by number of participants)
        $basePoints = 1000;
        $weightFactor = sqrt($participantsCount); // Square root weighting
        $weightedBasePoints = $basePoints * $weightFactor;

        // Process rankings if provided
        if (isset($data['rankings']) && is_array($data['rankings'])) {
            foreach ($data['rankings'] as $ranking) {
                if (!isset($ranking['userId']) || !isset($ranking['rank'])) {
                    continue;
                }

                $participant = $participantRepository->findByEventAndUser($id, $ranking['userId']);
                if (!$participant) {
                    continue;
                }

                $rank = (int) $ranking['rank'];
                $participant->setRank($rank);

                // Set points - use provided value or calculate based on rank
                if (isset($ranking['pointsEarned']) && $ranking['pointsEarned'] !== null && $ranking['pointsEarned'] !== '') {
                    $participant->setPointsEarned((string) round((float) $ranking['pointsEarned'], 2));
                } else {
                    // Calculate points based on rank (1st place gets max, descending)
                    // Formula: weightedBasePoints * (participantsCount - rank + 1) / participantsCount
                    $pointsEarned = ($weightedBasePoints * ($participantsCount - $rank + 1)) / $participantsCount;
                    $participant->setPointsEarned((string) round($pointsEarned, 2));
                }

                // Set prize if provided
                if (isset($ranking['prizeReceived']) && $ranking['prizeReceived'] !== null && $ranking['prizeReceived'] !== '') {
                    $participant->setPrizeReceived((string) round((float) $ranking['prizeReceived'], 2));
                }
            }
        }

        $em->flush();

        return $this->json($this->formatEvent($event, true));
    }

    #[Route('/events/leaderboard', name: 'events_leaderboard', methods: ['GET'])]
    public function leaderboard(EventParticipantRepository $participantRepository, UserRepository $userRepository): JsonResponse
    {
        $leaderboardData = $participantRepository->getLeaderboard();
        
        $formattedLeaderboard = [];
        foreach ($leaderboardData as $entry) {
            $user = $userRepository->find($entry['userId']);
            if ($user) {
                $formattedLeaderboard[] = [
                    'userId' => $user->getId(),
                    'username' => $user->getUsername(),
                    'totalPoints' => (float) $entry['totalPoints'],
                    'eventsCount' => (int) $entry['eventsCount'],
                ];
            }
        }

        // Sort by totalPoints descending
        usort($formattedLeaderboard, function($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });

        return $this->json($formattedLeaderboard);
    }

    private function formatEvent(Event $event, bool $includeDetails = false): array
    {
        try {
            $data = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date' => $event->getDate()->format('Y-m-d\TH:i:s'),
                'cashPrize' => $event->getCashPrize(),
                'image' => $event->getImage(),
                'ownerId' => $event->getOwnerId(),
                'isFinished' => $event->isFinished(),
                'createdAt' => $event->getCreatedAt()->format('Y-m-d\TH:i:s'),
            ];

            if ($includeDetails) {
                $data['note'] = $event->getNote();
            }

            return $data;
        } catch (\Exception $e) {
            error_log('Error formatting event: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw new \RuntimeException('Failed to format event: ' . $e->getMessage(), 0, $e);
        }
    }
}

