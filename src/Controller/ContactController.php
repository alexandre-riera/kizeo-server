<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContactRepository;
use App\Entity\Contact;

class ContactController extends AbstractController
{
    #[Route('/api/contacts', name: 'app_contact', methods: ['GET'])]
    public function getContacts(ContactRepository $contactRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $contactList  =  $contactRepository->getContacts();
        $jsonContactList = $serializer->serialize($contactList, 'json');

        // Persist each contact in database
        
        $contact = new Contact;

        return new JsonResponse($jsonContactList, Response::HTTP_OK, [], true);
    }
}
