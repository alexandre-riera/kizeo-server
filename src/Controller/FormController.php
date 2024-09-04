<?php

namespace App\Controller;

use App\Entity\Form;
use GuzzleHttp\Client;
use App\Entity\Portail;
use App\Entity\Equipement;
use App\Entity\PortailAuto;
use App\Repository\FormRepository;
use App\Entity\PortailEnvironement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormController extends AbstractController
{
    /**
     * A REMETTRE A LA RENTRÉE BISOUS
     * 
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(){
        return new JsonResponse("L'application API KIZEO est lancée !", Response::HTTP_OK, [], true);
    }

    /**
     * @return Form[]Function to list all lists on Kizeo with getLists() function from FormRepository  
     */
    #[Route('/api/lists/get/equipements-contrat-38', name: 'app_api_get_lists_equipements_contrat_38', methods: ['GET'])]
    public function getListsEquipementsContrat38(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getListsEquipementsContrats38();
        
        dump($formList);

        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList), Response::HTTP_OK, [], true);
    }
    /**
     * @return Form[]Function to list all lists on Kizeo with getLists() function from FormRepository  
     */
    #[Route('/api/lists/get', name: 'app_api_get_lists', methods: ['GET'])]
    public function getLists(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getLists();
        $jsonContactList = $serializer->serialize($formList, 'json');
        
        dd($formList);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList), Response::HTTP_OK, [], true);
    }
    /**
     * @return Form[]Function to list all forms on Kizeo with getForms() function from FormRepository  
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
     * 
     * @return Form[] Returns an array of Formulaires with class PORTAILS 
     */
    #[Route('/api/forms/data', name: 'app_api_form_data', methods: ['GET'])]
    public function getFormsAdvanced(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $formList  =  $formRepository->getFormsAdvanced();
        $jsonContactList = $serializer->serialize($formList, 'json');
       
        // Fetch all contacts in database
        $allFormsInDatabase = $entityManager->getRepository(Form::class)->findAll();
        
        dump($formList);
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }

    /**
     * Function to ADD new equipments from technicians forms MAINTENANCE forms formulaire Visite maintenance Grenoble ID = 1004962
     */
    #[Route('/api/forms/update/maintenance', name: 'app_api_form_update', methods: ['GET'])]
    public function getDataOfFormsMaintenance(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        // GET all technicians forms formulaire Visite maintenance Grenoble id = 1004962
        $dataOfFormList  =  $formRepository->getDataOfFormsMaintenance();
        dd($dataOfFormList);
        $allEquipementsInDatabase = $entityManager->getRepository(Equipement::class)->findAll();
        /**
         * Store all equipments split resumes stored in database to $allEquipementsResumeInDatabase array
         */
        $allEquipementsResumeInDatabase = [];
        for ($i=0; $i < count($allEquipementsInDatabase); $i++) { 
            array_push($allEquipementsResumeInDatabase, array_unique(preg_split("/[:|]/", $allEquipementsInDatabase[$i]->getIfexistDB())));
            // dump(array_unique(preg_split("/[:|]/", $allEquipementsInDatabase[$i]->getIfexistDB())));
            // dump($allEquipementsInDatabase[$i]->getNumeroEquipement());
            // dump($allEquipementsInDatabase[$i]);
        }
        // --------------------------- RPRENDRE LUNDI A PARTIR D'ICI
        foreach ($dataOfFormList as $equipements){
            dump("Je recupere les formulaires maintenance Grenoble et Paris du repository dans $ dataofFormList");
            // dump($dataOfFormList);
            /**
            * List all additional equipments stored in individual array
            */
            // dump($equipements['contrat_de_maintenance']['value']);
            foreach ($equipements['contrat_de_maintenance']['value']  as $additionalEquipment){
                // Everytime a new resume is read, we store its value in variable resume_equipement_supplementaire
                $resume_equipement_supplementaire = array_unique(preg_split("/[:|]/", $additionalEquipment['equipement']['columns']));
                dump("--------------------------------------------------------------------------------------------------------------------");
                dump("Je recupere les équipements supplémentaires Grenoble et Paris dans $ additionalEquipment de la boucle sur $ dataofFormList");
                // dd($equipements['contrat_de_maintenance']['value'][19]);
                /**
                 * If resume_equipement_supplementaire value is NOT in  $allEquipementsResumeInDatabase array
                 * Method used : in_array(search, inThisArray, type) 
                 * type Optional. If this parameter is set to TRUE, the in_array() function searches for the search-string and specific type in the array
                 */
                // if (!in_array($resume_equipement_supplementaire, $allEquipementsResumeInDatabase, TRUE) && $equipements['test_']['value'] != 'oui' ) {
                if (!in_array($resume_equipement_supplementaire, $allEquipementsResumeInDatabase, TRUE)) {
                    
                    /**
                     * Persist each equipement in database
                     * Save a new contrat_de_maintenance equipement in database when a technician make an update
                     */
                    // dd($equipements);
                    $equipement = new Equipement;
                    $equipement->setIdContact($equipements['id_client_']['value']);
                    $equipement->setRaisonSociale($equipements['nom_client']['value']);
                    $equipement->setTest($equipements['test_']['value']);

                    if (isset($equipements['id_societe']['value'])) {
                        $equipement->setCodeSociete($equipements['id_societe']['value']);
                    }else{
                        $equipement->setCodeSociete("");
                    }
                    if (isset($equipements['id_agence']['value'])) {
                        $equipement->setCodeAgence($equipements['id_agence']['value']);
                    }else{
                        $equipement->setCodeAgence("");
                    }
                    
                    $equipement->setDernièreVisite($equipements['date_et_heure1']['value']);
                    $equipement->setTrigrammeTech($equipements['trigramme']['value']);
                    $equipement->setSignatureTech($equipements['signature3']['value']);

                    $equipement->setNumeroEquipement($additionalEquipment['equipement']['value']);
                    $equipement->setIfExistDB($additionalEquipment['equipement']['columns']);
                    $equipement->setNature(strtolower($additionalEquipment['reference7']['value']));
                    $equipement->setModeFonctionnement($additionalEquipment['mode_fonctionnement_2']['value']);
                    $equipement->setRepereSiteClient($additionalEquipment['localisation_site_client']['value']);
                    $equipement->setMiseEnService($additionalEquipment['reference2']['value']);
                    $equipement->setNumeroDeSerie($additionalEquipment['reference6']['value']);
                    $equipement->setMarque($additionalEquipment['reference5']['value']);
                    if (isset($additionalEquipment['reference3']['value'])) {
                        $equipement->setLargeur($additionalEquipment['reference3']['value']);
                    }else{
                        $equipement->setLargeur("");
                    }
                    if (isset($additionalEquipment['reference1']['value'])) {
                        $equipement->setHauteur($additionalEquipment['reference1']['value']);
                    }else{
                        $equipement->setHauteur("");
                    }
                    $equipement->setPlaqueSignaletique($additionalEquipment['plaque_signaletique']['value']);

                    //Anomalies en fonction de la nature de l'équipement
                    switch($additionalEquipment['anomalie']['value']){
                        case 'niveleur':
                            $equipement->setAnomalies($additionalEquipment['anomalie_niveleur']['value']);
                            break;
                        case 'portail':
                            $equipement->setAnomalies($additionalEquipment['anomalie_portail']['value']);
                            break;
                        case 'porte rapide':
                            $equipement->setAnomalies($additionalEquipment['anomalie_porte_rapide']['value']);
                            break;
                        case 'porte pietonne':
                            $equipement->setAnomalies($additionalEquipment['anomalie_porte_pietonne']['value']);
                            break;
                        case 'barriere':
                            $equipement->setAnomalies($additionalEquipment['anomalie_barriere']['value']);
                            break;
                        case 'rideau':
                            $equipement->setAnomalies($additionalEquipment['rid']['value']);
                            break;
                        default:
                            $equipement->setAnomalies($additionalEquipment['anomalie']['value']);
                        
                    }
                    $equipement->setEtat($additionalEquipment['etat']['value']);
                    if (isset($additionalEquipment['hauteur_de_nacelle_necessaire']['value'])) {
                        $equipement->setHauteurNacelle($additionalEquipment['hauteur_de_nacelle_necessaire']['value']);
                    }else{
                        $equipement->setHauteurNacelle("");
                    }
                    
                    if (isset($additionalEquipment['si_location_preciser_le_model']['value'])) {
                        $equipement->setModeleNacelle($additionalEquipment['si_location_preciser_le_model']['value']);
                    }else{
                        $equipement->setModeleNacelle("");
                    }

                    // tell Doctrine you want to (eventually) save the Product (no queries yet)
                    $entityManager->persist($equipement);
                    
                    
                    // actually executes the queries (i.e. the INSERT query)
                    $entityManager->flush();
                    
                    echo nl2br("We have a new equipment or we have updated an equipment !");
                }else{
                    echo nl2br("All equipments are already in database \n  Vous pouvez revenir en arrière");
                    die;
                }
            }
        }
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
            /**
            * Persist each portail, portail auto in database
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
                    if (isset($portail['presence_notice_fabricant']['value'])) {
                        $equipement->setPresenceNoticeFabricant($portail['presence_notice_fabricant']['value']);
                    }else{
                        $equipement->setPresenceNoticeFabricant("");
                    }
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

    /**
     * Function to ADD new PORTAILS AUTO from technicians forms
     */
    #[Route('/api/forms/update/portails/auto', name: 'app_api_form_update_portails_auto', methods: ['GET'])]
    public function getEtatDesLieuxPortailsAutoDataOfForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
       // GET all technicians forms from list class PORTAILS
       $dataOfFormList  =  $formRepository->getEtatDesLieuxPortailsDataOfForms();
       $jsonDataOfFormList  = $serializer->serialize($dataOfFormList, 'json');
       $equipementsData = [];
       $eachEquipementsData = [];
       
       $allPortailsAutoInDatabase = $entityManager->getRepository(PortailAuto::class)->findAll();
       /**
        * Store all equipments resumes stored in database to an array
        */
       $allportailsAutoResumeInDatabase = [];
       $allNewPortailsResume = [];
       for ($i=0; $i < count($allPortailsAutoInDatabase); $i++) { 
           array_push($allportailsAutoResumeInDatabase, $allPortailsAutoInDatabase[$i]->getIfexistDB());
       }

       foreach ($dataOfFormList as $formPortail) { 
           /**
           * Persist each portail, portail auto in database
           */

           foreach ($formPortail['data']['fields']['portails']['value'] as $portail) {
               array_push($allNewPortailsResume, $formPortail['data']['fields']['liste_clients']['columns']);
               if (!in_array($formPortail['data']['fields']['liste_clients']['columns'], $allportailsAutoResumeInDatabase, TRUE)){

                   $portailAuto = new PortailAuto;

                   $portailAuto->setIdContact($formPortail['data']['fields']['ref_interne_client']['value']);
                   if (isset($formPortail['data']['fields']['id_societe_'])) {
                       $portailAuto->setIdSociete($formPortail['data']['fields']['id_societe_']['value']);
                   }else{
                       $portailAuto->setIdSociete("");
                   }
                   $portailAuto->setContactSecuritePortillon($portail['contact_securite_sur_portillo']['value']);
                   $portailAuto->setPresenceBoitierPompiers($portail['presence_boitier_pompiers']['value']);
                   $portailAuto->setProtectionPignonMoteur($portail['protection_pignon_moteur']['value']);
                   $portailAuto->setEspaceProtectionPignonCremaillereInfEgal8mm($portail['espace_entre_la_protection_du']['value']);
                   $portailAuto->setManipulableManuelCoupureCourant($portail['portail_manipulable_manuellem1']['value']);
                   $portailAuto->setManoeuvreDepannage($portail['man_uvre_de_depannage']['value']);
                   $portailAuto->setInstructionManoeuvreDepannage($portail['presence_instruction_manoeuvr']['value']);
                   $portailAuto->setDispositifCoupureElecProximite($portail['presence_dispositif_de_coupur']['value']);
                   $portailAuto->setRaccordementTerre($portail['raccordement_a_la_terre']['value']);
                   $portailAuto->setMesureTensionPhaseEtTerre($portail['mesure_tension_entre_phase_et1']['value']);
                   $portailAuto->setEclairageZoneDebattement($portail['presence_eclairage_de_zone_de']['value']);
                   $portailAuto->setFonctionnementEclairageZone($portail['fonctionnement_eclairage_zone']['value']);
                   $portailAuto->setPresenceFeuClignotantOrange($portail['presence_feu_clignotant_orang']['value']);
                   $portailAuto->setVisibiliteClignotant2Cotes($portail['visibilite_clignotant_des_2_c1']['value']);
                   $portailAuto->setPreavisClignotantMin2Sec($portail['preavis_feu_clignotant_2_sec']['value']);
                   $portailAuto->setMarquageAuSol($portail['presence_marquage_au_sol']['value']);
                   $portailAuto->setMarquageZoneRefoulement($portail['marquage_zone_de_refoulement']['value']);
                   $portailAuto->setEtatMarquage($portail['etat_du_marquage']['value']);
                   $portailAuto->setConformiteMarquageSolBandesJaunesNoirs45Deg($portail['conformite_marquage_au_sol']['value']);
                   $portailAuto->setFonctionnementCellules($portail['fonctionnement_cellules']['value']);
                   $portailAuto->setCoteEnAMm($portail['distance_entre_l_axe_cellule_']['value']);
                   $portailAuto->setCoteEnBMm($portail['cote_en_b']['value']);
                   $portailAuto->setCoteEnCMm($portail['cote_en_c']['value']);
                   $portailAuto->setCoteEnDMm($portail['cote_en_d']['value']);
                   $portailAuto->setCoteEnAPrimeMm($portail['cote_en_a_']['value']);
                   $portailAuto->setCoteEnBPrimeMm($portail['cote_en_b_']['value']);
                   $portailAuto->setCoteEnCPrimeMm($portail['cote_en_c_']['value']);
                   $portailAuto->setCoteEnDPrimeMm($portail['cote_en_d_']['value']);
                   $portailAuto->setProtectionBordPrimaire($portail['protection_bord_primaire']['value']);
                   $portailAuto->setProtectionBordSecondaire($portail['protection_bord_secondaire']['value']);
                   $portailAuto->setProtectionSurfaceVantail($portail['types_de_refoulement']['value']);
                   $portailAuto->setProtectionAirRefoulement($portail['protection_aire_de_refoulemen1']['value']);
                   $portailAuto->setPositionDesPoteaux($portail['position_des_poteaux']['value']);
                   $portailAuto->setProtectionCisaillementA($portail['protection_des_zones_de_cisai']['value']);
                   $portailAuto->setProtectionCisaillementA1($portail['protection_des_zones_de_cisai2']['value']);
                   $portailAuto->setProtectionCisaillementB($portail['protection_des_zones_de_cisai3']['value']);
                   $portailAuto->setProtectionCisaillementB1($portail['protection_des_zones_de_cisai4']['value']);
                   $portailAuto->setProtectionCisaillementC($portail['protection_des_zones_de_cisai5']['value']);
                   $portailAuto->setProtectionCisaillementC1($portail['protection_des_zones_de_cisai6']['value']);
                   $portailAuto->setProtectionCisaillementM($portail['protection_des_zones_de_cisai7']['value']);
                   $portailAuto->setZoneEcrasementFinOuvertureInf500Mm($portail['zone_d_ecrasement_fin_d_ouver']['value']);
                   $portailAuto->setDistanceZoneFinOuverture($portail['distance_de_la_zone_en_fin_d_']['value']);
                   $portailAuto->setIfExistDb($formPortail['data']['fields']['liste_clients']['columns']);

                   // tell Doctrine you want to (eventually) save the Portail (no queries yet)
                   $entityManager->persist($portailAuto);
               }
           }    
           // actually executes the queries (i.e. the INSERT query)
           $entityManager->flush();
           ?>
           We have a new portail auto or we have updated an equipment !
           <?php
           $allPortailsAutoInDatabase = $entityManager->getRepository(PortailAuto::class)->findAll();
       }
       return new JsonResponse("Portails auto en BDD : " . count($allPortailsAutoInDatabase) . "\n ", Response::HTTP_OK, [], true);
    }

    /**
     * Function to ADD new PORTAILS ENVIRONEMENT from technicians forms
     */
    #[Route('/api/forms/update/portails/environement', name: 'app_api_form_update_portails_environement', methods: ['GET'])]
    public function getEtatDesLieuxPortailsEnvironementDataOfForms(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
       // GET all technicians forms from list class PORTAILS
       $dataOfFormList  =  $formRepository->getEtatDesLieuxPortailsDataOfForms();
       $jsonDataOfFormList  = $serializer->serialize($dataOfFormList, 'json');
       $equipementsData = [];
       $eachEquipementsData = [];
       
       $allPortailsEnvironementInDatabase = $entityManager->getRepository(PortailEnvironement::class)->findAll();
       /**
        * Store all equipments resumes stored in database to an array
        */
       $allportailsEnvironementResumeInDatabase = [];
       $allNewPortailsEnvironementResume = [];
       for ($i=0; $i < count($allPortailsEnvironementInDatabase); $i++) { 
           array_push($allportailsEnvironementResumeInDatabase, $allPortailsEnvironementInDatabase[$i]->getIfexistDB());
       }

       foreach ($dataOfFormList as $formPortail) { 
           /**
           * Persist each portail, portail auto in database
           */

           foreach ($formPortail['data']['fields']['portails']['value'] as $portail) {
               array_push($allNewPortailsEnvironementResume, $formPortail['data']['fields']['liste_clients']['columns']);
               if (!in_array($formPortail['data']['fields']['liste_clients']['columns'], $allportailsEnvironementResumeInDatabase, TRUE)){

                   $portailEnvironement = new PortailEnvironement;

                   $portailEnvironement->setIdContact($formPortail['data']['fields']['ref_interne_client']['value']);
                   if (isset($formPortail['data']['fields']['id_societe_'])) {
                       $portailEnvironement->setIdSociete($formPortail['data']['fields']['id_societe_']['value']);
                   }else{
                       $portailEnvironement->setIdSociete("");
                   }
                   $portailEnvironement->setNumeroEquipement($portail['reference_equipement']['value']);
                   $portailEnvironement->setDistanceClotureExtEtVantailD1Mm($portail['distance_entre_grillage_et_va']['value']);
                   $portailEnvironement->setDimensionsMaillesGrillageExtMm($portail['dimensions_mailles_du_grillag1']['value']);
                   $portailEnvironement->setDistanceGrillageEtVantailIntD2Mm($portail['distance_entre_grillage_et_va2']['value']);
                   $portailEnvironement->setDimensionsMaillesGrillageIntMm($portail['dimensions_mailles_du_grillag2']['value']);
                   $portailEnvironement->setDimensionsMaillesTablierMm($portail['dimensions_maille_tablier_en']['value']);
                   $portailEnvironement->setDistanceBarreauxVantailMm($portail['distance_entre_les_barreaux_d']['value']);
                   $portailEnvironement->setValeursMesureesPoint1($portail['valeurs_mesurees_au_point_1']['value']);
                   $portailEnvironement->setValeursMesureesPoint2($portail['valeurs_mesurees_au_point_2']['value']);
                   $portailEnvironement->setValeursMesureesPoint3($portail['valeurs_mesurees_au_point_3']['value']);
                   $portailEnvironement->setValeursMesureesPoint4($portail['valeurs_mesurees_au_point_4']['value']);
                   $portailEnvironement->setValeursMesureesPoint5($portail['valeurs_mesurees_au_point_5']['value']);
                   $portailEnvironement->setCommentaireSuppSiNecessaire($portail['commentaire_supplementaire']['value']);
                   $portailEnvironement->setPhotoSupSiNecessaire($portail['photo4']['value']);
                   $portailEnvironement->setIfExistDb($formPortail['data']['fields']['liste_clients']['columns']);

                   // tell Doctrine you want to (eventually) save the Portail (no queries yet)
                   $entityManager->persist($portailEnvironement);
               }
           }    
           // actually executes the queries (i.e. the INSERT query)
           $entityManager->flush();
           ?>
           We have a new portail environement or we have updated an equipment !
           <?php
           $allPortailsEnvironementInDatabase = $entityManager->getRepository(PortailEnvironement::class)->findAll();
       }
       return new JsonResponse("Portails environement en BDD : " . count($allPortailsEnvironementInDatabase) . "\n ", Response::HTTP_OK, [], true);
    }

    /**
     * A REMETTRE A LA RENTRÉE BISOUS
     * 
     */
    #[Route('/api/forms/update/lists/equipements', name: 'app_api_form_update_lists_equipements', methods: ['GET','PUT'])]
    public function putUpdatesListsEquipementsFromKizeoForms(FormRepository $formRepository){
        $dataOfFormList  =  $formRepository->getDataOfFormsMaintenance();
        $equipmentsGrenoble = $formRepository->getListsEquipementsContrats38();
        
        foreach($dataOfFormList as $key=>$value){
            dump($dataOfFormList[$key]['code_agence']['value']);
            // $compteurEquipementsCheckes += count($dataOfFormList[$key]['contrat_de_maintenance']['value']);
            // dump($dataOfFormList[$key]['contrat_de_maintenance']['value']);

            switch ($dataOfFormList[$key]['code_agence']['value']) {
                case 'S50':
                    foreach ($dataOfFormList[$key]['contrat_de_maintenance']['value'] as $equipment) {
                        // dd($equipment);
                        $theEquipment = $equipment['equipement']['path'] . "\\" . $equipment['equipement']['columns'];
                        if (!in_array($theEquipment, $equipmentsGrenoble, true)) {
                            array_push($equipmentsGrenoble,  $theEquipment);
                        }
                    }
                    Request::enableHttpMethodParameterOverride(); // <-- add this line
                    $client = new Client();
                    $response = $client->request(
                        'PUT',
                        'https://forms.kizeo.com/rest/v3/lists/421883', [
                        // 'https://www.kizeoforms.com/lists/421883', [
                            'headers' => [
                                'Accept' => 'application/json',
                                'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                            ],
                            'json'=>[
                                'items' => $equipmentsGrenoble,
                            ]
                        ]
                    );

                    // $response = $client->request(
                    //     'PUT',
                    //     'https://forms.kizeo.com/rest/v3/lists/421883', [
                    //         'headers'=>[
                    //             'Accept'=>'application/json',
                    //             'Authorization'=>$_ENV['KIZEO_API_TOKEN'],
                    //         ],
                    //         'body'=>[
                    //             'items'=>$equipmentsGrenoble
                    //         ]
                    //     ]
                    // );
                    // $content = $response->getContent();
                    // $content = $response->toArray();
                    break;
                
                default:
                    return new JsonResponse('this not for our agencies', Response::HTTP_OK, [], true);
                    break;
            }
        }

        return new JsonResponse('ggggg', Response::HTTP_OK, [], true);
    }
}
