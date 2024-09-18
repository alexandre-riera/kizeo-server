<?php

namespace App\Controller;

use App\Entity\Form;
use GuzzleHttp\Client;
use App\Entity\Portail;
use App\Entity\Equipement;
use App\Entity\PortailAuto;
use App\Entity\EquipementS10;
use App\Entity\EquipementS40;
use App\Entity\EquipementS50;
use App\Entity\EquipementS60;
use App\Entity\EquipementS70;
use App\Entity\EquipementS80;
use App\Entity\EquipementS100;
use App\Entity\EquipementS120;
use App\Entity\EquipementS130;
use App\Entity\EquipementS140;
use App\Entity\EquipementS150;
use App\Entity\EquipementS160;
use App\Entity\EquipementS170;
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
     * HomePage route to avoid Symfony loading default page
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
        $formList  =  $formRepository->getAgencyListEquipementsFromKizeoByListId(414025);

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
        
        return new JsonResponse("Formulaires parc client sur API KIZEO : "  . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
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
        
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }

    

    /**
     * Function to ADD new equipments from technicians forms MAINTENANCE from formulaires Visite maintenance --------------- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    #[Route('/api/forms/update/maintenance', name: 'app_api_form_update', methods: ['GET'])]
    public function getDataOfFormsMaintenance(FormRepository $formRepository,EntityManagerInterface $entityManager)
        {
        $entiteEquipementS10 = new EquipementS10;
        $entiteEquipementS40 = new EquipementS40;
        $entiteEquipementS50 = new EquipementS50;
        $entiteEquipementS60 = new EquipementS60;
        $entiteEquipementS70 = new EquipementS70;
        $entiteEquipementS80 = new EquipementS80;
        $entiteEquipementS100 = new EquipementS100;
        $entiteEquipementS120 = new EquipementS120;
        $entiteEquipementS130 = new EquipementS130;
        $entiteEquipementS140 = new EquipementS140;
        $entiteEquipementS150 = new EquipementS150;
        $entiteEquipementS160 = new EquipementS160;
        $entiteEquipementS170 = new EquipementS170;

        $formRepository->savePdfOnO2switch();
        
        // GET all technicians forms formulaire Visite maintenance
        $dataOfFormList  =  $formRepository->getDataOfFormsMaintenance();
        
        // --------------------------------------                       Call function iterate by list equipments -------------------------------------------
        $allResumesGroupEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS10::class)->findAll());
        $allResumesStEtienneEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS40::class)->findAll());
        $allResumesGrenobleEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS50::class)->findAll());
        $allResumesLyonEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS60::class)->findAll());
        $allResumesBordeauxEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS70::class)->findAll());
        $allResumesParisNordEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS80::class)->findAll());
        $allResumesMontpellierEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS100::class)->findAll());
        $allResumesHautsDeFranceEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS120::class)->findAll());
        $allResumesToulouseEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS130::class)->findAll());
        $allResumesSmpEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS140::class)->findAll());
        $allResumesSogefiEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS150::class)->findAll());
        $allResumesRouenEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS160::class)->findAll());
        $allResumesRennesEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS170::class)->findAll());

        // -------------------------------------- REPRENDRE A CODER A PARTIR DE LA -- Tous les Resumes des equipements des agences au dessus sont bons ----------------

        // $allEquipementsInDatabase = [];
        /**
         * Store all equipments split resumes stored in database to $allEquipementsResumeInDatabase array
         * 

         * Créer une fonction pour push les resume des equipements selon leur agence  --------------    CA C'EST BON, FONCTION CRÉÉ AU DESSUS FONCTIONELLE ---------------
         */
        // $allEquipementsResumeInDatabase = [];
        // for ($i=0; $i < count($allEquipementsInDatabase); $i++) { 
        //     array_push($allEquipementsResumeInDatabase, array_unique(preg_split("/[:|]/", $allEquipementsInDatabase[$i]->getIfexistDB())));
        // }
        
        foreach ($dataOfFormList as $equipements){
            dump("Je recupere les nouveaux formulaires maintenance de toutes les agences du repository dans $ dataofFormList");
            // ----------------------------------------------------------   
            // IF code_agence d'$equipements = S50 ou S100 ou etc... on boucle sur ses équipements supplémentaires
            // ----------------------------------------------------------
            switch ($equipements['code_agence']['value']) {
                // Passer à la fonction createAndSaveInDatabaseByAgency()
                // les variables $equipements avec les nouveaux équipements des formulaires de maintenance, le tableau des résumés de l'agence et son entité ex: $entiteEquipementS10
                case 'S10':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesGroupEquipementsInDatabase, $entiteEquipementS10, $entityManager);
                    break;
                
                case 'S40':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesStEtienneEquipementsInDatabase, $entiteEquipementS40, $entityManager);
                    break;
                
                case 'S50':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesGrenobleEquipementsInDatabase, $entiteEquipementS50, $entityManager);
                    break;
                
                
                case 'S60':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesLyonEquipementsInDatabase, $entiteEquipementS60, $entityManager);
                    break;
                
                
                case 'S70':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesBordeauxEquipementsInDatabase, $entiteEquipementS70, $entityManager);
                    break;
                
                
                case 'S80':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesParisNordEquipementsInDatabase, $entiteEquipementS80, $entityManager);
                    break;
                
                
                case 'S100':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesMontpellierEquipementsInDatabase, $entiteEquipementS100, $entityManager);
                    break;
                
                
                case 'S120':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesHautsDeFranceEquipementsInDatabase, $entiteEquipementS120, $entityManager);
                    break;
                
                
                case 'S130':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesToulouseEquipementsInDatabase, $entiteEquipementS130, $entityManager);
                    break;
                
                
                case 'S140':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesSmpEquipementsInDatabase, $entiteEquipementS140, $entityManager);
                    break;
                
                
                case 'S150':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesSogefiEquipementsInDatabase, $entiteEquipementS150, $entityManager);
                    break;
                
                
                case 'S160':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesRouenEquipementsInDatabase, $entiteEquipementS160, $entityManager);
                    break;
                
                
                case 'S170':
                    $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesRennesEquipementsInDatabase, $entiteEquipementS170, $entityManager);
                    break;
                
                default:
                    dump('Le code agence n\'est pas prévu dans le code');
                    break;
            }
            
        }
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($dataOfFormList) . " | Equipements en BDD : " , Response::HTTP_OK, [], true);
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
     * UPDATE LIST OF EQUIPMENTS ON KIZEO AND FLUSH NEW EQUIPMENTS IN LOCAL DATABASE    --------------- OK POUR TOUTES LES AGENCES DE S10 à S170
     * 
     */
    #[Route('/api/forms/update/lists/equipements', name: 'app_api_form_update_lists_equipements', methods: ['GET','PUT'])]
    public function putUpdatesListsEquipementsFromKizeoForms(FormRepository $formRepository){
        $dataOfFormList  =  $formRepository->getDataOfFormsMaintenance();
        // GET equipments des agences de Grenoble, Paris et Montpellier en apellant la fonction getAgencyListEquipementsFromKizeoByListId($list_id) avec leur ID de list sur KIZEO
        // $equipmentsGroup = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        $equipmentsGrenoble = $formRepository->getAgencyListEquipementsFromKizeoByListId(414025);
        // $equipmentsLyon = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsBordeaux = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        $equipmentsParis = $formRepository->getAgencyListEquipementsFromKizeoByListId(421993);
        $equipmentsMontpellier = $formRepository->getAgencyListEquipementsFromKizeoByListId(423853);
        // $equipmentsHautsDeFrance = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsToulouse = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsSmp = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsSogefi = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsRouen = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        // $equipmentsRennes = $formRepository->getAgencyListEquipementsFromKizeoByListId();
        
        foreach($dataOfFormList as $key=>$value){

            switch ($dataOfFormList[$key]['code_agence']['value']) {
                // Fonction uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList,$key,$agencyEquipments,$agencyListId)
                // case 'S10':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsGroup, );
                //     dump('Uploads S10 OK');
                //     break;
                // case 'S40':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsStEtienne, );
                //     dump('Uploads S40 OK');
                //     break;
                case 'S50':
                    $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsGrenoble, 414025);
                    dump('Uploads S50 OK');
                    break;
                // case 'S60':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsLyon, );
                //     dump('Uploads S60 OK');
                //     break;
                // case 'S70':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsBordeaux, );
                //     dump('Uploads S70 OK');
                //     break;
                
                case 'S80':
                    $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsParis, 421993);
                    dump('Uploads for S80 OK');
                    break;
                
                case 'S100':
                    $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsMontpellier, 423852);
                    dump('Uploads for S100 OK');
                    break;
                
                // case 'S120':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsHautsDeFrance, );
                //     dump('Uploads for S120 OK');
                //     break;
                
                // case 'S130':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsToulouse, );
                //     dump('Uploads for S130 OK');
                //     break;
                
                // case 'S140':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsSmp, );
                //     dump('Uploads for S140 OK');
                //     break;
                
                // case 'S150':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsSogefi, );
                //     dump('Uploads for S150 OK');
                //     break;
                
                // case 'S160':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsRouen, );
                //     dump('Uploads for S160 OK');
                //     break;
                
                // case 'S170':
                //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $equipmentsRennes, );
                //     dump('Uploads for S170 OK');
                //     break;
                
                default:
                    return new JsonResponse('this not for our agencies', Response::HTTP_OK, [], true);
                    break;
            }
        }

        // ----------------------                 Save new equipements in database from all agencies
        

        // return new JsonResponse('La mise à jour sur KIZEO s\'est bien déroulée !', Response::HTTP_OK, [], true);
        return $this->redirectToRoute('app_api_form_update');
    }

    
    /**
     * SAVE LISTS OF EQUIPMENTS IN LOCAL DATABASE BY AGENCY LIST --------  JUST ONE TIME AT THE BEGGINING FOR EACH LIST  --------- IT TAKE 24 MINUTES --- About 8 or 10 minutes by equipments list
     * Prefer uploads excel list equipments in database by phpmyadmin
     */
    #[Route('/api/upload/list/equipements/grenoble', name: 'app_api_upload_list_equipements_grenoble', methods: ['GET'])]
    public function saveAllGrenobleListEquipmentsInDatabase(FormRepository $formRepository, EntityManagerInterface $entityManager){
        // $entiteEquipementS10 = new EquipementS10;
        // $entiteEquipementS40 = new EquipementS40;
        $entiteEquipementS50 = new EquipementS50;
        // $entiteEquipementS60 = new EquipementS60;
        // $entiteEquipementS70 = new EquipementS70;
        // $entiteEquipementS80 = new EquipementS80;
        // $entiteEquipementS100 = new EquipementS100;
        // $entiteEquipementS120 = new EquipementS120;
        // $entiteEquipementS130 = new EquipementS130;
        // $entiteEquipementS140 = new EquipementS140;
        // $entiteEquipementS150 = new EquipementS150;
        // $entiteEquipementS160 = new EquipementS160;
        // $entiteEquipementS170 = new EquipementS170;

        $formRepository->saveEquipmentsListByAgencyOnLocalDatabase($formRepository->getAgencyListEquipementsFromKizeoByListId(414025), $entiteEquipementS50, $entityManager);

        return $this->redirectToRoute('app_api_upload_list_equipements_paris');
    }
    #[Route('/api/upload/list/equipements/paris', name: 'app_api_upload_list_equipements_paris', methods: ['GET'])]
    public function saveAllParisListEquipmentsInDatabase(FormRepository $formRepository, EntityManagerInterface $entityManager){
        $entiteEquipementS80 = new EquipementS80;
        $formRepository->saveEquipmentsListByAgencyOnLocalDatabase($formRepository->getAgencyListEquipementsFromKizeoByListId(421993), $entiteEquipementS80, $entityManager);
       
        return $this->redirectToRoute('app_api_upload_list_equipements_montpellier');
    }
    #[Route('/api/upload/list/equipements/montpellier', name: 'app_api_upload_list_equipements_montpellier', methods: ['GET'])]
    public function saveAllMontpellierListEquipmentsInDatabase(FormRepository $formRepository, EntityManagerInterface $entityManager){
        $entiteEquipementS100 = new EquipementS100;
        $formRepository->saveEquipmentsListByAgencyOnLocalDatabase($formRepository->getAgencyListEquipementsFromKizeoByListId(423852), $entiteEquipementS100, $entityManager);
       
        return new JsonResponse("All lists have been uploaded !", Response::HTTP_OK, [], true);
    }

    // Ajouter ici la route de l'agence quand Margaux créera les listes équipements des agences
    // #[Route('/api/upload/list/equipements/nom_de_l_agence', name: 'app_api_upload_list_equipements_nom_de_l_agence', methods: ['GET'])]
}
