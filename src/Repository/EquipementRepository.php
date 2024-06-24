<?php

namespace App\Repository;

use App\Entity\Equipement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @extends ServiceEntityRepository<Equipement>
 */
class EquipementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,  private HttpClientInterface $client)
    {
        parent::__construct($registry, Equipement::class);
    }

    /**
    * @return Equipement[] Returns an array of Contact objects
    */
   public function getEquipements(): array
   {
        $equipementsArray = [];
        $equipementsSplittedArray = [];

        $response = $this->client->request(
            'GET',
            'https://forms.kizeo.com/rest/v3/lists/414025', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                ],
            ]
        );
        
        $content = $response->getContent();
        $content = $response->toArray();
        
        
        $equipementsArray = array_map(null, $content['list']['items']);
        for ($i=0; $i < count($equipementsArray) ; $i++) {
            if (isset($equipementsArray[$i])) {
                array_push($equipementsSplittedArray, array_unique(preg_split("/[:|]/", $equipementsArray[$i])));
            }
        }
        return $equipementsSplittedArray;
   }
}
