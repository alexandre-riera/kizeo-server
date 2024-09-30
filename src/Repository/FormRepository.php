<?php

namespace App\Repository;

use App\Entity\Form;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use GuzzleHttp\Client;
use PhpParser\Node\Stmt\Continue_;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<ApiForm>
 */
class FormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,  private HttpClientInterface $client)
    {
        parent::__construct($registry, Form::class);
    }

    /**
     * @return Form[] Returns an array of lists from Kizeo
     */

    public function getLists(): array
    {
            $response = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/lists', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $response->getContent();
            $content = $response->toArray();

            return $content;
    }

    /**
        * @return Form[] Returns an array of forms from Kizeo
        */
    public function getForms(): array
    {
            $response = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $response->getContent();
            $content = $response->toArray();

            return $content;
    }


    //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------- GET EQUIPMENTS LISTS FROM KIZEO --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
    /**
     * @return Form[] Returns an array with all items from all agencies equipments lists 
     */
    public function getAgencyListEquipementsFromKizeoByListId($list_id): array
    {
         $response = $this->client->request(
             'GET',
             'https://forms.kizeo.com/rest/v3/lists/' . $list_id, [
                 'headers' => [
                     'Accept' => 'application/json',
                     'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                 ],
             ]
         );
         $content = $response->getContent();
         $content = $response->toArray();
         
         $equipementsSplittedArray = [];
         // $equipementsArray = array_map(null, $content['list']['items']);
         $equipementsArray = array_map(null, $content['list']['items']);
         /* On Kizeo, all lines look like that
         *  ATEIS\CEA\SEC01|Porte sectionnelle|MISE EN SERVICE|NUMERO DE SERIE|ISEA|HAUTEUR|LARGEUR|REPERE SITE CLIENT|361|361|S50
         *
         *  And I need to sending this : 
         *  "ATEIS:ATEIS\CEA:CEA\SEC01:SEC01|Porte sectionnelle:Porte sectionnelle|MISE EN SERVICE:MISE EN SERVICE|NUMERO DE SERIE:NUMERO DE SERIE|ISEA:ISEA|HAUTEUR:HAUTEUR|LARGEUR:LARGEUR|REPERE SITE CLIENT:REPERE SITE CLIENT|361:361|361:361|S50:S50"
         */ 
         for ($i=0; $i < count($equipementsArray) ; $i++) {
             if (isset($equipementsArray[$i]) && in_array($equipementsArray[$i], $equipementsSplittedArray) == false) {
                 array_push($equipementsSplittedArray, preg_split("/[|]/", $equipementsArray[$i]));
             }
         }
 
         return $equipementsArray;
    }
 
    /**
     * @return Form[] Returns an array with all items from all agencies portails lists 
     */
    public function getAgencyListPortailsFromKizeoByListId($list_id): array
    {
         $response = $this->client->request(
             'GET',
             'https://forms.kizeo.com/rest/v3/lists/' . $list_id, [
                 'headers' => [
                     'Accept' => 'application/json',
                     'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                 ],
             ]
         );
         $content = $response->getContent();
         $content = $response->toArray();
         
         $equipementsSplittedArray = [];
         // $equipementsArray = array_map(null, $content['list']['items']);
         $equipementsArray = array_map(null, $content['list']['items']);
         /* On Kizeo, all lines look like that
         *  ATEIS\CEA\SEC01|Porte sectionnelle|MISE EN SERVICE|NUMERO DE SERIE|ISEA|HAUTEUR|LARGEUR|REPERE SITE CLIENT|361|361|S50
         *
         *  And I need to sending this : 
         *  "ATEIS:ATEIS\CEA:CEA\SEC01:SEC01|Porte sectionnelle:Porte sectionnelle|MISE EN SERVICE:MISE EN SERVICE|NUMERO DE SERIE:NUMERO DE SERIE|ISEA:ISEA|HAUTEUR:HAUTEUR|LARGEUR:LARGEUR|REPERE SITE CLIENT:REPERE SITE CLIENT|361:361|361:361|S50:S50"
         */ 
         for ($i=0; $i < count($equipementsArray) ; $i++) {
             if (isset($equipementsArray[$i]) && in_array($equipementsArray[$i], $equipementsSplittedArray) == false) {
                 array_push($equipementsSplittedArray, preg_split("/[|]/", $equipementsArray[$i]));
             }
         }
 
         return $equipementsArray;
    }

   //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------- GET MAINTENANCE EQUIPMENTS FORMS FROM KIZEO --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
   /**
    * @return Form[] Returns an array of Formulaires with class "MAINTENANCE" wich is all visites maintenance
    */
   public function getDataOfFormsMaintenance(): array
   {
        // -----------------------------   Return all forms in an array
        $allFormsArray = FormRepository::getForms();
        $allFormsArray = $allFormsArray['forms'];
        $allFormsMaintenanceArray = [];
        $allFormsMaintenanceDataArray = [];
        $allFormsPdf = [];

        // -----------------------------   Return all forms with class "MAINTENANCE"
        foreach ($allFormsArray as $key => $value) {
            if ($allFormsArray[$key]['class'] === 'MAINTENANCE') {
                
                $response = $this->client->request(
                    'POST',
                    'https://forms.kizeo.com/rest/v3/forms/' . $allFormsArray[$key]['id'] . '/data/advanced', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                        ],
                    ]
                );
                $content = $response->getContent();
                $content = $response->toArray();
                foreach ($content['data'] as $key => $value) {
                    array_push($allFormsMaintenanceArray, $value);
                }
            }
        }
        foreach ($allFormsMaintenanceArray as $key => $value) {
            $responseDataOfForm = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsMaintenanceArray[$key]['_form_id'] . '/data/' . $allFormsMaintenanceArray[$key]['_id'], [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $responseDataOfForm->getContent();
            $content = $responseDataOfForm->toArray();
            array_push($allFormsMaintenanceDataArray, $content['data']['fields']);
        }
        return $allFormsMaintenanceDataArray;
   }

    //      ----------------------------------------------------------------------------------------------------------------------
    //      ----------------------------------- GET ETAT DES LIEUX PORTAILS FORMS FROM KIZEO --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
   /**
    * @return Form[] Returns an array of Formulaires with class PORTAILS
    */
   public function getFormsAdvancedPortails(): array
   {
        $allFormsArray = FormRepository::getForms();
        $allFormsArray = $allFormsArray['forms'];
        $allDataPortailsArray = [];

        // dd($allFormsArray); // -----------------------------   Return all forms in an array

        foreach ($allFormsArray as $key => $value) {
            // if ($allFormsArray[$key]['class'] === 'PORTAILS') {
            if (str_contains($allFormsArray[$key]['name'], 'Etat des lieux')) {
                $response = $this->client->request(
                    'POST',
                    'https://forms.kizeo.com/rest/v3/forms/' . $allFormsArray[$key]['id'] . '/data/advanced', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                        ],
                    ]
                );
                $content = $response->getContent();
                $content = $response->toArray();
                foreach ($content['data'] as $key => $value) {
                    array_push($allDataPortailsArray, $value);
                }
            }
        }
        return $allDataPortailsArray;
   }

   /**
    * @return data of Form[] Returns an array of all Portails objects in all formulaires
    */
   public function getEtatDesLieuxPortailsDataOfForms(): array
   {
        $eachFormDataArray = [];
        $allFormsPortailsArray = FormRepository::getFormsAdvancedPortails();
        // ------------------------      Return arrays with portails in them from forms with class forms name begin with 'Etat des lieux'

        foreach ($allFormsPortailsArray as $key => $value) {
            
            $responseDataOfForm = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsPortailsArray[$key]['_form_id'] . '/data/' . $allFormsPortailsArray[$key]['_id'], [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $responseDataOfForm->getContent();
            $content = $responseDataOfForm->toArray();
            array_push($eachFormDataArray, $content);
            
        }
        
        return $eachFormDataArray;
   }

    /**
     * Function to iterate through list equipements to get resumes and store them in an array
     */
    public function iterateListEquipementsToGetResumes($theList){

        $arrayResumesOfTheList = [];

        for ($i=0; $i < count($theList); $i++) {
            $portailIfExistDb = $theList[$i]->getIfexistDB();
            if (!in_array($portailIfExistDb, $arrayResumesOfTheList)) {
                array_push($arrayResumesOfTheList, $portailIfExistDb);
            }
            // array_push($arrayResumesOfTheList, array_unique(preg_split("/[:|]/", $theList[$i]->getIfexistDB())));
        }
        return $arrayResumesOfTheList;
    }

    /**
     * Function to iterate through list equipements to get unique PORTAILS and return them in an array
     *  OK ! Return an array of portails by agency in local database
     */
    public function getOneTypeOfEquipementInListEquipements($equipementType, $theList){

        $arrayPortailsInTheList = [];

        for ($i=0; $i < count($theList); $i++) { 
            if ($theList[$i]->getLibelleEquipement() === $equipementType) {
                if (!in_array($theList[$i], $arrayPortailsInTheList)) {
                    array_push($arrayPortailsInTheList, $theList[$i]);
                }
            }
        }
        return $arrayPortailsInTheList;
    }


    //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------- SAVE NEW EQUIPMENTS TO LOCAL BDD --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
    /**
     * Function to create and save new equipments in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170 --- MAJ IMAGES OK
     */
    public function createAndSaveInDatabaseByAgency($equipements, $arrayResumesEquipments, $entityAgency, $entityManager){
        // Passer à la fonction les variables $equipements avec les nouveaux équipements des formulaires de maintenance, le tableau des résumés de l'agence et son entité ex: $entiteEquipementS10
        /**
        * List all additional equipments stored in individual array
        */
        foreach ($equipements['contrat_de_maintenance']['value']  as $additionalEquipment){
            // Everytime a new resume is read, we store its value in variable resume_equipement_supplementaire
            $resume_equipement_supplementaire = array_unique(preg_split("/[:|]/", $additionalEquipment['equipement']['columns']));
            
            /**
             * If resume_equipement_supplementaire value is NOT in  $allEquipementsResumeInDatabase array
             * Method used : in_array(search, inThisArray, type) 
             * type Optional. If this parameter is set to TRUE, the in_array() function searches for the search-string and specific type in the array
             */
            // if (!in_array($resume_equipement_supplementaire, $allEquipementsResumeInDatabase, TRUE) && $equipements['test_']['value'] != 'oui' ) {
            if (!in_array($resume_equipement_supplementaire, $arrayResumesEquipments, TRUE)) {
                
                /**
                 * Persist each equipement in database
                 * Save a new contrat_de_maintenance equipement in database when a technician make an update
                 */
                $equipement = new $entityAgency;
                $equipement->setIdContact($equipements['id_client_']['value']);
                $equipement->setRaisonSociale($equipements['nom_client']['value']);
                $equipement->setTest($equipements['test_']['value']);
                $equipement->setDateEnregistrement($equipements['date_et_heure1']['value']);

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
                $equipement->setLibelleEquipement(strtolower($additionalEquipment['reference7']['value']));
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
                if (isset($additionalEquipment['longueur']['value'])) {
                    $equipement->setLongueur($additionalEquipment['longueur']['value']);
                }else{
                    $equipement->setLongueur("NC");
                }
                $equipement->setPlaqueSignaletique($additionalEquipment['plaque_signaletique']['value']);

                //Anomalies en fonction du libellé de l'équipement
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
                
                if (isset($additionalEquipment['etat']['value'])) {
                    switch ($additionalEquipment['etat']['value']) {
                        case "Rien à signaler le jour de la visite. Fonctionnement ok":
                            $equipement->setStatutDeMaintenance("Vert");
                            break;
                        case "Travaux à prévoir":
                            $equipement->setStatutDeMaintenance("Orange");
                            break;
                        case "Travaux obligatoires":
                            $equipement->setStatutDeMaintenance("Rouge");
                            break;
                        case "Equipement inaccessible le jour de la visite":
                            $equipement->setStatutDeMaintenance("Inaccessible");
                            break;
                        case "Equipement à l'arrêt le jour de la visite":
                            $equipement->setStatutDeMaintenance("A l'arrêt");
                            break;
                        case "Equipement mis à l'arrêt lors de l'intervention":
                            $equipement->setStatutDeMaintenance("Rouge");
                            break;
                        case "Equipement non présent sur site":
                            $equipement->setStatutDeMaintenance("Non présent");
                            break;
                        default:
                            $equipement->setStatutDeMaintenance("NC");
                            break;
                            
                    }
                }

                $equipement->setEnMaintenance(true);
                
                // tell Doctrine you want to (eventually) save the Product (no queries yet)
                $entityManager->persist($equipement);
                
                
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
                
                echo nl2br("We have a new equipment or we have updated an equipment !");
            }else{
                echo nl2br("All equipments are already in database \n  You can go to the homepage");
                die;
            }
        }
    }

    /**
     * Function to create and save new PORTAILS from ETAT DES LIEUX PORTAILS in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170
     * Function OK
     */
    public function saveNewPortailsInDatabaseByAgency($libelle_equipement, $equipements, $arrayResumesEquipmentsInDatabase, $entityAgency, $entityManager){
       
        // Passer à la fonction les variables $equipements avec les nouveaux équipements des formulaires de maintenance, le tableau des résumés de l'agence et son entité ex: $entiteEquipementS10
        /**
        * List all additional equipments stored in individual array
        */
        foreach ($equipements['data']['fields']['portails']['value'] as $additionalEquipment){
            // dump($equipements['data']);
            // Everytime a new portail is read, we store its value in variable resume_equipement_supplementaire
            if (isset($additionalEquipment['types_equipements']['value'])) {
                # code...
                $resume_equipement_supplementaire = 
                $additionalEquipment['types_equipements']['value'] . 
                "|portail|" . 
                $additionalEquipment['types_de_fonctionnement']['value'] . 
                "|" . 
                $additionalEquipment['localisation_sur_site']['value'] . 
                "|" . 
                $additionalEquipment['annee_installation_portail_']['value'] . 
                "|" . 
                $additionalEquipment['numero_serie']['value'] . 
                "|" . 
                $additionalEquipment['marques1']['value'] . 
                "|" . 
                $additionalEquipment['dimension_hauteur_vantail']['value'] . 
                "|" . 
                $additionalEquipment['dimension_largeur_passage_uti']['value'] . 
                "|" . 
                $additionalEquipment['dimension_longueur_vantail']['value'] . 
                "|" . 
                $additionalEquipment['plaque_identification']['value'] . 
                "|" . 
                $equipements['data']['fields']['liste_clients']['value'] . 
                "|" . 
                $equipements['data']['fields']['ref_interne_client']['value'] .
                "|" . 
                $equipements['data']['fields']['id_societe_']['value']; 
            }else{
                $resume_equipement_supplementaire = 
                $additionalEquipment['reference_equipement']['value'] . 
                "|portail|" . 
                $additionalEquipment['types_de_fonctionnement']['value'] . 
                "|" . 
                $additionalEquipment['localisation_sur_site']['value'] . 
                "|" . 
                $additionalEquipment['annee_installation_portail_']['value'] . 
                "|" . 
                $additionalEquipment['numero_serie']['value'] . 
                "|" . 
                $additionalEquipment['marques1']['value'] . 
                "|" . 
                $additionalEquipment['dimension_hauteur_vantail']['value'] . 
                "|" . 
                $additionalEquipment['dimension_largeur_passage_uti']['value'] . 
                "|" . 
                $additionalEquipment['dimension_longueur_vantail']['value'] . 
                "|" . 
                $additionalEquipment['plaque_identification']['value'] . 
                "|" . 
                $equipements['data']['fields']['liste_clients']['value'] . 
                "|" . 
                $equipements['data']['fields']['ref_interne_client']['value'] .
                "|NC";
            }

            
            /**
             * If resume_equipement_supplementaire value is NOT in  $allEquipementsResumeInDatabase array
             * Method used : in_array(search, inThisArray, type) 
             * type Optional. If this parameter is set to TRUE, the in_array() function searches for the search-string and specific type in the array
             */
            
            if(!in_array($resume_equipement_supplementaire, $arrayResumesEquipmentsInDatabase, TRUE)) {
                /**
                 * Persist each equipement in database
                 * Save a new contrat_de_maintenance equipement in database when a technician make an update
                 */
                $equipement = new $entityAgency;
                $equipement->setTrigrammeTech($equipements['data']['fields']['trigramme_de_la_personne_real']['value']);
                $equipement->setTest("non");
                $equipement->setIdContact($equipements['data']['fields']['ref_interne_client']['value']);
                if (isset($equipements['data']['fields']['id_societe_']['value'])){
                    $equipement->setCodeSociete($equipements['data']['fields']['id_societe_']['value']);
                }else{
                    $equipement->setCodeSociete("NC");
                }
                $equipement->setDernièreVisite($equipements['data']['fields']['date_et_heure1']['value']);
                $equipement->setIfExistDB($resume_equipement_supplementaire);
                $equipement->setCodeAgence($equipements['data']['fields']['n_agence']['value']);
                $equipement->setRaisonSociale($equipements['data']['fields']['liste_clients']['value']);
                if (isset($additionalEquipment['types_equipements']['value'])){
                    $equipement->setNumeroEquipement($additionalEquipment['types_equipements']['value']);
                }else{
                    $equipement->setNumeroEquipement($additionalEquipment['reference_equipement']['value']);
                }
                $equipement->setRepereSiteClient($additionalEquipment['localisation_sur_site']['value']);
                $equipement->setEtat($additionalEquipment['etat_general_equipement1']['value']);
                $equipement->setMarque($additionalEquipment['marques1']['value']);
                $equipement->setNumeroDeSerie($additionalEquipment['numero_serie']['value']);
                $equipement->setMiseEnService($additionalEquipment['annee_installation_portail_']['value']);
                $equipement->setLibelleEquipement("portail");
                $equipement->setModeFonctionnement($additionalEquipment['types_de_fonctionnement']['value']);
                $equipement->setLargeur($additionalEquipment['dimension_largeur_passage_uti']['value']);
                $equipement->setLongueur($additionalEquipment['dimension_longueur_vantail']['value']);
                $equipement->setHauteur($additionalEquipment['dimension_hauteur_vantail']['value']);
                $equipement->setPresenceCarnetEntretien($additionalEquipment['presence_carnet_entretien']['value']);
                $equipement->setEtatDesLieuxFait(true);
                
                $entityManager->persist($equipement);
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
                
                echo nl2br("Portails are updaated in BDD in table equipementS.. !");
            }
        }
    }


    //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------- UPLOAD NEW EQUIPMENTS LIST TO KIZEO --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
    /**
     * Function to upload and save list agency with new records from maintenance formulaires to Kizeo --- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    public function uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $agencyEquipments, $agencyListId){
        foreach ($dataOfFormList[$key]['contrat_de_maintenance']['value'] as $equipment) {
            
            $theEquipment = $equipment['equipement']['path'] . "\\" . $equipment['equipement']['columns'];
            if (!in_array($theEquipment, $agencyEquipments, true)) {
                array_push($agencyEquipments,  $theEquipment);
            }
        }
        Request::enableHttpMethodParameterOverride(); // <-- add this line
        $client = new Client();
        $response = $client->request(
            'PUT',
            'https://forms.kizeo.com/rest/v3/lists/' . $agencyListId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                ],
                'json'=>[
                    'items' => $agencyEquipments,
                ]
            ]
        );
    }

    /**
     * Function to upload and save list agency with new records from ETAT DES LIEUX PORTAILS formulaires to Kizeo --- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    // public function uploadListAgencyEtatDesLieuxPortailsWithNewRecordsOnKizeo($dataOfFormList, $key, $agencyEquipments, $agencyListId){
    //     // Mettre à jour pour les nouveaux portails des états des lieux
    //     foreach ($dataOfFormList[$key]['contrat_de_maintenance']['value'] as $equipment) {
    //         // Recréer le path avec pour modèle celui de la liste portails
    //         $theEquipment = $equipment['equipement']['path'] . "\\" . $equipment['equipement']['columns'];
    //         if (!in_array($theEquipment, $agencyEquipments, true)) {
    //             array_push($agencyEquipments,  $theEquipment);
    //         }
    //     }
    //     Request::enableHttpMethodParameterOverride(); // <-- add this line
    //     $client = new Client();
    //     $response = $client->request(
    //         'PUT',
    //         'https://forms.kizeo.com/rest/v3/lists/' . $agencyListId, [
    //             'headers' => [
    //                 'Accept' => 'application/json',
    //                 'Authorization' => $_ENV["KIZEO_API_TOKEN"],
    //             ],
    //             'json'=>[
    //                 'items' => $agencyEquipments,
    //             ]
    //         ]
    //     );
    // }

    //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------- SAVE BASE EQUIPMENTS LIST TO LOCAL BDD --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
    /**
     * Function to save base equipments lists in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    public function saveEquipmentsListByAgencyOnLocalDatabase($listAgency, $entityAgency, $entityManager){
        $listAgencySplitted = [];
        
        foreach ($listAgency as $equipement) {
            array_push($listAgencySplitted, preg_split("/[:|]/", $equipement));
        }

        foreach ($listAgencySplitted as $equipements){
            $equipement = new $entityAgency;
            $equipement->setTest("Non");
            $equipement->setIdContact($equipements[18]);
            $equipement->setRaisonSociale($equipements[0]);
            $equipement->setCodeSociete($equipements[20]);
            $equipement->setCodeAgence($equipements[22]);

            $equipement->setNumeroEquipement($equipements[3]);
            $equipement->setLibelleEquipement(strtolower($equipements[4]));
            $equipement->setRepereSiteClient($equipements[2]);
            $equipement->setMiseEnService($equipements[6]);
            $equipement->setNumeroDeSerie($equipements[8]);
            $equipement->setMarque($equipements[10]);

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($equipement);
            
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            
        }
    }


    //      ----------------------------------------------------------------------------------------------------------------------
    //      ---------------------------------------------  SAVE PDF STANDARD FROM KIZEO --------------------------------------
    //      ----------------------------------------------------------------------------------------------------------------------
    /**
     * Function to save PDF with and without pictures in directories on O2switch  -------------- FUNCTIONNAL -------
     */
    public function saveEquipementPdfInPublicFolder(){
        // Récupérer les fichiers PDF dans un tableau
        // -----------------------------   Return all forms in an array
        $allFormsArray = FormRepository::getForms();
        $allFormsArray = $allFormsArray['forms'];
        $allFormsMaintenanceArray = [];
        $allFormsPdf = [];

        // -----------------------------   Return all forms with class "MAINTENANCE"
        foreach ($allFormsArray as $key => $value) {
            if ($allFormsArray[$key]['class'] === 'MAINTENANCE') {
                
                $response = $this->client->request(
                    'POST',
                    'https://forms.kizeo.com/rest/v3/forms/' . $allFormsArray[$key]['id'] . '/data/advanced', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                        ],
                    ]
                );
                $content = $response->getContent();
                $content = $response->toArray();
                foreach ($content['data'] as $key => $value) {
                    array_push($allFormsMaintenanceArray, $value);
                }
            }
        }
        // GET available exports from form_id

        foreach ($allFormsMaintenanceArray as $key => $value) {
            $responseExportsAvailable = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsMaintenanceArray[$key]['_form_id'] . '/exports', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $contentExportsAvailable = $responseExportsAvailable->getContent();
            $contentExportsAvailable = $responseExportsAvailable->toArray();
            
            // ------------------------------------------      GET to receive PDF FROM FORMS FROM TECHNICIANS WHITH PICTURES
            $responseData = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsMaintenanceArray[$key]['_form_id'] . '/data/' . $allFormsMaintenanceArray[$key]['_id'] . '/pdf', [
                    'headers' => [
                        'Accept' => 'application/pdf',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $responseData->getContent();
            dump('GET to receive PDF FROM FORMS FROM TECHNICIANS WHITH PICTURES');
            if (!file_exists('Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . substr($allFormsMaintenanceArray[$key]['date_et_heure1'], 0, 4))) {
                # code...
                switch (str_contains($allFormsMaintenanceArray[$key]['nom_client'], '/')) {
                    case false:
                        mkdir('Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . substr($allFormsMaintenanceArray[$key]['date_et_heure1'], 0, 4), 0777, true);
                        file_put_contents( 'Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . substr($allFormsMaintenanceArray[$key]['date_et_heure1'], 0, 4) . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        break;
                
                    case true:
                        $nomClient = $allFormsMaintenanceArray[$key]['nom_client'];
                        $nomClientClean = str_replace("/", "", $nomClient);
                        if (!file_exists('Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' . $nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'])){
                            mkdir('Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' . $nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . substr($allFormsMaintenanceArray[$key]['date_et_heure1'], 0, 4), 0777, true);
                            file_put_contents('Maintenance/' . $allFormsMaintenanceArray[$key]['code_agence']  . '/' .  $nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . substr($allFormsMaintenanceArray[$key]['date_et_heure1'], 0, 4) . '/' . $nomClientClean . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        }
                        break;
    
                    default:
                        dump('Nom en erreur:   ' . $allFormsMaintenanceArray[$key]['nom_client']);
                        break;
                }
            }
        }
        return $allFormsPdf;
    } 
    /**
     * Function to save PDF with and without pictures in directories on O2switch  -------------- FUNCTIONNAL -------
     */
    public function savePortailsPdfInPublicFolder(){
        // Récupérer les fichiers PDF dans un tableau
        // -----------------------------   Return all forms in an array
        $allFormsArray = FormRepository::getForms();
        $allFormsArray = $allFormsArray['forms'];
        $allFormsPortailsArray = [];
        $allFormsPdf = [];

        // -----------------------------   Return all forms with class "MAINTENANCE"
        foreach ($allFormsArray as $key => $value) {
            if ($allFormsArray[$key]['class'] === 'PORTAILS') {
                
                $response = $this->client->request(
                    'POST',
                    'https://forms.kizeo.com/rest/v3/forms/' . $allFormsArray[$key]['id'] . '/data/advanced', [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                        ],
                    ]
                );
                $content = $response->getContent();
                $content = $response->toArray();
                foreach ($content['data'] as $key => $value) {
                    array_push($allFormsPortailsArray, $value);
                }
            }
        }
        // GET available exports from form_id

        foreach ($allFormsPortailsArray as $key => $value) {
            $responseExportsAvailable = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsPortailsArray[$key]['_form_id'] . '/exports', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $contentExportsAvailable = $responseExportsAvailable->getContent();
            $contentExportsAvailable = $responseExportsAvailable->toArray();
            
            // ------------------------------------------      GET to receive PDF FROM FORMS FROM TECHNICIANS WHITH PICTURES
            $responseData = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsPortailsArray[$key]['_form_id'] . '/data/' . $allFormsPortailsArray[$key]['_id'] . '/pdf', [
                    'headers' => [
                        'Accept' => 'application/pdf',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]
            );
            $content = $responseData->getContent();
            dump('GET to receive PDF FROM FORMS FROM TECHNICIANS WHITH PICTURES');
            if (!file_exists('ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $allFormsPortailsArray[$key]['liste_clients'] . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'])) {
                # code...
                switch (str_contains($allFormsPortailsArray[$key]['liste_clients'], '/')) {
                    case false:
                        mkdir('ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $allFormsPortailsArray[$key]['liste_clients'] . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'] . '/' . substr($allFormsPortailsArray[$key]['date_et_heure1'], 0, 4), 0777, true);
                        file_put_contents('ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $allFormsPortailsArray[$key]['liste_clients'] . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'] . '/' . substr($allFormsPortailsArray[$key]['date_et_heure1'], 0, 4) . '/' . $allFormsPortailsArray[$key]['liste_clients'] . '-' . $allFormsPortailsArray[$key]['n_agence']  . '-' . $allFormsPortailsArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        break;
                
                    case true:
                        $nomClient = $allFormsPortailsArray[$key]['liste_clients'];
                        $nomClientClean = str_replace("/", "", $nomClient);
                        if (!file_exists('ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $nomClientClean . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'])){
                            mkdir('ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $nomClientClean . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'], 0777, true);
                            file_put_contents( 'ETAT_DES_LIEUX_PORTAILS/' . $allFormsPortailsArray[$key]['n_agence'] . '/' . $nomClientClean . ' - ' .  $allFormsPortailsArray[$key]['date_et_heure1'] . '/' . date_format($allFormsPortailsArray[$key]['date_et_heure1'], 'Y') . '/' . $nomClientClean . '-' . $allFormsPortailsArray[$key]['n_agence']  . '-' . $allFormsPortailsArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        }
                        break;
    
                    default:
                        dump('Nom en erreur:   ' . $allFormsPortailsArray[$key]['liste_clients']);
                        break;
                }
            }
        }
        return $allFormsPdf;
    } 
}
