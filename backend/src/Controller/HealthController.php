<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private Connection $connection
    ) {}

    #[Route('/health/live', name: 'health_live', methods: ['GET'])]
    public function liveness(): JsonResponse
    {
        // Liveness: Just check if the app process is alive
        return new JsonResponse(['status' => 'alive'], 200);
    }

    #[Route('/health/ready', name: 'health_ready', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        // Readiness: Check if app is ready to serve traffic (dependencies)
        try {
            // Check database connectivity
            $this->connection->executeQuery('SELECT 1');
            return new JsonResponse(['status' => 'ready'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'not_ready', 'error' => 'Database unavailable'], 503);
        }
    }
} 