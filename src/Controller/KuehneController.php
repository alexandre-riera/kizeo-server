<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class KuehneController extends AbstractController
{
    #[Route('/kuehne', name: 'app_kuehne')]
    public function index(): Response
    {
        return $this->render('kuehne/index.html.twig', [
            'controller_name' => 'KuehneController',
        ]);
    }
}
