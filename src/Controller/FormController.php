<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\Equipement;
use App\Repository\FormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormController extends AbstractController
{

    #[Route('/api/forms', name: 'app_api_form')]
    public function getForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getForms(986403);
        $jsonContactList = $serializer->serialize($formList, 'json');
       
        // Fetch all contacts in database
        $allFormsInDatabase = $entityManager->getRepository(Form::class)->findAll();
        
        dump($formList['data']);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList['data']) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }

    #[Route('/api/forms/update', name: 'app_api_form_update')]
    public function getDataOfForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $dataOfFormList  =  $formRepository->getDataOfForms(986403);
        $jsonDataOfFormList  = $serializer->serialize($dataOfFormList, 'json');
        $equipementsData = [];

        $allEquipementsInDatabase = $entityManager->getRepository(Equipement::class)->findAll();

        // dump($dataOfFormList);

        foreach ($dataOfFormList as $key => $value) {
            foreach ($dataOfFormList[$key] as $ids => $value) {
                array_push($equipementsData, $dataOfFormList[$key]['data']);
            }
        }
        // dump($equipementsData[10]['fields']['contrat_de_maintenance']);
        dump($equipementsData);

        // Persist each equipement in database
        // Save a new contrat_de_maintenance equipement in database when a technician make an update
        
        foreach ($equipementsData as $id => $value) {
            $equipement = new Equipement;
            $equipement->setIdContact($equipementsData[$id]['fields']['id_client_']['value']);
            $equipement->setDernièreVisite($equipementsData[$id]['fields']['date_et_heure1']['value']);
            $equipement->setTrigrammeTech($equipementsData[$id]['fields']['trigramme']['value']);
                foreach ($equipementsData[$id]['fields']['contrat_de_maintenance']['value'] as $idEquipement => $value) {
                        $equipement->setNumeroEquipement($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['equipement']['value']);
                        $equipement->setNature(strtolower($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference7']['value']));
                        $equipement->setModeFonctionnement($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['mode_fonctionnement_2']['value']);
                        $equipement->setRepereSiteClient($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['localisation_site_client']['value']);
                        $equipement->setMiseEnService($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference2']['value']);
                        $equipement->setNumeroDeSerie($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference6']['value']);
                        $equipement->setMarque($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference5']['value']);
                        $equipement->setHauteur($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference3']['value']);
                        $equipement->setLargeur($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference1']['value']);
                        $equipement->setPlaqueSignaletique($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['plaque_signaletique']['value']);

                        //Anomalies en fonction de la nature de l'équipement
                        switch($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference7']['value']){
                            case 'niveleur':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie_niveleur']['value']);
                                break;
                            case 'portail':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie_portail']['value']);
                                break;
                            case 'porte rapide':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie_porte_rapide']['value']);
                                break;
                            case 'porte pietonne':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie_porte_pietonne']['value']);
                                break;
                            case 'barriere':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie_barriere']['value']);
                                break;
                            case 'rideau':
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['rid']['value']);
                                break;
                            default:
                                $equipement->setAnomalies($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['anomalie']['value']);
                            
                        }
                        $equipement->setEtat($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['etat']['value']);
        
                        // tell Doctrine you want to (eventually) save the Product (no queries yet)
                        $entityManager->persist($equipement);
                }
            }
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
        
        
        
        // return new JsonResponse($dataOfFormList[1]['data'], Response::HTTP_OK, [], false);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($dataOfFormList) . " | Equipements en BDD : " . count($allEquipementsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }
}
