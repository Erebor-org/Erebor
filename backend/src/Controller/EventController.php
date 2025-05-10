<?php

namespace App\Controller;

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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/events')]
class EventController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService,
        private FileUploadService $fileUploadService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getAllEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findAllOrderedByDate();
        
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/upcoming', methods: ['GET'])]
    public function getUpcomingEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findUpcomingEvents();
        
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/past', methods: ['GET'])]
    public function getPastEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findPastEvents();
        
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/completed', methods: ['GET'])]
    public function getCompletedEvents(EventRepository $repository): JsonResponse
    {
        $events = $repository->findCompletedEvents();
        
        return $this->json($events, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getEvent(Event $event): JsonResponse
    {
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

#[Route('', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true);
        
        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setEventDate(new \DateTime($data['eventDate']));
        $event->setOrganizer($this->getUser());
        
        // Handle image upload if present
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $imageFileName = $this->fileUploadService->upload($imageFile);
            $event->setImageFilename($imageFileName);
        }
        
        $entityManager->persist($event);
        $entityManager->flush();
        
        $this->notificationService->createNotification('Nouvel événement créé: ' . $event->getTitle());
        
        return $this->json($event, Response::HTTP_CREATED, [], ['groups' => 'event:read']);
    }

#[Route('/{id}', methods: ['PUT'])]
    public function updateEvent(Request $request, Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true);
        
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setEventDate(new \DateTime($data['eventDate']));
        
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
        
        $this->notificationService->createNotification('Événement mis à jour: ' . $event->getTitle());
        
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }

#[Route('/{id}', methods: ['DELETE'])]
    public function deleteEvent(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        // Delete image if exists
        if ($event->getImageFilename()) {
            $this->fileUploadService->deleteFile($event->getImageFilename());
        }
        
        $eventTitle = $event->getTitle();
        
        $entityManager->remove($event);
        $entityManager->flush();
        
        $this->notificationService->createNotification('Événement supprimé: ' . $eventTitle);
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

#[Route('/{id}/complete', methods: ['PATCH'])]
    public function completeEvent(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $event->setIsCompleted(true);
        $entityManager->flush();
        
        $this->notificationService->createNotification('Événement terminé: ' . $event->getTitle());
        
        return $this->json($event, Response::HTTP_OK, [], ['groups' => 'event:read']);
    }
}
