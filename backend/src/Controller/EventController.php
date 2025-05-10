<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\FileUploadService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    private FileUploadService $fileUploadService;
    private NotificationService $notificationService;

    public function __construct(
        FileUploadService $fileUploadService,
        NotificationService $notificationService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
    }

    #[Route('/events', methods: ['GET'])]
    public function getAllEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findAllOrderedByDate();
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events/upcoming', methods: ['GET'])]
    public function getUpcomingEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findUpcomingEvents();
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events/past', methods: ['GET'])]
    public function getPastEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findPastEvents();
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events/completed', methods: ['GET'])]
    public function getCompletedEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findCompletedEvents();
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events/{id}', methods: ['GET'])]
    public function getEvent(Event $event): JsonResponse
    {
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true);
        
        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setEventDate(new \DateTime($data['eventDate']));
        
        // Get organizer from request
        if (isset($data['organizerId'])) {
            $organizer = $entityManager->getRepository(Characters::class)->find($data['organizerId']);
            if (!$organizer) {
                return $this->json(['error' => 'Organizer not found'], Response::HTTP_BAD_REQUEST);
            }
            $event->setOrganizer($organizer);
        } else {
            return $this->json(['error' => 'Organizer is required'], Response::HTTP_BAD_REQUEST);
        }
        
        // Handle image upload if present
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $imageFileName = $this->fileUploadService->upload($imageFile);
            $event->setImageFilename($imageFileName);
        }
        
        $entityManager->persist($event);
        $entityManager->flush();
        
        return $this->json($event, Response::HTTP_CREATED, [], ['groups' => 'event:read']);
    }

    #[Route('/events/{id}', methods: ['PUT'])]
    public function updateEvent(Request $request, Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        // Try to get data from FormData first
        $formData = $request->request->get('data');
        
        // If not found, try to get from request body
        if (!$formData) {
            $formData = $request->getContent();
        }
        
        $data = json_decode($formData, true);
        
        if (!$data) {
            return $this->json(['error' => 'Invalid request data'], Response::HTTP_BAD_REQUEST);
        }
        
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setEventDate(new \DateTime($data['eventDate']));
        
        // Update organizer if provided
        if (isset($data['organizerId'])) {
            $organizer = $entityManager->getRepository(Characters::class)->find($data['organizerId']);
            if (!$organizer) {
                return $this->json(['error' => 'Organizer not found'], Response::HTTP_BAD_REQUEST);
            }
            $event->setOrganizer($organizer);
        }
        
        // Handle image upload if present
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            // Delete old image if exists
            if ($event->getImageFilename()) {
                $this->fileUploadService->deleteFile($event->getImageFilename());
            }
            
            $imageFileName = $this->fileUploadService->upload($imageFile);
            $event->setImageFilename($imageFileName);
        }
        
        $entityManager->flush();
        
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/events/{id}', methods: ['DELETE'])]
    public function deleteEvent(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        // Delete image if exists
        if ($event->getImageFilename()) {
            $this->fileUploadService->deleteFile($event->getImageFilename());
        }
        
        $entityManager->remove($event);
        $entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/events/{id}/complete', methods: ['PATCH'])]
    public function completeEvent(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $event->setIsCompleted(true);
        $entityManager->flush();
        
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }
}
