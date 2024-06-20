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
   public function getForms(int $formId): array
   {
        $response = $this->client->request(
            'POST',
            'https://forms.kizeo.com/rest/v3/forms/' . $formId . '/data/advanced', [
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
    * @return data of Form[] Returns an array of Contact objects
    */
   public function getDataOfForms(int $formId): array
   {
       $dataId = [];
       $eachFormDataArray = [];

        $allFormsArray = FormRepository::getForms(986403);

        foreach ($allFormsArray['data'] as $key => $value) {
            foreach ($allFormsArray['data'][$key] as $ids => $value) {
                if ($ids === "_id" ) {
                    array_push($dataId, $value);
                }
            }
        }

        foreach ($dataId as $key => $value) {
            $responseDataOfForm = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms/' . $formId . '/data/' . $value, [
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
}
