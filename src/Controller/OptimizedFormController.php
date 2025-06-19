<?php

namespace App\Controller;

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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OptimizedFormController extends AbstractController
{
    private const BATCH_SIZE = 5; // Traitement par lots de 5
    private const MAX_EXECUTION_TIME = 50; // 50 secondes max pour éviter timeout
    
    private HttpClientInterface $client;
    private FormRepository $formRepository;

    public function __construct(HttpClientInterface $client, FormRepository $formRepository)
    {
        $this->client = $client;
        $this->formRepository = $formRepository;
    }

    /**
     * Route principale pour traiter les formulaires de maintenance par lots
     */
    #[Route('/api/forms/process/maintenance/batch', name: 'app_process_maintenance_batch', methods: ['GET'])]
    public function processMaintenanceBatch(
        EntityManagerInterface $entityManager, 
        CacheInterface $cache,
        Request $request
    ): JsonResponse {
        $startTime = time();
        $processedCount = 0;
        $totalProcessed = 0;
        
        try {
            // Récupérer l'offset depuis la requête (pour la pagination)
            $offset = $request->query->get('offset', 0);
            $agencyFilter = $request->query->get('agency', null); // Filtrer par agence si nécessaire
            
            // Récupérer les formulaires de maintenance non lus par lots
            $unreadForms = $this->getUnreadMaintenanceFormsBatch($cache, self::BATCH_SIZE, $offset, $agencyFilter);
            
            if (empty($unreadForms)) {
                return new JsonResponse([
                    'status' => 'completed',
                    'message' => 'Aucun formulaire non lu trouvé',
                    'processed' => 0,
                    'total_processed' => $totalProcessed
                ]);
            }

            // Traitement des formulaires
            foreach ($unreadForms as $formData) {
                // Vérifier le temps d'exécution
                if ((time() - $startTime) >= self::MAX_EXECUTION_TIME) {
                    break;
                }

                try {
                    // Récupérer les données détaillées du formulaire
                    $formDetails = $this->getFormDetails($formData['form_id'], $formData['data_id']);
                    
                    if ($formDetails && isset($formDetails['fields'])) {
                        // Traiter et enregistrer les équipements
                        $this->processFormEquipments($formDetails['fields'], $entityManager);
                        
                        // Marquer le formulaire comme lu
                        $this->markFormAsRead($formData['form_id'], $formData['data_id']);
                        
                        $processedCount++;
                        $totalProcessed++;
                    }
                } catch (\Exception $e) {
                    error_log("Erreur lors du traitement du formulaire {$formData['form_id']}/{$formData['data_id']}: " . $e->getMessage());
                    continue;
                }
            }

            // Déterminer s'il y a encore des formulaires à traiter
            $hasMore = count($unreadForms) === self::BATCH_SIZE;
            $nextOffset = $offset + self::BATCH_SIZE;

            return new JsonResponse([
                'status' => $hasMore ? 'continue' : 'completed',
                'processed' => $processedCount,
                'total_processed' => $totalProcessed,
                'next_offset' => $hasMore ? $nextOffset : null,
                'execution_time' => (time() - $startTime),
                'next_url' => $hasMore ? $this->generateUrl('app_process_maintenance_batch', ['offset' => $nextOffset]) : null
            ]);

        } catch (\Exception $e) {
            error_log('Erreur lors du traitement par lots: ' . $e->getMessage());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors du traitement',
                'processed' => $processedCount
            ], 500);
        }
    }

    /**
     * Route pour traiter automatiquement tous les formulaires par lots successifs
     */
    #[Route('/api/forms/process/maintenance/auto', name: 'app_process_maintenance_auto', methods: ['GET'])]
    public function processMaintenanceAuto(EntityManagerInterface $entityManager, CacheInterface $cache): JsonResponse
    {
        $stats = [
            'total_processed' => 0,
            'batches_processed' => 0,
            'errors' => 0,
            'start_time' => time()
        ];

        $offset = 0;
        $hasMore = true;

        while ($hasMore && (time() - $stats['start_time']) < 300) { // 5 minutes max
            try {
                $unreadForms = $this->getUnreadMaintenanceFormsBatch($cache, self::BATCH_SIZE, $offset);
                
                if (empty($unreadForms)) {
                    $hasMore = false;
                    break;
                }

                $batchProcessed = 0;
                foreach ($unreadForms as $formData) {
                    try {
                        $formDetails = $this->getFormDetails($formData['form_id'], $formData['data_id']);
                        
                        if ($formDetails && isset($formDetails['fields'])) {
                            $this->processFormEquipments($formDetails['fields'], $entityManager);
                            $this->markFormAsRead($formData['form_id'], $formData['data_id']);
                            $batchProcessed++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        error_log("Erreur formulaire {$formData['form_id']}: " . $e->getMessage());
                    }
                }

                $stats['total_processed'] += $batchProcessed;
                $stats['batches_processed']++;
                $offset += self::BATCH_SIZE;
                
                // Si on a traité moins que la taille du lot, on a terminé
                if (count($unreadForms) < self::BATCH_SIZE) {
                    $hasMore = false;
                }

                // Petite pause pour éviter la surcharge
                usleep(100000); // 0.1 seconde

            } catch (\Exception $e) {
                $stats['errors']++;
                error_log('Erreur dans le lot: ' . $e->getMessage());
                break;
            }
        }

        return new JsonResponse([
            'status' => 'completed',
            'stats' => $stats,
            'execution_time' => (time() - $stats['start_time'])
        ]);
    }

    /**
     * Récupère les formulaires de maintenance non lus par lots
     */
    private function getUnreadMaintenanceFormsBatch(CacheInterface $cache, int $limit, int $offset = 0, ?string $agencyFilter = null): array
    {
        // Récupérer les formulaires de maintenance depuis le cache
        $maintenanceForms = $cache->get('maintenance_forms_list', function($item) {
            $item->expiresAfter(3600); // 1 heure
            
            $response = $this->client->request('GET', 'https://forms.kizeo.com/rest/v3/forms', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                ],
            ]);
            
            $content = $response->toArray();
            return array_filter($content['forms'], function($form) {
                return $form['class'] == "MAINTENANCE";
            });
        });

        $unreadForms = [];
        $processedCount = 0;

        foreach ($maintenanceForms as $form) {
            if ($processedCount >= ($offset + $limit)) {
                break;
            }

            try {
                // Récupérer les données non lues pour ce formulaire
                $response = $this->client->request('GET', 
                    "https://forms.kizeo.com/rest/v3/forms/{$form['id']}/data/unread/lus/" . $limit, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]);

                $result = $response->toArray();
                
                foreach ($result['data'] as $formData) {
                    if ($processedCount < $offset) {
                        $processedCount++;
                        continue;
                    }
                    
                    if (count($unreadForms) >= $limit) {
                        break 2;
                    }

                    // Filtrer par agence si spécifié
                    if ($agencyFilter) {
                        $details = $this->getFormDetails($formData['_form_id'], $formData['_id']);
                        if (!$details || !isset($details['fields']['code_agence']['value']) || 
                            $details['fields']['code_agence']['value'] !== $agencyFilter) {
                            continue;
                        }
                    }

                    $unreadForms[] = [
                        'form_id' => $formData['_form_id'],
                        'data_id' => $formData['_id']
                    ];
                }
            } catch (\Exception $e) {
                error_log("Erreur lors de la récupération des formulaires non lus pour {$form['id']}: " . $e->getMessage());
                continue;
            }
        }

        return $unreadForms;
    }

    /**
     * Récupère les détails d'un formulaire
     */
    private function getFormDetails(string $formId, string $dataId): ?array
    {
        try {
            $response = $this->client->request('GET', 
                "https://forms.kizeo.com/rest/v3/forms/{$formId}/data/{$dataId}", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                ],
            ]);

            $result = $response->toArray();
            return $result['data'] ?? null;
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération des détails du formulaire {$formId}/{$dataId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Traite et enregistre les équipements d'un formulaire
     */
    private function processFormEquipments(array $fields, EntityManagerInterface $entityManager): void
    {
        if (!isset($fields['code_agence']['value'])) {
            return;
        }

        $entityClass = $this->getEntityClassByAgency($fields['code_agence']['value']);
        if (!$entityClass) {
            return;
        }

        // Traitement des équipements AU CONTRAT
        if (isset($fields['contrat_de_maintenance']['value'])) {
            $this->processContractEquipments($fields, $entityClass, $entityManager);
        }

        // Traitement des équipements HORS CONTRAT
        if (isset($fields['tableau2']['value'])) {
            $this->processOffContractEquipments($fields, $entityClass, $entityManager);
        }
    }

    /**
     * Marque un formulaire comme lu
     */
    private function markFormAsRead(string $formId, string $dataId): void
    {
        try {
            $this->client->request('POST', 
                "https://forms.kizeo.com/rest/v3/forms/{$formId}/markasreadbyaction/lus", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                ],
                'json' => [
                    "data_ids" => [intval($dataId)]
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors du marquage comme lu du formulaire {$formId}/{$dataId}: " . $e->getMessage());
        }
    }

    /**
     * Route pour obtenir le statut du traitement
     */
    #[Route('/api/forms/status/maintenance', name: 'app_maintenance_status', methods: ['GET'])]
    public function getMaintenanceStatus(CacheInterface $cache): JsonResponse
    {
        try {
            $stats = [];
            
            // Compter les formulaires non lus par agence
            $maintenanceForms = $cache->get('maintenance_forms_list', function($item) {
                $item->expiresAfter(300); // 5 minutes pour le statut
                
                $response = $this->client->request('GET', 'https://forms.kizeo.com/rest/v3/forms', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                ]);
                
                $content = $response->toArray();
                return array_filter($content['forms'], function($form) {
                    return $form['class'] == "MAINTENANCE";
                });
            });

            $totalUnread = 0;
            foreach ($maintenanceForms as $form) {
                try {
                    $response = $this->client->request('GET', 
                        "https://forms.kizeo.com/rest/v3/forms/{$form['id']}/data/unread/lus/100", [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                        ],
                    ]);

                    $result = $response->toArray();
                    $count = count($result['data']);
                    $totalUnread += $count;
                    
                    $stats['forms'][] = [
                        'form_id' => $form['id'],
                        'form_name' => $form['name'],
                        'unread_count' => $count
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }

            return new JsonResponse([
                'total_unread' => $totalUnread,
                'estimated_batches' => ceil($totalUnread / self::BATCH_SIZE),
                'batch_size' => self::BATCH_SIZE,
                'forms_details' => $stats['forms'] ?? []
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération du statut'], 500);
        }
    }

    // Reprendre les méthodes de traitement des équipements du WebhookController précédent
    private function processContractEquipments(array $fields, string $entityClass, EntityManagerInterface $entityManager): void
    {
        // Code identique au WebhookController
        foreach ($fields['contrat_de_maintenance']['value'] as $additionalEquipment) {
            $equipement = new $entityClass();
            
            $this->setCommonEquipmentData($equipement, $fields);
            
            $equipement->setNumeroEquipement($additionalEquipment['equipement']['value']);
            $equipement->setIfExistDB($additionalEquipment['equipement']['columns']);
            $equipement->setLibelleEquipement(strtolower($additionalEquipment['reference7']['value']));
            $equipement->setModeFonctionnement($additionalEquipment['mode_fonctionnement_2']['value']);
            $equipement->setRepereSiteClient($additionalEquipment['localisation_site_client']['value']);
            $equipement->setMiseEnService($additionalEquipment['reference2']['value']);
            $equipement->setNumeroDeSerie($additionalEquipment['reference6']['value']);
            $equipement->setMarque($additionalEquipment['reference5']['value']);
            
            $equipement->setLargeur($additionalEquipment['reference3']['value'] ?? '');
            $equipement->setHauteur($additionalEquipment['reference1']['value'] ?? '');
            $equipement->setLongueur($additionalEquipment['longueur']['value'] ?? 'NC');
            
            $equipement->setPlaqueSignaletique($additionalEquipment['plaque_signaletique']['value']);
            $equipement->setEtat($additionalEquipment['etat']['value']);
            
            $equipement->setHauteurNacelle($additionalEquipment['hauteur_de_nacelle_necessaire']['value'] ?? '');
            $equipement->setModeleNacelle($additionalEquipment['si_location_preciser_le_model']['value'] ?? '');
            
            $equipement->setStatutDeMaintenance($this->getMaintenanceContractStatus($additionalEquipment['etat']['value']));
            $equipement->setVisite($this->getVisitType($additionalEquipment['equipement']['path']));
            
            $equipement->setEnMaintenance(true);
            $equipement->setIsArchive(false);
            
            $entityManager->persist($equipement);
        }
        
        $entityManager->flush();
    }

    private function processOffContractEquipments(array $fields, string $entityClass, EntityManagerInterface $entityManager): void
    {
        foreach ($fields['tableau2']['value'] as $equipementsHorsContrat) {
            $equipement = new $entityClass();
            
            // Données communes
            $this->setCommonEquipmentData($equipement, $fields);
            
            // Attribution automatique du numéro d'équipement
            $typeLibelle = strtolower($equipementsHorsContrat['nature']['value']);
            $typeCode = $this->getTypeCodeFromLibelle($typeLibelle);
            $idClient = $fields['id_client_']['value'];
            $nouveauNumero = $this->getNextEquipmentNumberFromDatabase($typeCode, $idClient, $entityClass, $entityManager);
            $numeroFormate = $typeCode . str_pad($nouveauNumero, 2, '0', STR_PAD_LEFT);
            
            $equipement->setNumeroEquipement($numeroFormate);
            $equipement->setLibelleEquipement($typeLibelle);
            $equipement->setModeFonctionnement($equipementsHorsContrat['mode_fonctionnement_']['value']);
            $equipement->setRepereSiteClient($equipementsHorsContrat['localisation_site_client1']['value']);
            $equipement->setMiseEnService($equipementsHorsContrat['annee']['value']);
            $equipement->setNumeroDeSerie($equipementsHorsContrat['n_de_serie']['value']);
            $equipement->setMarque($equipementsHorsContrat['marque']['value']);
            
            // Dimensions (optionnelles)
            $equipement->setLargeur($equipementsHorsContrat['largeur']['value'] ?? '');
            $equipement->setHauteur($equipementsHorsContrat['hauteur']['value'] ?? '');
            
            $equipement->setPlaqueSignaletique($equipementsHorsContrat['plaque_signaletique1']['value']);
            $equipement->setEtat($equipementsHorsContrat['etat1']['value']);
            
            // Statut de maintenance pour équipements hors contrat
            $equipement->setStatutDeMaintenance($this->getOffContractMaintenanceStatus($equipementsHorsContrat['etat1']['value']));
            
            // Type de visite basé sur le premier équipement au contrat
            $equipement->setVisite($this->getDefaultVisitType($fields));
            
            $equipement->setEnMaintenance(false);
            $equipement->setIsArchive(false);
            
            $entityManager->persist($equipement);
        }
        
        $entityManager->flush();
    }

    /**
     * Définition des données communes à tous les équipements
     */
    private function setCommonEquipmentData($equipement, array $fields): void
    {
        $equipement->setIdContact($fields['id_client_']['value']);
        $equipement->setRaisonSociale($fields['nom_client']['value']);
        $equipement->setDateEnregistrement($fields['date_et_heure1']['value']);
        $equipement->setCodeSociete($fields['id_societe']['value'] ?? '');
        $equipement->setCodeAgence($fields['id_agence']['value'] ?? '');
        $equipement->setDerniereVisite($fields['date_et_heure1']['value']);
        $equipement->setTrigrammeTech($fields['trigramme']['value']);
        $equipement->setSignatureTech($fields['signature3']['value']);
        
        if (isset($fields['test_']['value'])) {
            $equipement->setTest($fields['test_']['value']);
        }
    }

    /**
     * Détermination de la classe d'entité selon le code agence
     */
    private function getEntityClassByAgency(string $codeAgence): ?string
    {
        $agencyMap = [
            'S10' => EquipementS10::class,
            'S40' => EquipementS40::class,
            'S50' => EquipementS50::class,
            'S60' => EquipementS60::class,
            'S70' => EquipementS70::class,
            'S80' => EquipementS80::class,
            'S100' => EquipementS100::class,
            'S120' => EquipementS120::class,
            'S130' => EquipementS130::class,
            'S140' => EquipementS140::class,
            'S150' => EquipementS150::class,
            'S160' => EquipementS160::class,
            'S170' => EquipementS170::class,
        ];

        return $agencyMap[$codeAgence] ?? null;
    }

    /**
     * Détermination du type de visite basé sur le chemin de l'équipement
     */
    private function getVisitType(string $equipmentPath): string
    {
        if (str_contains($equipmentPath, 'CE1')) return 'CE1';
        if (str_contains($equipmentPath, 'CE2')) return 'CE2';
        if (str_contains($equipmentPath, 'CE3')) return 'CE3';
        if (str_contains($equipmentPath, 'CE4')) return 'CE4';
        if (str_contains($equipmentPath, 'CEA')) return 'CEA';
        
        return 'CE1'; // Valeur par défaut
    }

    /**
     * Type de visite par défaut pour équipements hors contrat
     */
    private function getDefaultVisitType(array $fields): string
    {
        if (!empty($fields['contrat_de_maintenance']['value'])) {
            return $this->getVisitType($fields['contrat_de_maintenance']['value'][0]['equipement']['path']);
        }
        return 'CE1';
    }

    /**
     * Statut de maintenance pour équipements au contrat
     */
    private function getMaintenanceContractStatus(string $etat): string
    {
        switch ($etat) {
            case "Rien à signaler le jour de la visite. Fonctionnement ok":
                return "Vert";
            case "Travaux à prévoir":
                return "Orange";
            case "Travaux obligatoires":
                return "Rouge";
            case "Equipement inaccessible le jour de la visite":
                return "Inaccessible";
            case "Equipement à l'arrêt le jour de la visite":
                return "A l'arrêt";
            case "Equipement mis à l'arrêt lors de l'intervention":
                return "Rouge";
            case "Equipement non présent sur site":
                return "Non présent";
            default:
                return "NC";
        }
    }

    /**
     * Statut de maintenance pour équipements hors contrat
     */
    private function getOffContractMaintenanceStatus(string $etat): string
    {
        switch ($etat) {
            case "A":
                return "Bon état de fonctionnement le jour de la visite";
            case "B":
                return "Travaux préventifs";
            case "C":
                return "Travaux curatifs";
            case "D":
                return "Equipement à l'arrêt le jour de la visite";
            case "E":
                return "Equipement mis à l'arrêt lors de l'intervention";
            default:
                return "NC";
        }
    }

    /**
     * Obtient le code du type d'équipement à partir du libellé
     */
    private function getTypeCodeFromLibelle(string $typeLibelle): string
    {
        $typeCodeMap = [
            'porte sectionnelle' => 'SEC',
            'porte battante' => 'BPA',
            'porte basculante' => 'PBA',
            'porte rapide' => 'RAP',
            'porte pietonne' => 'PPV',
            'porte coulissante' => 'COU',
            'porte coupe feu' => 'CFE',
            'porte coupe-feu' => 'CFE',
            'porte accordéon' => 'PAC',
            'porte frigorifique' => 'COF',
            'barriere levante' => 'BLE',
            'barriere' => 'BLE',
            'mini pont' => 'MIP',
            'mini-pont' => 'MIP',
            'rideau' => 'RID',
            'rideau métalliques' => 'RID',
            'rideau metallique' => 'RID',
            'rideau métallique' => 'RID',
            'niveleur' => 'NIV',
            'portail' => 'PAU',
            'portail motorisé' => 'PMO',
            'portail motorise' => 'PMO',
            'portail manuel' => 'PMA',
            'portail coulissant' => 'PCO',
            'protection' => 'PRO',
            'portillon' => 'POR',
            'table elevatrice' => 'TEL',
            'tourniquet' => 'TOU',
            'issue de secours' => 'BPO',
            'bloc roue' => 'BLR',
            'sas' => 'SAS',
            'plaque de quai' => 'PLQ',
        ];

        $typeLibelle = strtolower(trim($typeLibelle));
        
        if (isset($typeCodeMap[$typeLibelle])) {
            return $typeCodeMap[$typeLibelle];
        }
        
        // Si le libellé contient plusieurs mots, prendre les premières lettres
        $words = explode(' ', $typeLibelle);
        if (count($words) > 1) {
            $code = '';
            foreach ($words as $word) {
                if (strlen($word) > 0) {
                    $code .= strtoupper(substr($word, 0, 1));
                }
            }
            if (strlen($code) < 3 && strlen($words[0]) >= 3) {
                $code = strtoupper(substr($words[0], 0, 3));
            }
            return $code;
        }
        
        return strtoupper(substr($typeLibelle, 0, 3));
    }

    /**
     * Détermine le prochain numéro d'équipement à utiliser
     */
    private function getNextEquipmentNumberFromDatabase(string $typeCode, string $idClient, string $entityClass, EntityManagerInterface $entityManager): int
    {
        $equipements = $entityManager->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->where('e.idContact = :idClient')
            ->andWhere('e.numeroEquipement LIKE :pattern')
            ->setParameter('idClient', $idClient)
            ->setParameter('pattern', $typeCode . '%')
            ->getQuery()
            ->getResult();
        
        $dernierNumero = 0;
        
        foreach ($equipements as $equipement) {
            $numeroEquipement = $equipement->getNumeroEquipement();
            
            if (preg_match('/^' . preg_quote($typeCode) . '(\d+)$/', $numeroEquipement, $matches)) {
                $numero = (int)$matches[1];
                if ($numero > $dernierNumero) {
                    $dernierNumero = $numero;
                }
            }
        }
        
        return $dernierNumero + 1;
    }
}