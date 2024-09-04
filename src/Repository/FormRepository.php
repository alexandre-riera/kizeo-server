<?php

namespace App\Repository;

use App\Entity\Form;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
    * @return Form[] Returns an array with all items from "Equipements Contrat 38" with ID 414025 || La liste test a l'ID 421883
    */
   public function getListsEquipementsContrats38(): array
   {
        $response = $this->client->request(
            'GET',
            'https://forms.kizeo.com/rest/v3/lists/421883', [
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
    * PUT Update form from Kizeo formulaires and then update the list "Test equipement 38" with id  421883
    * @return $ items  to put to Kizeo
    */
    public function PutDataOfFormsForUpdateListEquipementsOnKizeo(): array{
        $compteurEquipementsCheckes = 0;
        $compteurFormulaireMaintenanceEnregistres = 0;
        $responseRequest = [];
        $equipmentsGrenoble = $this->getListsEquipementsContrats38();
        $theEquipment =  "";

        // $AllLists = $this -> getLists();
        // ['contrat_de_maintenance']['value'][0]['equipement']['columns']

        // @return  an array of Formulaires with class "MAINTENANCE" wich is all maintenance visits 
        // $dataOfFormList  = $this -> getDataOfFormsMaintenance();
        // dd($dataOfFormList[16]);
        // $compteurFormulaireMaintenanceEnregistres += count($dataOfFormList);


        // foreach($dataOfFormList as $key=>$value){
        //     dump($dataOfFormList[$key]['code_agence']['value']);
        //     $compteurEquipementsCheckes += count($dataOfFormList[$key]['contrat_de_maintenance']['value']);
        //     // dump($dataOfFormList[$key]['contrat_de_maintenance']['value']);

        //     switch ($dataOfFormList[$key]['code_agence']['value']) {
        //         case 'S50':
        //             foreach ($dataOfFormList[$key]['contrat_de_maintenance']['value'] as $equipment) {
        //                 // dd($equipment);
        //                 $theEquipment = $equipment['equipement']['path'] . "\\" . $equipment['equipement']['columns'];
        //                 if (!in_array($theEquipment, $equipmentsGrenoble, true)) {
        //                     array_push($equipmentsGrenoble,  $theEquipment);
        //                 }
        //             }
        //             $response = $this->client->request(
        //                 'PUT',
        //                 'https://forms.kizeo.com/rest/v3/lists/421883', [
        //                     'headers'=>[
        //                         'Accept'=>'application/json',
        //                         'Authorization'=>$_ENV['KIZEO_API_TOKEN'],
        //                     ],
        //                     'body'=>[
        //                         'items'=>$equipmentsGrenoble
        //                     ]
        //                 ]
        //             );
        //             $content = $response->getContent();
        //             $content = $response->toArray();
        //             array_push($responseRequest, $content);
        //             break;
                
        //         default:
        //             # code...
        //             break;
        //     }
        // }
        // dump('Il y a ' . $compteurEquipementsCheckes . ' équipements en maintenance checkés par les techniciens dans ' . $compteurFormulaireMaintenanceEnregistres . ' formulaires de maintenance');
        return $dataOfFormList;
    }
}
