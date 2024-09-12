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
    * @return Form[] Returns an array of Contact objects
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
    * @return Form[] Returns an array of Contact objects
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

   /**
    * @return Form[] Returns an array of Formulaires with class PORTAILS
    */
   public function getFormsAdvanced(): array
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
       $allFormsPortailsArray = FormRepository::getFormsAdvanced();
    //    dd($allFormsPortailsArray); // ------------------------      Return 24 arrays with portails in them from 8 forms with class PORTAILS

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

        // dd($eachFormDataArray[0]['data']['fields']['portails']);
        
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
     * Function to create and save new equipments in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    public function createAndSaveInDatabaseByAgency($equipements, $arrayResumesEquipments, $entityAgency, $entityManager){
        // Passer à la fonction les variables $equipements avec les nouveaux équipements des formulaires de maintenance, le tableau des résumés de l'agence et son entité ex: $entiteEquipementS10
        /**
        * List all additional equipments stored in individual array
        */
        foreach ($equipements['contrat_de_maintenance']['value']  as $additionalEquipment){
            // dd($additionalEquipment);
            // Everytime a new resume is read, we store its value in variable resume_equipement_supplementaire
            $resume_equipement_supplementaire = array_unique(preg_split("/[:|]/", $additionalEquipment['equipement']['columns']));
            dump("--------------------------------------------------------------------------------------------------------------------");
            dump("Je recupere les équipements supplémentaires de Grenoble, Paris et Montpellier dans $ additionalEquipment de la boucle sur $ dataofFormList");
            // dd($equipements['contrat_de_maintenance']['value'][19]);
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
                // dd($equipements);
                $equipement = new $entityAgency;
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

    /**
     * Function to create and save new equipments in local database by agency --- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    public function uploadListAgencyWithNewRecordsOnKizeo($dataOfFormList, $key, $agencyEquipments, $agencyListId){
        foreach ($dataOfFormList[$key]['contrat_de_maintenance']['value'] as $equipment) {
            // dd($equipment);
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
            $equipement->setNature(strtolower($equipements[4]));
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
}
