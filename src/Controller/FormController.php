<?php

namespace App\Controller;

use App\Entity\Form;
use App\Entity\Equipement;
use App\Entity\Portail;
use App\Repository\FormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormController extends AbstractController
{
    /**
     * Function to count how many parents forms are on Kizeo  
     */
    #[Route('/api/forms/get', name: 'app_api_get_forms', methods: ['GET'])]
    public function getForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getForms();
        $jsonContactList = $serializer->serialize($formList, 'json');
       
        // Fetch all contacts in database
        $allFormsInDatabase = $entityManager->getRepository(Form::class)->findAll();
        
        dump($formList['forms']);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList['forms']) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }
    /**
     * Function to count how many parents forms are on Kizeo  
     */
    #[Route('/api/forms/data', name: 'app_api_form_data', methods: ['GET'])]
    public function getFormsAdvanced(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getFormsAdvanced();
        $jsonContactList = $serializer->serialize($formList, 'json');
       
        // Fetch all contacts in database
        $allFormsInDatabase = $entityManager->getRepository(Form::class)->findAll();
        
        // dump($formList['data']);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }

    /**
     * Function to ADD new equipments from technicians forms
     */
    #[Route('/api/forms/update', name: 'app_api_form_update', methods: ['GET'])]
    public function getDataOfForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        // GET all technicians forms from list class PORTAILS
        $dataOfFormList  =  $formRepository->getDataOfForms();
        $jsonDataOfFormList  = $serializer->serialize($dataOfFormList, 'json');
        $equipementsData = [];
        
        $allEquipementsInDatabase = $entityManager->getRepository(Equipement::class)->findAll();
        
        // dump($dataOfFormList);

        // GET DATA field from all technicians forms from list ID 986403 on Kizeo
        foreach ($dataOfFormList as $key => $value) {
                array_push($equipementsData, $dataOfFormList[$key]['data']);
        }
        // List all equipements updated in technician forms in database
        /**
        * dump($allEquipementsInDatabase); // gettype() = Type array
        * dump(gettype($equipementsData)); // Array of arrays. 1 array is 1 equipement
        */

        /**
         * Store all equipments resumes stored in database to an array
         */
        $allEquipementsResumeInDatabase = [];
        for ($i=0; $i < count($allEquipementsInDatabase); $i++) { 
            array_push($allEquipementsResumeInDatabase, $allEquipementsInDatabase[$i]->getIfexistDB());
        }
        // dump(gettype($allEquipementsResumeInDatabase[0])); // gettype = string for each value in $allEquipmementsResumeInDatabase

        foreach ($equipementsData as $equipement){
            /**
            * list all additional equipments stored in individual array
            * dump($equipement['fields']['contrat_de_maintenance']['value']);
            */
            foreach ($equipement['fields']['contrat_de_maintenance']['value']  as $additionalEquipment){
                // Everytime a new resume is read, we store its value in variable resume_equipement_supplementaire
                $resume_equipement_supplementaire = $additionalEquipment['equipement']['columns'];
                /**
                 * If resume_equipement_supplementaire value is NOT in  $allEquipementsResumeInDatabase array
                 * Method used : in_array(search, inThisArray, type) 
                 * type Optional. If this parameter is set to TRUE, the in_array() function searches for the search-string and specific type in the array
                 */
                if (!in_array($resume_equipement_supplementaire, $allEquipementsResumeInDatabase, TRUE)) {
                    

                    /**
                     * Persist each equipement in database
                     * Save a new contrat_de_maintenance equipement in database when a technician make an update
                    */
                    
                    foreach ($equipementsData as $id => $value) {
                        
                        foreach ($equipementsData[$id]['fields']['contrat_de_maintenance']['value'] as $idEquipement => $value) {
                            $equipement = new Equipement;
                            $equipement->setIdContact($equipementsData[$id]['fields']['id_client_']['value']);
                            $equipement->setDernièreVisite($equipementsData[$id]['fields']['date_et_heure1']['value']);
                            $equipement->setTrigrammeTech($equipementsData[$id]['fields']['trigramme']['value']);
                            $equipement->setSignatureTech($equipementsData[$id]['fields']['signature3']['value']);
                            $equipement->setNumeroEquipement($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['equipement']['value']);
                            $equipement->setIfExistDB($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['equipement']['columns']);
                            $equipement->setNature(strtolower($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference7']['value']));
                            $equipement->setModeFonctionnement($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['mode_fonctionnement_2']['value']);
                            $equipement->setRepereSiteClient($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['localisation_site_client']['value']);
                            $equipement->setMiseEnService($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference2']['value']);
                            $equipement->setNumeroDeSerie($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference6']['value']);
                            $equipement->setMarque($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference5']['value']);
                            $equipement->setLargeur($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference3']['value']);
                            $equipement->setHauteur($equipementsData[$id]['fields']['contrat_de_maintenance']['value'][$idEquipement]['reference1']['value']);
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
                    ?>
                    We have a new equipment or we have updated an equipment !
                    <?php
                }else{
                    echo "All equipments are already in database \n  Vous pouvez revenir en arrière";
                    die;
                }
            }
        }
        // return new JsonResponse($dataOfFormList[1]['data'], Response::HTTP_OK, [], false);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($dataOfFormList) . " | Equipements en BDD : " . count($allEquipementsInDatabase) . "\n ", Response::HTTP_OK, [], true);
    }

    /**
     * Function to ADD new PORTAILS from technicians forms
     */
    #[Route('/api/forms/update/portails', name: 'app_api_form_update_portails', methods: ['GET'])]
    public function getEtatDesLieuxPortailsDataOfForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        // GET all technicians forms from list class PORTAILS
        $dataOfFormList  =  $formRepository->getEtatDesLieuxPortailsDataOfForms();
        $jsonDataOfFormList  = $serializer->serialize($dataOfFormList, 'json');
        $equipementsData = [];
        $eachEquipementsData = [];
        
        $allPortailsInDatabase = $entityManager->getRepository(Portail::class)->findAll();
        /**
         * Store all equipments resumes stored in database to an array
         */
        $allportailsResumeInDatabase = [];
        $allNewPortailsResume = [];
        for ($i=0; $i < count($allPortailsInDatabase); $i++) { 
            array_push($allportailsResumeInDatabase, $allPortailsInDatabase[$i]->getIfexistDB());
        }

        foreach ($dataOfFormList as $formPortail) {
            // dd($formPortail);
            // array_push($equipementsData, $dataOfFormList[$key]['data']['fields']['portails']);
            // foreach ($equipementsData as $equipement){
            //     foreach ($equipement['value'] as $eachEquipement) {
            //         array_push($eachEquipementsData,  $eachEquipement);
            //     }
            // }
             
            /**
            * Persist each portail in database
            */

            foreach ($formPortail['data']['fields']['portails']['value'] as $portail) {
                array_push($allNewPortailsResume, $formPortail['data']['fields']['liste_clients']['columns']);
                if (!in_array($formPortail['data']['fields']['liste_clients']['columns'], $allportailsResumeInDatabase, TRUE)){
                    $equipement = new Portail;
                    $equipement->setTrigrammeTech($formPortail['data']['fields']['trigramme_de_la_personne_real']['value']);
                    $equipement->setIdContact($formPortail['data']['fields']['ref_interne_client']['value']);
                    if (isset($formPortail['data']['fields']['id_societe_'])) {
                        $equipement->setCodeSociete($formPortail['data']['fields']['id_societe_']['value']);
                    }else{
                        $equipement->setCodeSociete("");
                    }
                    $equipement->setDernièreVisite($formPortail['data']['fields']['date_et_heure1']['value']);
                    $equipement->setSignatureTech($formPortail['data']['fields']['signature2']['value']);
                    $equipement->setIfExistDB($formPortail['data']['fields']['liste_clients']['columns']);
                    $equipement->setCodeAgence($formPortail['data']['fields']['n_agence']['value']);
                    $equipement->setNomClient($formPortail['data']['fields']['liste_clients']['value']);
                    $equipement->setNumeroEquipement($portail['reference_equipement']['value']);
                    $equipement->setRepereSiteClient($portail['localisation_sur_site']['value']);
                    $equipement->setEtat($portail['etat_general_equipement1']['value']);
                    $equipement->setMarque($portail['marques']['value']);
                    $equipement->setNumeroDeSerie($portail['numero_serie']['value']);
                    $equipement->setModele($portail['modele']['value']);
                    $equipement->setMiseEnService($portail['date_installation']['value']);
                    $equipement->setnature($portail['types_de_portails']['value']);
                    $equipement->setNombresVantaux($portail['nombres_vantaux']['value']);
                    $equipement->setModeFonctionnement($portail['types_de_fonctionnement']['value']);
                    $equipement->setLargeur($portail['dimension_largeur_passage_uti']['value']);
                    $equipement->setLongueur($portail['dimension_longueur_vantail']['value']);
                    $equipement->setHauteur($portail['dimension_hauteur_vantail']['value']);
                    
                    $equipement->setPresenceCarnetEntretien($portail['presence_carnet_entretien']['value']);
                    $equipement->setPresenceNoticeFabricant($portail['presence_notice_fabriquant']['value']);
                    $equipement->setPortillonSurVantail($portail['presence_portillon_sur_le_van']['value']);
                    $equipement->setTypeDeGuidage($portail['types_de_guidage']['value']);
                    $equipement->setTypePortail($portail['types_de_portails1']['value']);
                    $equipement->setEspaceInf8mmRailProtectionGalets($portail['espace_inferieur_ou_egal_a_8_']['value']);
                    $equipement->setDistanceBasPortailRailInferieurSol($portail['distance_entre_le_bas_du_port']['value']);
                    $equipement->setEspaceHautPortailPlatineGaletsGuidage($portail['espace_entre_la_protection_du']['value']);
                    $equipement->setEspaceVantailGaletsGuidageInf8($portail['espace_entre_le_vantail_et_le']['value']);
                    $equipement->setButeeMecaAvantSurVantail($portail['butees_mecaniques_sur_vantail']['value']);
                    $equipement->setButeeMecaArriereSurVantail($portail['presence_de_la_butee_mecaniq']['value']);
                    $equipement->setEfficaciteButeesEnManuel($portail['verification_de_l_efficacite_']['value']);
                    $equipement->setSystemeAntiChutes($portail['presence_systeme_anti_chutes']['value']);
                    $equipement->setSeuilSurelSupA5($portail['absence_de_seuil_ou_surelevat']['value']);
                    $equipement->setMarquagePartiesSureleveesNonVisibles($portail['marquage_des_parties_sureleve']['value']);
                    $equipement->setPortailImmobileToutesPositionsEnManuel($portail['en_toute_position_a_arret_le_1']['value']);
                    $equipement->setDurMecaEnManuel($portail['absence_de_dur_mecanique_']['value']);
                    $equipement->setDistanceBarreauxCloture($portail['distance_entre_les_barreaux_d1']['value']);
                    // tell Doctrine you want to (eventually) save the Portail (no queries yet)
                    $entityManager->persist($equipement);
                }
            }    
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            ?>
            We have a new portail or we have updated an equipment !
            <?php
            $allPortailsInDatabase = $entityManager->getRepository(Portail::class)->findAll();
        }
        return new JsonResponse("Portails en BDD : " . count($allPortailsInDatabase) . "\n ", Response::HTTP_OK, [], true);
    }
}
