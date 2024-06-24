<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EquipementRepository;
use App\Entity\Equipement;


class EquipementController extends AbstractController
{
    #[Route('/api/equipements', name: 'app_equipement', methods: ['GET'])]
    public function getEquipements(EquipementRepository $equipementRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $equipementList  =  $equipementRepository->getEquipements();
        $jsonContactList = $serializer->serialize($equipementList, 'json');

        // Fetch all contacts in database
        $allEquipementsInDatabase = $entityManager->getRepository(Equipement::class)->findAll();
        
        // Persist each contact in database
        // Check before if contact exist in database
        if (count($allEquipementsInDatabase) !== count($equipementList)) {
            $equipementsArray = array_map(null, $equipementList);
            for ($i=0; $i < count($equipementsArray) ; $i++) {
                if (isset($equipementsArray[$i]) && !in_array($equipementsArray[$i],$allEquipementsInDatabase)) {
                    $equiment = new Equipement;
                    $equiment->setRepereSiteClient($equipementsArray[$i]['2']);
                    $equiment->setNumeroEquipement($equipementsArray[$i]['3']);
                    $equiment->setNature($equipementsArray[$i]['4']);
                    $equiment->setMiseEnService($equipementsArray[$i]['6']);
                    if (isset($equipementsArray[$i]['8'])) {
                        $equiment->setNumeroDeSerie($equipementsArray[$i]['8']);
                    }else{
                        $equiment->setNumeroDeSerie("");
                    }
                    if (isset($equipementsArray[$i]['10'])) {
                        $equiment->setMarque($equipementsArray[$i]['10']);
                    }else{
                        $equiment->setMarque("");
                    }
                    $equiment->setIdContact($equipementsArray[$i]['18']);

                    // tell Doctrine you want to (eventually) save the Product (no queries yet)
                    $entityManager->persist($equiment);

                }
            }
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
        }


        return new JsonResponse("Equipements sur API KIZEO : " . count($equipementList), Response::HTTP_OK, [], true);
    }
}
