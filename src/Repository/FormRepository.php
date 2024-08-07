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
    * @return Form[] Returns an array of Formulaires with id 986403 wich is "Visite maintenance Grenoble"
    */
   public function getDataOfFormsMaintenance(): array
   {
        $allFormsArray = FormRepository::getForms();
        $allFormsArray = $allFormsArray['forms'];
        $allFormsMaintenanceArray = [];
        $allFormsMaintenanceDataArray = [];

        // dd($allFormsArray); // -----------------------------   Return all forms in an array

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
        // dd($allFormsMaintenanceDataArray);
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
}
