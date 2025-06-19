<?php

namespace App\Controller;

use App\Entity\Form;
use GuzzleHttp\Client;
use App\Entity\Portail;
use App\Entity\ContactS10;
use App\Entity\ContactS50;
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
use Doctrine\ORM\EntityManager;
use App\Repository\FormRepository;
use App\Entity\PortailEnvironement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormController extends AbstractController
{
    /**
     * HomePage route to avoid Symfony loading default page
     * 
     */
    // #[Route('/', name: 'home', methods: ['GET'])]
    // public function home(){
    //     return new JsonResponse("L'application API KIZEO est lancée !", Response::HTTP_OK, [], true);
    // }

    /**
     * @return Form[]Function to get Grenoble clients list from BDD  
     */
    #[Route('/api/lists/get/grenoble/clients', name: 'app_api_get_lists_clients_grenoble', methods: ['GET'])]
    public function getListsClientsGrenoble(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        // $formList  =  $formRepository->getAgencyListClientsFromKizeoByListId(409466);
        $clientList  =  $entityManager->getRepository(ContactS50::class)->findAll();
        $clientList = $serializer->serialize($clientList, 'json');
        $response = new Response($clientList);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @return Form[]Function to get Grenoble clients equipments list from BDD  
     */
    #[Route('/api/lists/get/grenoble/equipements', name: 'app_api_get_lists_equipements_clients_grenoble', methods: ['GET'])]
    public function getListsEquipementsClientGrenoble(FormRepository $formRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        // $formList  =  $formRepository->getAgencyListClientsFromKizeoByListId(409466);
        $equipementsClientList  =  $entityManager->getRepository(EquipementS50::class)->findAll();
        $equipementsClientList = $serializer->serialize($equipementsClientList, 'json');
        $response = new Response($equipementsClientList);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
        $formList  =  $formRepository->getFormsAdvancedPortails();
        $jsonContactList = $serializer->serialize($formList, 'json');
       
        // Fetch all contacts in database
        $allFormsInDatabase = $entityManager->getRepository(Form::class)->findAll();
        
        return new JsonResponse("Formulaires parc client sur API KIZEO : " . count($formList) . " | Formulaires parc client en BDD : " . count($allFormsInDatabase) . "\n", Response::HTTP_OK, [], true);
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * Save maintenance equipments in local database then call save equipments to KIZEO  --  FIRST CALL IN CRON TASK
     */
    #[Route('/api/forms/save/maintenance/equipments', name: 'app_api_form_save_maintenance_equipments', methods: ['GET'])]
    public function saveEquipementsInDatabase(FormRepository $formRepository, CacheInterface $cache) //: JsonResponse
    {
        $formRepository->saveEquipmentsInDatabase($cache);
        
        
        // return new JsonResponse("Les équipements de maintenance ont bien été sauvegardés ", Response::HTTP_OK, [], true);
        // return $this->redirectToRoute('app_api_form_save_maintenance_equipments'); // Sauvegarder 5 formulaires à la fois en BDD en BOUCLE A utiliser pour ne mettre à jour que la base de données
       
        // Commenter pour éviter de mettre les listes Kizeo à jour à chaque fois que l'on sauvegarde les équipements de maintenance
        return $this->redirectToRoute('app_api_form_update_lists_equipements_from_bdd'); 
    }

    /**
     * -------------------------------------------------------------------------- SECOND CALL IN CRON TASK
     * This route is going to replace the route above to update equipments list on Kizeo Forms
     */
    #[Route('/api/forms/update/lists/kizeo', name: 'app_api_form_update_lists_equipements_from_bdd', methods: ['GET','PUT'])]
    public function updateKizeoFormsByEquipmentsListFromBdd(FormRepository $formRepository, CacheInterface $cache, EntityManagerInterface $entityManager): JsonResponse
    {
        $formRepository->updateKizeoWithEquipmentsListFromBdd($entityManager, $formRepository, $cache);

        return new JsonResponse('La mise à jour sur KIZEO a été réalisée !', Response::HTTP_OK, [], true);
        // return $this->redirectToRoute('app_api_form_save_maintenance_equipments'); // A remettre pour faire tourner la boucle des 2 URL
    }
    /**
     * 
     * Save PDF maintenance on remote server --                                  THIRD CALL IN CRON TASK
     * LA MISE A JOUR DES PDF A ETE ENLEVEE CAR KIZEO GERE LE FTP DES PDF EN DIRECT A CHAQUE UPLOAD SUR KIZEO
     */
    #[Route('/api/forms/save/maintenance/pdf', name: 'app_api_form_save_maintenance_pdf', methods: ['GET'])]
    public function savePdfInAssetsPdfFolder(FormRepository $formRepository, CacheInterface $cache) : JsonResponse
    {
        $formRepository->savePdfInAssetsPdfFolder($cache);
        
        // return $this->redirectToRoute('app_api_form_save_maintenance_equipments'); // DDécommenter et commenter  : JsonResponse pour faire une boucle fermée des 3 url pour mettre à jour si les tâches cron ne marchent pas
        return new JsonResponse("Les pdf de maintenance ont bien été sauvegardés + on est à jour en BDD et sur KIZEO ", Response::HTTP_OK, [], true);
    }
    
    /**
     * NOUVELLE ROUTE : Démarrage du processus asynchrone pour markasunread
     */
    #[Route('/api/forms/markasunread', name: 'app_api_form_markasunread', methods: ['GET'])]
    public function markMaintenanceFormsAsUnread(FormRepository $formRepository, CacheInterface $cache): JsonResponse
    {
        // Générer un ID unique pour ce processus
        $processId = uniqid('mark_unread_', true);
        
        try {
            // Initialiser le statut du processus en cache
            $initialStatus = [
                'status' => 'started',
                'started_at' => date('Y-m-d H:i:s'),
                'progress' => 0,
                'total' => 0,
                'success_count' => 0,
                'error_count' => 0,
                'processed' => 0,
                'current_form' => null,
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
            $cache->set("mark_unread_status_$processId", $initialStatus, 7200); // 2 heures
            
            // Retourner immédiatement la réponse à l'utilisateur
            $response = new JsonResponse([
                'success' => true,
                'message' => 'Processus de marquage démarré en arrière-plan',
                'process_id' => $processId,
                'status_url' => "/api/forms/markasunread/status/$processId",
                'started_at' => date('Y-m-d H:i:s')
            ]);
            
            // Fermer la connexion HTTP immédiatement
            $response->send();
            
            // Terminer la réponse pour PHP-FPM ou Apache
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                // Pour Apache mod_php
                if (ob_get_level()) {
                    ob_end_flush();
                }
                flush();
            }
            
            // Maintenant démarrer le traitement en arrière-plan
            $this->processMarkUnreadAsync($formRepository, $cache, $processId);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors du démarrage du processus: ' . $e->getMessage()
            ], 500);
        }
        
        // Cette ligne ne sera jamais atteinte si fastcgi_finish_request() fonctionne
        return $response;
    }

    /**
     * NOUVELLE ROUTE : Vérification du statut du processus
     */
    #[Route('/api/forms/markasunread/status/{processId}', name: 'app_api_form_markasunread_status', methods: ['GET'])]
    public function getMarkUnreadStatus(string $processId, CacheInterface $cache): JsonResponse
    {
        try {
            $status = $cache->get("mark_unread_status_$processId");
            
            if (!$status) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Processus non trouvé ou expiré',
                    'process_id' => $processId
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'process_id' => $processId,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération du statut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TRAITEMENT ASYNCHRONE PRINCIPAL
     */
    private function processMarkUnreadAsync(FormRepository $formRepository, CacheInterface $cache, string $processId): void
    {
        try {
            // Configuration pour traitement long
            set_time_limit(0); // Pas de limite de temps
            ini_set('memory_limit', '512M');
            ignore_user_abort(true); // Continue même si l'utilisateur ferme son navigateur
            
            // Mettre à jour le statut : en cours de récupération des formulaires
            $this->updateAsyncStatus($cache, $processId, [
                'status' => 'fetching_forms',
                'message' => 'Récupération de la liste des formulaires...'
            ]);
            
            // Récupérer la liste des formulaires MAINTENANCE
            $maintenanceForms = $this->getMaintenanceFormsForAsync($cache);
            $totalForms = count($maintenanceForms);
            
            if ($totalForms === 0) {
                $this->updateAsyncStatus($cache, $processId, [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'message' => 'Aucun formulaire de maintenance trouvé',
                    'progress' => 100
                ]);
                return;
            }
            
            // Mettre à jour avec le total
            $this->updateAsyncStatus($cache, $processId, [
                'status' => 'processing',
                'total' => $totalForms,
                'message' => "Traitement de $totalForms formulaires..."
            ]);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Traiter chaque formulaire
            foreach ($maintenanceForms as $index => $form) {
                $formId = $form['id'];
                $formName = $form['name'];
                
                try {
                    // Mettre à jour le formulaire en cours
                    $this->updateAsyncStatus($cache, $processId, [
                        'current_form' => [
                            'id' => $formId,
                            'name' => $formName,
                            'index' => $index + 1
                        ],
                        'message' => "Traitement: $formName (". ($index + 1) ."/$totalForms)"
                    ]);
                    
                    // Récupérer les data_ids pour ce formulaire
                    $dataIds = $this->getDataIdsForAsync($formId);
                    
                    if (!empty($dataIds)) {
                        // Marquer comme non lu
                        $this->markFormAsUnreadForAsync($formId, $dataIds);
                        $successCount++;
                        
                        error_log("✅ Formulaire $formId ($formName) marqué avec " . count($dataIds) . " data_ids");
                    } else {
                        error_log("⚠️ Aucun data_id trouvé pour $formId ($formName)");
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorDetail = [
                        'form_id' => $formId,
                        'form_name' => $formName,
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    $errors[] = $errorDetail;
                    
                    error_log("❌ Erreur formulaire $formId ($formName): " . $e->getMessage());
                }
                
                // Calculer et mettre à jour le progrès
                $processed = $index + 1;
                $progress = round(($processed / $totalForms) * 100, 2);
                
                $this->updateAsyncStatus($cache, $processId, [
                    'processed' => $processed,
                    'progress' => $progress,
                    'success_count' => $successCount,
                    'error_count' => $errorCount
                ]);
                
                // Petite pause pour éviter la surcharge de l'API
                usleep(150000); // 0.15 seconde
            }
            
            // Statut final de completion
            $this->updateAsyncStatus($cache, $processId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'progress' => 100,
                'current_form' => null,
                'message' => "Terminé ! Succès: $successCount, Erreurs: $errorCount",
                'final_summary' => [
                    'total_processed' => $totalForms,
                    'successful' => $successCount,
                    'failed' => $errorCount,
                    'success_rate' => $totalForms > 0 ? round(($successCount / $totalForms) * 100, 2) : 0
                ],
                'errors' => $errors
            ]);
            
            error_log("🎉 Processus $processId terminé : $successCount succès, $errorCount erreurs sur $totalForms formulaires");
            
        } catch (\Exception $e) {
            // Erreur critique dans tout le processus
            $this->updateAsyncStatus($cache, $processId, [
                'status' => 'failed',
                'failed_at' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
                'message' => 'Erreur critique: ' . $e->getMessage()
            ]);
            
            error_log("💥 Erreur critique dans processMarkUnreadAsync: " . $e->getMessage());
        }
    }

    /**
     * Récupération des formulaires MAINTENANCE optimisée pour async
     */
    private function getMaintenanceFormsForAsync(CacheInterface $cache): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://forms.kizeo.com/rest/v3/forms', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                    'timeout' => 30
                ]
            );
            
            $content = $response->toArray();
            $maintenanceForms = [];
            
            foreach ($content['forms'] as $form) {
                if ($form['class'] == "MAINTENANCE") {
                    $maintenanceForms[] = [
                        'id' => $form['id'],
                        'name' => $form['name'] ?? 'Formulaire ' . $form['id']
                    ];
                }
            }
            
            return $maintenanceForms;
            
        } catch (\Exception $e) {
            error_log("Erreur getMaintenanceFormsForAsync: " . $e->getMessage());
            throw new \Exception("Impossible de récupérer la liste des formulaires: " . $e->getMessage());
        }
    }

    /**
     * Récupération des data_ids optimisée pour async
     */
    private function getDataIdsForAsync($formId): array
    {
        try {
            $response = $this->client->request('POST', 
                'https://forms.kizeo.com/rest/v3/forms/' . $formId . '/data/advanced', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                    'timeout' => 25
                ]
            );
            
            $content = $response->toArray();
            $dataIds = [];
            
            if (isset($content['data']) && is_array($content['data'])) {
                foreach ($content['data'] as $dataItem) {
                    if (isset($dataItem['_id'])) {
                        $dataIds[] = intval($dataItem['_id']);
                    }
                }
            }
            
            return $dataIds;
            
        } catch (\Symfony\Component\HttpClient\Exception\TimeoutException $e) {
            throw new \Exception("Timeout lors de la récupération des data_ids");
        } catch (\Exception $e) {
            throw new \Exception("Erreur récupération data_ids: " . $e->getMessage());
        }
    }

    /**
     * Marquage comme non lu optimisé pour async
     */
    private function markFormAsUnreadForAsync($formId, $dataIds): void
    {
        try {
            $response = $this->client->request('POST', 
                'https://forms.kizeo.com/rest/v3/forms/' . $formId . '/markasunreadbyaction/read', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $_ENV["KIZEO_API_TOKEN"],
                    ],
                    'json' => ["data_ids" => $dataIds],
                    'timeout' => 20
                ]
            );
            
            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new \Exception("Réponse HTTP inattendue: $statusCode");
            }
            
        } catch (\Symfony\Component\HttpClient\Exception\TimeoutException $e) {
            throw new \Exception("Timeout lors du marquage");
        } catch (\Exception $e) {
            throw new \Exception("Erreur marquage: " . $e->getMessage());
        }
    }

    /**
     * Mise à jour du statut du processus asynchrone
     */
    private function updateAsyncStatus(CacheInterface $cache, string $processId, array $updates): void
    {
        try {
            $currentStatus = $cache->get("mark_unread_status_$processId", []);
            $newStatus = array_merge($currentStatus, $updates);
            $newStatus['last_updated'] = date('Y-m-d H:i:s');
            
            $cache->set("mark_unread_status_$processId", $newStatus, 7200); // 2 heures
            
        } catch (\Exception $e) {
            error_log("Erreur updateAsyncStatus: " . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * Save PDF etat des lieux portails on remote server
     */
    #[Route('/api/forms/save/etatdeslieuxportails/pdf', name: 'app_api_form_save_etatdeslieuxportails_pdf', methods: ['GET'])]
    public function savePortailsPdfInPublicFolder(FormRepository $formRepository): JsonResponse
    {
        //Changer l'appel à la fonction saveEquipementPdfInPublicFolder() pour enregistrer les PDF standard des état des lieux portail
        $formRepository->savePortailsPdfInPublicFolder();
        
        return new JsonResponse("Les pdf d'état des lieux portails ont bien été sauvegardés ", Response::HTTP_OK, [], true);
    }

    /**
     * Function to SAVE new equipments from technicians forms MAINTENANCE from formulaires Visite maintenance To local BDD
     * then call route to save portails at  #[Route('/api/forms/update/portails', name: 'app_api_form_update_portails', methods: ['GET'])]
     * --------------- OK POUR TOUTES LES AGENCES DE S10 à S170
     */
    #[Route('/api/forms/update/maintenance', name: 'app_api_form_update', methods: ['GET'])]
    public function getDataOfFormsMaintenance(FormRepository $formRepository,EntityManagerInterface $entityManager, CacheInterface $cache)
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
        
        
        // GET all technicians forms formulaire Visite maintenance
        $dataOfFormMaintenance  =  $formRepository->getDataOfFormsMaintenance($cache);
        
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
        // $allResumesRennesEquipementsInDatabase = $formRepository->iterateListEquipementsToGetResumes($entityManager->getRepository(EquipementS170::class)->findAll());
        
        foreach ($dataOfFormMaintenance as $equipements){
            
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
                
                
                // case 'S170':
                //     $formRepository->createAndSaveInDatabaseByAgency($equipements, $allResumesRennesEquipementsInDatabase, $entiteEquipementS170, $entityManager);
                //     break;
                
                default:
                    break;
            }
            
        }
        return new JsonResponse("Les équipements en maintenance sont bien enregistrés en base !", Response::HTTP_OK, [], true);
        // return $this->redirectToRoute('app_api_form_update_portails');
    }

    /**
     * Function to ADD new PORTAILS from technicians forms in BDD and save PDF état des lieux locally
     */
    #[Route('/api/forms/update/portails', name: 'app_api_form_update_portails', methods: ['GET'])]
    public function getEtatDesLieuxPortailsDataOfForms(FormRepository $formRepository, EntityManagerInterface $entityManager)
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
        
        // -------------------------------------------
        // ----------------Call function iterate by list equipments to get ONLY Portails in Equipement_numeroAgence list IN LOCAL BDD
        // --------------  OK for this function
        // -------------------------------------------
        
        $allGroupPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS10::class)->findAll());
        $allStEtiennePortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS40::class)->findAll());
        $allGrenoblePortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS50::class)->findAll());
        $allLyonPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS60::class)->findAll());
        $allBordeauxPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS70::class)->findAll());
        $allParisNordPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS80::class)->findAll());
        $allMontpellierPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS100::class)->findAll());
        $allHautsDeFrancePortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS120::class)->findAll());
        $allToulousePortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS130::class)->findAll());
        $allSmpPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS140::class)->findAll());
        $allSogefiPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS150::class)->findAll());
        $allRouenPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS160::class)->findAll());
        $allRennesPortailsInDatabase = $formRepository->getOneTypeOfEquipementInListEquipements("portail", $entityManager->getRepository(EquipementS170::class)->findAll());

        
        // -------------------------------------------
        // --------------------------------------     Call function iterate by list of portails to get resumes in if_exist_db 
        // ----------------------------------  OK for this function
        // -------------------------------------------
        $allResumesGroupPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allGroupPortailsInDatabase);
        $allResumesStEtiennePortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes( $allStEtiennePortailsInDatabase);
        $allResumesGrenoblePortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allGrenoblePortailsInDatabase);
        $allResumesLyonPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allLyonPortailsInDatabase);
        $allResumesBordeauxPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allBordeauxPortailsInDatabase);
        $allResumesParisNordPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allParisNordPortailsInDatabase);
        $allResumesMontpellierPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allMontpellierPortailsInDatabase);
        $allResumesHautsDeFrancePortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allHautsDeFrancePortailsInDatabase);
        $allResumesToulousePortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allToulousePortailsInDatabase);
        $allResumesSmpPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allSmpPortailsInDatabase);
        $allResumesSogefiPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allSogefiPortailsInDatabase);
        $allResumesRouenPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allRouenPortailsInDatabase);
        $allResumesRennesPortailsInDatabase = $formRepository->iterateListEquipementsToGetResumes($allRennesPortailsInDatabase);
        
        // GET all technicians Etat des lieux portails forms from list class PORTAILS
        $dataOfFormsEtatDesLieuxPortails  =  $formRepository->getEtatDesLieuxPortailsDataOfForms();

        foreach ($dataOfFormsEtatDesLieuxPortails as $formPortail) { 
            
            /**
            * Persist each portail in database
            */
            if (isset($formPortail['data']['fields']['n_agence']['value'])) {
                # code...
                switch($formPortail['data']['fields']['n_agence']['value']){
                    case 'S10':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesGroupPortailsInDatabase, $entiteEquipementS10, $entityManager);
                        break;
                    
                    case 'S40':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesStEtiennePortailsInDatabase, $entiteEquipementS40, $entityManager);
                        break;
                    
                    case 'S50':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesGrenoblePortailsInDatabase, $entiteEquipementS50, $entityManager);
                        break;
                    
                    
                    case 'S60':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesLyonPortailsInDatabase, $entiteEquipementS60, $entityManager);
                        break;
                    
                    
                    case 'S70':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesBordeauxPortailsInDatabase, $entiteEquipementS70, $entityManager);
                        break;
                    
                    
                    case 'S80':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesParisNordPortailsInDatabase, $entiteEquipementS80, $entityManager);
                        break;
                    
                    
                    case 'S100':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesMontpellierPortailsInDatabase, $entiteEquipementS100, $entityManager);
                        break;
                    
                    
                    case 'S120':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesHautsDeFrancePortailsInDatabase, $entiteEquipementS120, $entityManager);
                        break;
                    
                    
                    case 'S130':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesToulousePortailsInDatabase, $entiteEquipementS130, $entityManager);
                        break;
                    
                    
                    case 'S140':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesSmpPortailsInDatabase, $entiteEquipementS140, $entityManager);
                        break;
                    
                    
                    case 'S150':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesSogefiPortailsInDatabase, $entiteEquipementS150, $entityManager);
                        break;
                    
                    
                    case 'S160':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesRouenPortailsInDatabase, $entiteEquipementS160, $entityManager);
                        break;
                    
                    
                    case 'S170':
                        $formRepository->saveNewPortailsInDatabaseByAgency("portail", $formPortail, $allResumesRennesPortailsInDatabase, $entiteEquipementS170, $entityManager);
                        break;
                    
                    default:
                        break;
                }
            }
        }
        
        return "Les portails d'états des lieux portails sont bien enregistrés en base !";
        // return $this->redirectToRoute('app_api_form_update_lists_equipements');
    }

    /**
     * UPDATE LIST OF PORTAILS ON KIZEO AND FLUSH NEW PORTAILS IN LOCAL DATABASE    --------------- OK POUR TOUTES LES AGENCES DE S10 à S170
     * 
     */
    // #[Route('/api/forms/update/lists/portails', name: 'app_api_form_update_lists_portails', methods: ['GET','PUT'])]
    // public function putUpdatesListsPortailsFromKizeoForms(FormRepository $formRepository){
    //     $dataOfFormListPortails  =  $formRepository->getEtatDesLieuxPortailsDataOfForms();
    //     dd($dataOfFormListPortails);
    //     // GET portails des agences de Grenoble, Paris et Montpellier en apellant la fonction getAgencyListEquipementsFromKizeoByListId($list_id) avec leur ID de list sur KIZEO
    //     // $portailsGroup = $formRepository->getAgencyListPortailsFromKizeoByListId();
    //     // $portailsStEtienne = $formRepository->getAgencyListPortailsFromKizeoByListId(418520);
    //     $portailsGrenoble = $formRepository->getAgencyListPortailsFromKizeoByListId(418507);
    //     // $portailsLyon = $formRepository->getAgencyListPortailsFromKizeoByListId(418519);
    //     // $portailsBordeaux = $formRepository->getAgencyListPortailsFromKizeoByListId(419394);
    //     $portailsParis = $formRepository->getAgencyListPortailsFromKizeoByListId(417773);
    //     $portailsMontpellier = $formRepository->getAgencyListPortailsFromKizeoByListId(419710);
    //     // $portailsHautsDeFrance = $formRepository->getAgencyListPortailsFromKizeoByListId(417950);
    //     // $portailsToulouse = $formRepository->getAgencyListPortailsFromKizeoByListId(419424);
    //     // $portailsSmp = $formRepository->getAgencyListPortailsFromKizeoByListId();
    //     // $portailsSogefi = $formRepository->getAgencyListPortailsFromKizeoByListId(422539);
    //     // $portailsRouen = $formRepository->getAgencyListPortailsFromKizeoByListId();
    //     // $portailsRennes = $formRepository->getAgencyListPortailsFromKizeoByListId();
        
    //     foreach($dataOfFormListPortails as $key=>$value){

    //         switch ($dataOfFormListPortails[$key]['code_agence']['value']) {
    //             // Fonction uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails,$key,$agencyEquipments,$agencyListId)
    //             // case 'S10':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsGroup, );
    //             //     dump('Uploads S10 OK');
    //             //     break;
    //             // case 'S40':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsStEtienne, );
    //             //     dump('Uploads S40 OK');
    //             //     break;
    //             case 'S50':
    //                 $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsGrenoble, 414025);
    //                 dump('Uploads S50 OK');
    //                 break;
    //             // case 'S60':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsLyon, );
    //             //     dump('Uploads S60 OK');
    //             //     break;
    //             // case 'S70':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsBordeaux, );
    //             //     dump('Uploads S70 OK');
    //             //     break;
                
    //             case 'S80':
    //                 $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsParis, 421993);
    //                 dump('Uploads for S80 OK');
    //                 break;
                
    //             case 'S100':
    //                 $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsMontpellier, 423853);
    //                 dump('Uploads for S100 OK');
    //                 break;
                
    //             // case 'S120':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsHautsDeFrance, );
    //             //     dump('Uploads for S120 OK');
    //             //     break;
                
    //             // case 'S130':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsToulouse, );
    //             //     dump('Uploads for S130 OK');
    //             //     break;
                
    //             // case 'S140':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsSmp, );
    //             //     dump('Uploads for S140 OK');
    //             //     break;
                
    //             // case 'S150':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsSogefi, );
    //             //     dump('Uploads for S150 OK');
    //             //     break;
                
    //             // case 'S160':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsRouen, );
    //             //     dump('Uploads for S160 OK');
    //             //     break;
                
    //             // case 'S170':
    //             //     $formRepository->uploadListAgencyWithNewRecordsOnKizeo($dataOfFormListPortails, $key, $portailsRennes, );
    //             //     dump('Uploads for S170 OK');
    //             //     break;
                
    //             default:
    //                 return new JsonResponse('this is not for our agencies', Response::HTTP_OK, [], true);
    //                 break;
    //         }
    //     }

    //     // ----------------------                 Save new portails in database from all agencies
        

    //     // return new JsonResponse('La mise à jour sur KIZEO s\'est bien déroulée !', Response::HTTP_OK, [], true);
    //     return $this->redirectToRoute('app_api_form_update_portails');
    // }
    
}
