<?php

namespace App\Repository;

use App\Entity\Form;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use GuzzleHttp\Client;
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

            //    ----------------------- LES PORTAILS  -----------------------------------  
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
        // ------------------------      Return 24 arrays with portails in them from 8 forms with class PORTAILS

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
            array_push($arrayResumesOfTheList, array_unique(preg_split("/[:|]/", $theList[$i]->getIfexistDB())));
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
                //    ----------------  Enregistrement des photos ---------------------------
                if (isset($additionalEquipment['photo_plaque']['value'])) {
                    $equipement->setPhotoPlaque($additionalEquipment['photo_plaque']['value']);
                }else{
                    $equipement->setPhotoPlaque("");
                }
                
                if (isset($additionalEquipment['photo_choc']['value'])) {
                    $equipement->setPhotoChoc($additionalEquipment['photo_choc']['value']);
                }else{
                    $equipement->setPhotoChoc("");
                }
                if (isset($additionalEquipment['photo_choc_montant']['value'])) {
                    $equipement->setPhotoChocMontant($additionalEquipment['photo_choc_montant']['value']);
                }else{
                    $equipement->setPhotoChocMontant("");
                }
                if (isset($additionalEquipment['photo_panneau_intermediaire_i']['value'])) {
                    $equipement->setPhotoPanneauIntermediaire($additionalEquipment['photo_panneau_intermediaire_i']['value']);
                }else{
                    $equipement->setPhotoPanneauIntermediaire("");
                }
                if (isset($additionalEquipment['photo_panneau_bas_inter_ext']['value'])) {
                    $equipement->setPhotoPanneauBasInterExt($additionalEquipment['photo_panneau_bas_inter_ext']['value']);
                }else{
                    $equipement->setPhotoPanneauBasInterExt("");
                }
                if (isset($additionalEquipment['photo_lame_basse_int_ext']['value'])) {
                    $equipement->setPhotoLameBasseIntExt($additionalEquipment['photo_lame_basse_int_ext']['value']);
                }else{
                    $equipement->setPhotoLameBasseIntExt("");
                }
                if (isset($additionalEquipment['photo_lame_intermediaire_int']['value'])) {
                    $equipement->setPhotoLameIntermediaireInt($additionalEquipment['photo_lame_intermediaire_int']['value']);
                }else{
                    $equipement->setPhotoLameIntermediaireInt("");
                }
                if (isset($additionalEquipment['photo_environnement_equipement1']['value'])) {
                    $equipement->setPhotoEnvironnementEquipement($additionalEquipment['photo_environnement_equipement1']['value']);
                }else{
                    $equipement->setPhotoEnvironnementEquipement("");
                }
                if (isset($additionalEquipment['photo_bache']['value'])) {
                    $equipement->setPhotoBache($additionalEquipment['photo_bache']['value']);
                }else{
                    $equipement->setPhotoBache("");
                }
                if (isset($additionalEquipment['photo_marquage_au_sol_']['value'])) {
                    $equipement->setPhotoMarquageAuSol($additionalEquipment['photo_marquage_au_sol_']['value']);
                }else{
                    $equipement->setPhotoMarquageAuSol("");
                }
                if (isset($additionalEquipment['photo_environnement_eclairage']['value'])) {
                    $equipement->setPhotoEnvironnementEclairage($additionalEquipment['photo_environnement_eclairage']['value']);
                }else{
                    $equipement->setPhotoEnvironnementEclairage("");
                }
                if (isset($additionalEquipment['photo_coffret_de_commande']['value'])) {
                    $equipement->setPhotoCoffretDeCommande($additionalEquipment['photo_coffret_de_commande']['value']);
                }else{
                    $equipement->setPhotoCoffretDeCommande("");
                }
                if (isset($additionalEquipment['photo_carte']['value'])) {
                    $equipement->setPhotoCarte($additionalEquipment['photo_carte']['value']);
                }else{
                    $equipement->setPhotoCarte("");
                }
                if (isset($additionalEquipment['photo_rail']['value'])) {
                    $equipement->setPhotoRail($additionalEquipment['photo_rail']['value']);
                }else{
                    $equipement->setPhotoRail("");
                }
                if (isset($additionalEquipment['photo_equerre_rail']['value'])) {
                    $equipement->setPhotoEquerreRail($additionalEquipment['photo_equerre_rail']['value']);
                }else{
                    $equipement->setPhotoEquerreRail("");
                }
                if (isset($additionalEquipment['photo_fixation_coulisse']['value'])) {
                    $equipement->setPhotoFixationCoulisse($additionalEquipment['photo_fixation_coulisse']['value']);
                }else{
                    $equipement->setPhotoFixationCoulisse("");
                }
                if (isset($additionalEquipment['photo_moteur']['value'])) {
                    $equipement->setPhotoMoteur($additionalEquipment['photo_moteur']['value']);
                }else{
                    $equipement->setPhotoMoteur("");
                }
                if (isset($additionalEquipment['photo_deformation_plateau']['value'])) {
                    $equipement->setPhotoDeformationPlateau($additionalEquipment['photo_deformation_plateau']['value']);
                }else{
                    $equipement->setPhotoDeformationPlateau("");
                }
                if (isset($additionalEquipment['photo_deformation_plaque']['value'])) {
                    $equipement->setPhotoDeformationPlaque($additionalEquipment['photo_deformation_plaque']['value']);
                }else{
                    $equipement->setPhotoDeformationPlaque("");
                }
                if (isset($additionalEquipment['photo_deformation_structure']['value'])) {
                    $equipement->setPhotoDeformationStructure($additionalEquipment['photo_deformation_structure']['value']);
                }else{
                    $equipement->setPhotoDeformationStructure("");
                }
                if (isset($additionalEquipment['photo_deformation_chassis']['value'])) {
                    $equipement->setPhotoDeformationChassis($additionalEquipment['photo_deformation_chassis']['value']);
                }else{
                    $equipement->setPhotoDeformationChassis("");
                }
                if (isset($additionalEquipment['photo_deformation_levre']['value'])) {
                    $equipement->setPhotoDeformationLevre($additionalEquipment['photo_deformation_levre']['value']);
                }else{
                    $equipement->setPhotoDeformationLevre("");
                }
                if (isset($additionalEquipment['photo_fissure_cordon']['value'])) {
                    $equipement->setPhotoFissureCordon($additionalEquipment['photo_fissure_cordon']['value']);
                }else{
                    $equipement->setPhotoFissureCordon("");
                }
                if (isset($additionalEquipment['photo_joue']['value'])) {
                    $equipement->setPhotoJoue($additionalEquipment['photo_joue']['value']);
                }else{
                    $equipement->setPhotoJoue("");
                }
                if (isset($additionalEquipment['photo_butoir']['value'])) {
                    $equipement->setPhotoButoir($additionalEquipment['photo_butoir']['value']);
                }else{
                    $equipement->setPhotoButoir("");
                }
                if (isset($additionalEquipment['photo_vantail']['value'])) {
                    $equipement->setPhotoVantail($additionalEquipment['photo_vantail']['value']);
                }else{
                    $equipement->setPhotoVantail("");
                }
                if (isset($additionalEquipment['photo_linteau']['value'])) {
                    $equipement->setPhotoLinteau($additionalEquipment['photo_linteau']['value']);
                }else{
                    $equipement->setPhotoLinteau("");
                }
                if (isset($additionalEquipment['photo_barriere']['value'])) {
                    $equipement->setPhotoBariere($additionalEquipment['photo_barriere']['value']);
                }else{
                    $equipement->setPhotoBarriere("");
                }
                if (isset($additionalEquipment['photo_tourniquet']['value'])) {
                    $equipement->setPhotoTourniquet($additionalEquipment['photo_tourniquet']['value']);
                }else{
                    $equipement->setPhotoTourniquet("");
                }
                if (isset($additionalEquipment['photo_sas']['value'])) {
                    $equipement->setPhotoSas($additionalEquipment['photo_sas']['value']);
                }else{
                    $equipement->setPhotoSas("");
                }
                if (isset($additionalEquipment['photo_marquage_au_sol']['value'])) {
                    $equipement->setPhotoMarquageAuSolPortail($additionalEquipment['photo_marquage_au_sol']['value']);
                }else{
                    $equipement->setPhotoMarquageAuSolPortail("");
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
    /**
     * Function to create and save new PORTAILS from ETAT DES LIEUX PORTAILS in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170 --- MAJ IMAGES OK
     */
    public function createAndSavePortailsInDatabaseByAgency($equipements, $arrayResumesEquipments, $entityAgency, $entityManager){
        // Passer à la fonction les variables $equipements avec les nouveaux équipements des formulaires de maintenance, le tableau des résumés de l'agence et son entité ex: $entiteEquipementS10
        /**
        * List all additional equipments stored in individual array
        */
        foreach ($equipements['data']['fields']['portails']['value']  as $additionalEquipment){
            // Everytime a new resume is read, we store its value in variable resume_equipement_supplementaire
            $resume_equipement_supplementaire = 
            $equipements['data']['fields']['liste_clients']['value'] . 
            "\\" . 
            $additionalEquipment['reference_equipement']['value'] . 
            "|portail|" . 
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
            $additionalEquipment['ref_interne_client']['value'] . 
            "|" . 
            $additionalEquipment['id_societe_']['value'] . 
            "|" . 
            $additionalEquipment['n_agence']['value'];

            
            /**
             * If resume_equipement_supplementaire value is NOT in  $allEquipementsResumeInDatabase array
             * Method used : in_array(search, inThisArray, type) 
             * type Optional. If this parameter is set to TRUE, the in_array() function searches for the search-string and specific type in the array
             */
            
            if(!in_array($resume_equipement_supplementaire, $arrayResumesEquipments, TRUE)) {
                
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

    /**
     * Function to upload and save list agency with new records from maintenance formulaires --- OK POUR TOUTES LES AGENCES DE S10 à S170
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
     * Function to save and save new equipments in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170
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
            if (!file_exists($allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'])) {
                # code...
                switch (str_contains($allFormsMaintenanceArray[$key]['nom_client'], '/')) {
                    case false:
                        mkdir($allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'], 0777, true);
                        file_put_contents( $allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        break;
                
                    case true:
                        $nomClient = $allFormsMaintenanceArray[$key]['nom_client'];
                        $nomClientClean = str_replace("/", "", $nomClient);
                        if (!file_exists($nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'])){
                            mkdir($nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'], 0777, true);
                            file_put_contents( $nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . $nomClientClean . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
                        }
                        break;
    
                    default:
                        dump('Nom en erreur:   ' . $allFormsMaintenanceArray[$key]['nom_client']);
                        break;
                }
            }


            // ------------------------------------------      POST to receive PDF FROM FORMS FROM TECHNICIANS WHITHOUT PICTURES
            // $responseData = $this->client->request(
            //     'POST',
            //     'https://forms.kizeo.com/rest/v3/forms/' .  $allFormsMaintenanceArray[$key]['_form_id'] . '/multiple_data/exports/' . $contentExportsAvailable['exports'][0]['id'] . '/pdf', [
            //         'headers' => [
            //             'Accept' => 'application/pdf',
            //             'Authorization' => $_ENV["KIZEO_API_TOKEN"],
            //         ],
            //         'body' => [
            //             'data_ids' => [
            //                 $allFormsMaintenanceArray[$key]['_id']
            //             ]
            //         ],
            //     ]
            // );
            // $content = $responseData->getContent();

            // dump('Nom client entrant dans le switch : ' . $allFormsMaintenanceArray[$key]['nom_client']);
            // switch (str_contains($allFormsMaintenanceArray[$key]['nom_client'], '/')) {
            //     case false:
            //         mkdir($allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'], 0777, true);
            //         file_put_contents( $allFormsMaintenanceArray[$key]['nom_client'] . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . $allFormsMaintenanceArray[$key]['nom_client'] . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
            //         break;
            
            //     case true:
            //         $nomClient = $allFormsMaintenanceArray[$key]['nom_client'];
            //         $nomClientClean = str_replace("/", "", $nomClient);
            //         mkdir($nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'], 0777, true);
            //         file_put_contents( $nomClientClean . ' - ' .  $allFormsMaintenanceArray[$key]['date_et_heure1'] . '/' . $nomClientClean . '-' . $allFormsMaintenanceArray[$key]['code_agence']  . '-' . $allFormsMaintenanceArray[$key]['date_et_heure1'] . '.pdf' , $content, LOCK_EX);
            //         break;

            //     default:
            //         dump('Nom en erreur:   ' . $allFormsMaintenanceArray[$key]['nom_client']);
            //         break;
            // }
            
        }
        return $allFormsPdf;
    } 
}
