<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploadController extends AbstractController
{
    private string $uploadDirectory;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $projectDir = $parameterBag->get('kernel.project_dir');
        $this->uploadDirectory = $projectDir . '/public/uploads/events';
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0755, true);
        }
    }

    #[Route('/api/upload/event-image', name: 'upload_event_image', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadEventImage(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('image');

        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        // Validate file type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return $this->json(['error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WEBP'], 400);
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            return $this->json(['error' => 'File size too large. Maximum size: 5MB'], 400);
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $extension = $file->guessExtension() ?: 'bin';
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        // Move file to upload directory
        try {
            $file->move($this->uploadDirectory, $newFilename);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to upload file: ' . $e->getMessage()], 500);
        }

        // Return the URL path (relative to public directory)
        $fileUrl = '/uploads/events/' . $newFilename;

        return $this->json([
            'success' => true,
            'url' => $fileUrl,
            'filename' => $newFilename
        ]);
    }

    #[Route('/uploads/events/{filename}', name: 'serve_event_image', methods: ['GET'])]
    public function serveEventImage(string $filename): Response
    {
        $filePath = $this->uploadDirectory . '/' . $filename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            return new Response('File not found', 404);
        }

        $mimeType = mime_content_type($filePath);
        $fileContent = file_get_contents($filePath);

        return new Response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
        ]);
    }
}

