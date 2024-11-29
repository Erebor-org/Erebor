<?php
// src/Controller/ApiController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/hello', name: 'hello', methods: ['GET'])]
    public function hello(): JsonResponse
    {
        return new JsonResponse(['message' => 'hello world']);
    }
}