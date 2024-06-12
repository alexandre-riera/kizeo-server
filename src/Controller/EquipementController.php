<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class EquipementController extends AbstractController
{
    #[Route('/api/equipements', name: 'app_equipement', methods: ['GET'])]
    public function getEquipements(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Welcome to yhe equipement controller!',
            'path' => 'src/Controller/EquipementController.php',
        ]);
    }
}
