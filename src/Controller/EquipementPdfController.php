<?php
// src/Controller/EquipementPdfController.php
namespace App\Controller;

use App\Entity\Form;
use App\Entity\ContactS10;
use App\Entity\ContactS40;
use App\Entity\ContactS50;
use App\Entity\ContactS60;
use App\Entity\ContactS70;
use App\Entity\ContactS80;
use App\Entity\ContactS100;
use App\Entity\ContactS120;
use App\Entity\ContactS130;
use App\Entity\ContactS140;
use App\Entity\ContactS150;
use App\Entity\ContactS160;
use App\Entity\ContactS170;
use App\Entity\EquipementS10;
use App\Entity\EquipementS40;
use App\Entity\EquipementS50;
use App\Entity\EquipementS60;
use App\Entity\EquipementS70;
use App\Entity\EquipementS80;
use App\Service\EmailService;
use App\Service\PdfGenerator;
use App\Entity\EquipementS100;
use App\Entity\EquipementS120;
use App\Entity\EquipementS130;
use App\Entity\EquipementS140;
use App\Entity\EquipementS150;
use App\Entity\EquipementS160;
use App\Entity\EquipementS170;
use App\Service\ShortLinkService;
use App\Service\PdfStorageService;
use App\Service\ImageStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EquipementPdfController extends AbstractController
{
    private $pdfGenerator;
    private $imageStorageService;
    private PdfStorageService $pdfStorageService;
    private ShortLinkService $shortLinkService;
    private EmailService $emailService;
    
    public function __construct(PdfGenerator $pdfGenerator, ImageStorageService $imageStorageService, PdfStorageService $pdfStorageService, ShortLinkService $shortLinkService, EmailService $emailService)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->imageStorageService = $imageStorageService;
        $this->pdfStorageService = $pdfStorageService;
        $this->shortLinkService = $shortLinkService;
        $this->emailService = $emailService;
    }
    
    /**
     * 
     */
    #[Route('/equipement/pdf/{agence}/{id}', name: 'equipement_pdf_single')]
    public function generateSingleEquipementPdf(string $agence, string $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'équipement selon l'agence (même logique que votre fonction existante)
        $equipment = $this->getEquipmentByAgence($agence, $id, $entityManager);
        
        if (!$equipment) {
            throw $this->createNotFoundException('Équipement non trouvé');
        }
        
        // Récupérer les photos selon votre logique existante
        $picturesArray = $entityManager->getRepository(Form::class)->findBy([
            'code_equipement' => $equipment->getNumeroEquipement(), 
            'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()
        ]);
        
        $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
        
        // Générer le HTML pour le PDF
        $html = $this->renderView('pdf/single_equipement.html.twig', [
            'equipment' => $equipment,
            'picturesData' => $picturesData,
            'agence' => $agence
        ]);
        
        // Générer le PDF
        $filename = "equipement_{$equipment->getNumeroEquipement()}_{$agence}.pdf";
        $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
        
        // Retourner le PDF
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\""
            ]
        );
    }
    
    /**
     * Génère un PDF complet pour tous les équipements d'un client
     * VERSION MISE À JOUR - Utilise les photos stockées en local au lieu des appels API
     * Route: /client/equipements/pdf/{agence}/{id}
     */

    #[Route('/client/equipements/pdf/{agence}/{id}', name: 'client_equipements_pdf')]
    public function generateClientEquipementsPdf(Request $request, string $agence, string $id, EntityManagerInterface $entityManager): Response
    {
        // 1. TOUJOURS initialiser imageUrl dès le début
        $imageUrl = $this->getImageUrlForAgency($agence) ?: 'https://www.pdf.somafi-group.fr/background/group.jpg';
        
        // Initialiser les métriques de performance
        $startTime = microtime(true);
        $photoSourceStats = ['local' => 0, 'api_fallback' => 0, 'none' => 0];
        
        try {
            // Configuration MySQL optimisée pour les gros volumes
            $entityManager->getConnection()->executeStatement('SET SESSION wait_timeout = 300');
            $entityManager->getConnection()->executeStatement('SET SESSION interactive_timeout = 300');
            
            // Récupérer les filtres depuis les paramètres de la requête
            $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
            $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            
            error_log("=== GÉNÉRATION PDF CLIENT ===");
            error_log("Agence: {$agence}, Client: {$id}");
            error_log("Filtres - Année: '{$clientAnneeFilter}', Visite: '{$clientVisiteFilter}'");
            
            // Récupérer les informations client TOUT DE SUITE
            $clientSelectedInformations = $this->getClientInformations($agence, $id, $entityManager);
            
            // Récupérer les informations client (autre méthode)
            $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
            error_log("Client info récupérées: " . json_encode($clientInfo));
            
            // 2. RÉCUPÉRATION SIMPLIFIÉE ET SÉCURISÉE DES ÉQUIPEMENTS
            $equipments = $this->getEquipmentsByClientAndAgence($agence, $id, $entityManager);
            error_log("Équipements bruts trouvés: " . count($equipments));
            
            if (empty($equipments)) {
                throw new \Exception("Aucun équipement trouvé pour le client {$id}");
            }
            
            // 3. LOGIQUE DE FILTRAGE CORRIGÉE SELON VOS SPÉCIFICATIONS
            $equipmentsFiltered = [];
            $filtreApplique = false;
            
            if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
                // CAS AVEC FILTRES : équipements de la visite sélectionnée avec année de dernière visite
                error_log("Application des filtres spécifiques...");
                
                foreach ($equipments as $equipment) {
                    try {
                        $matches = true;
                        
                        // Filtre par visite si défini
                        if (!empty($clientVisiteFilter)) {
                            $visiteEquipment = $equipment->getVisite();
                            if ($visiteEquipment !== $clientVisiteFilter) {
                                $matches = false;
                            }
                            error_log("Équipement {$equipment->getNumeroEquipement()}: visite '{$visiteEquipment}' vs filtre '{$clientVisiteFilter}' = " . ($matches ? 'OUI' : 'NON'));
                        }
                        
                        // Filtre par année de dernière visite si défini
                        if ($matches && !empty($clientAnneeFilter)) {
                            $derniereVisite = $equipment->getDerniereVisite();
                            if ($derniereVisite) {
                                $anneeEquipment = date("Y", strtotime($derniereVisite));
                                if ($anneeEquipment !== $clientAnneeFilter) {
                                    $matches = false;
                                }
                                error_log("Équipement {$equipment->getNumeroEquipement()}: année dernière visite {$anneeEquipment} vs filtre {$clientAnneeFilter} = " . ($matches ? 'OUI' : 'NON'));
                            } else {
                                $matches = false;
                                error_log("Équipement {$equipment->getNumeroEquipement()}: pas de date de dernière visite");
                            }
                        }
                        
                        if ($matches) {
                            $equipmentsFiltered[] = $equipment;
                            $filtreApplique = true;
                        }
                        
                    } catch (\Exception $e) {
                        error_log("Erreur filtrage équipement {$equipment->getNumeroEquipement()}: " . $e->getMessage());
                    }
                }
                
                error_log("Après filtrage: " . count($equipmentsFiltered) . " équipements");
                
            } else {
                // CAS PAR DÉFAUT : équipements de la dernière visite uniquement
                error_log("Pas de filtres - récupération équipements de la dernière visite");
                
                // Trouver la date de dernière visite la plus récente
                $derniereVisiteMax = null;
                foreach ($equipments as $equipment) {
                    $derniereVisite = $equipment->getDerniereVisite();
                    if ($derniereVisite && (!$derniereVisiteMax || strtotime($derniereVisite) > strtotime($derniereVisiteMax))) {
                        $derniereVisiteMax = $derniereVisite;
                    }
                }
                
                if ($derniereVisiteMax) {
                    $anneeDerniereVisite = date("Y", strtotime($derniereVisiteMax));
                    error_log("Dernière visite trouvée: {$derniereVisiteMax} (année: {$anneeDerniereVisite})");
                    
                    // Filtrer les équipements de cette dernière visite (même année)
                    foreach ($equipments as $equipment) {
                        $derniereVisite = $equipment->getDerniereVisite();
                        if ($derniereVisite && date("Y", strtotime($derniereVisite)) === $anneeDerniereVisite) {
                            $equipmentsFiltered[] = $equipment;
                        }
                    }
                } else {
                    // Fallback : tous les équipements si aucune date trouvée
                    error_log("Aucune date de dernière visite trouvée - utilisation de tous les équipements");
                    $equipmentsFiltered = $equipments;
                }
            }
            
            // 4. VÉRIFICATION APRÈS FILTRAGE
            if (empty($equipmentsFiltered)) {
                error_log("ATTENTION: Aucun équipement après filtrage!");
                
                // Debug des équipements disponibles
                $sampleEquipments = array_slice($equipments, 0, 5);
                foreach ($sampleEquipments as $eq) {
                    error_log("Équipement échantillon - Num: {$eq->getNumeroEquipement()}, Visite: '{$eq->getVisite()}', Dernière visite: {$eq->getDerniereVisite()}");
                }
                
                // Générer un PDF d'erreur informatif
                return $this->generateErrorPdf($agence, $id, $imageUrl, $entityManager, 
                    "Aucun équipement ne correspond aux filtres sélectionnés.", 
                    [
                        'filtre_annee' => $clientAnneeFilter,
                        'filtre_visite' => $clientVisiteFilter,
                        'total_equipements_bruts' => count($equipments)
                    ], 
                    $clientSelectedInformations
                );
            }
            
            // 5. TRAITEMENT DES ÉQUIPEMENTS AVEC PHOTOS
            $equipmentsWithPictures = [];
            $dateDeDerniererVisite = null;
            
            foreach ($equipmentsFiltered as $equipment) {
                try {
                    // Récupération optimisée des photos
                    $picturesArray = [
                        "numeroEquipement" => $equipment->getNumeroEquipement(),
                        "client" => explode("\\", $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale(),
                        "annee" => $clientAnneeFilter ?: date('Y', strtotime($equipment->getDateEnregistrement() ?: 'now')),
                        "visite" => $clientVisiteFilter ?: ($equipment->getVisite() ?? 'CEA')
                    ];
                    
                    $picturesData = $entityManager->getRepository(Form::class)
                        ->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                    
                    $photoSource = !empty($picturesData) ? 'local' : 'none';
                    $photoSourceStats[$photoSource]++;
                    
                } catch (\Exception $e) {
                    error_log("Erreur photos équipement {$equipment->getNumeroEquipement()}: " . $e->getMessage());
                    $picturesData = [];
                    $photoSource = 'none';
                    $photoSourceStats['none']++;
                }
                
                $equipmentsWithPictures[] = [
                    'equipment' => $equipment,
                    'pictures' => $picturesData,
                    'photo_source' => $photoSource
                ];
                
                // Récupérer la date de dernière visite
                if (!$dateDeDerniererVisite && $equipment->getDerniereVisite()) {
                    $dateDeDerniererVisite = $equipment->getDerniereVisite();
                }
            }
            
            error_log("DEBUG - equipmentsWithPictures count: " . count($equipmentsWithPictures));
            
            // 6. SÉPARATION DES ÉQUIPEMENTS - VERSION SÉCURISÉE
            $equipementsSupplementaires = [];
            $equipementsNonPresents = [];
            
            foreach ($equipmentsWithPictures as $equipmentData) {
                try {
                    // Vérifier si la méthode isEnMaintenance existe avant de l'appeler
                    if (method_exists($equipmentData['equipment'], 'isEnMaintenance')) {
                        if ($equipmentData['equipment']->isEnMaintenance() === false) {
                            $equipementsSupplementaires[] = $equipmentData;
                        }
                    }
                    
                    // Équipements non présents
                    $etat = $equipmentData['equipment']->getEtat();
                    if ($etat === "Equipement non présent sur site" || $etat === "G") {
                        $equipementsNonPresents[] = $equipmentData;
                    }
                } catch (\Exception $e) {
                    error_log("Erreur séparation équipement: " . $e->getMessage());
                }
            }
            
            error_log("DEBUG - equipementsSupplementaires count: " . count($equipementsSupplementaires));
            
            // 7. CALCUL DES STATISTIQUES
            $statistiques = $this->calculateEquipmentStatistics($equipmentsFiltered);
            
            // 8. CALCUL DES STATISTIQUES SUPPLÉMENTAIRES
            $statistiquesSupplementaires = null;
            if (!empty($equipementsSupplementaires)) {
                $equipmentsSupplementairesOnly = array_map(function($item) {
                    return $item['equipment'];
                }, $equipementsSupplementaires);
                $statistiquesSupplementaires = $this->calculateEquipmentStatistics($equipmentsSupplementairesOnly);
            }
            
            // 9. GÉNÉRATION DU PDF
            $filename = "equipements_client_{$id}_{$agence}";
            if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
                $filename .= '_filtered';
                if (!empty($clientAnneeFilter)) $filename .= "_{$clientAnneeFilter}";
                if (!empty($clientVisiteFilter)) $filename .= "_" . str_replace(' ', '_', $clientVisiteFilter);
            }
            $filename .= '.pdf';

            $templateVars = [
                'equipmentsWithPictures' => $equipmentsWithPictures,
                'equipementsSupplementaires' => $equipementsSupplementaires,
                'equipementsNonPresents' => $equipementsNonPresents,
                'clientId' => $id,
                'agence' => $agence,
                'imageUrl' => $imageUrl,
                'clientAnneeFilter' => $clientAnneeFilter ?: '',
                'clientVisiteFilter' => $clientVisiteFilter ?: '',
                'statistiques' => $statistiques,
                'statistiquesSupplementaires' => $statistiquesSupplementaires,
                'photoSourceStats' => $photoSourceStats,
                'isFiltered' => !empty($clientAnneeFilter) || !empty($clientVisiteFilter),
                'dateDeDerniererVisite' => $dateDeDerniererVisite,
                'filtrage_success' => true,
                'total_equipements_bruts' => count($equipments),
                'total_equipements_filtres' => count($equipmentsFiltered),
                'clientSelectedInformations' => $clientSelectedInformations,
            ];
            
            // Vérifier que imageUrl est bien définie
            if (empty($templateVars['imageUrl'])) {
                $templateVars['imageUrl'] = 'https://www.pdf.somafi-group.fr/background/group.jpg';
                error_log("WARNING: imageUrl était vide, fallback utilisé");
            }
            
            error_log("Génération du template avec " . count($equipmentsWithPictures) . " équipements");
            
            $html = $this->renderView('pdf/equipements.html.twig', $templateVars);
            $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            
            return new Response($pdfContent, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\"",
                'X-Equipment-Count' => count($equipmentsFiltered),
                'X-Filter-Applied' => $filtreApplique ? 'yes' : 'no'
            ]);
            
        } catch (\Exception $e) {
            error_log("ERREUR GÉNÉRATION PDF: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // En cas d'erreur, générer un PDF d'erreur détaillé
            return $this->generateErrorPdf($agence, $id, $imageUrl, $entityManager, $e->getMessage(), [], $clientSelectedInformations);
        }
    }

    #[Route('/diagnostic/photos/{agence}/{clientId}', name: 'diagnostic_photos')]
    public function diagnosticPhotos(string $agence, string $clientId, EntityManagerInterface $entityManager): Response
    {
        $basePhotoPath = $_SERVER['DOCUMENT_ROOT'] . '/public/img/' . $agence . '/GEODIS_CORBAS/2025/CE1/';
        
        $results = [];
        
        // Vérifier si le répertoire existe
        if (is_dir($basePhotoPath)) {
            $files = scandir($basePhotoPath);
            $generalePhotos = array_filter($files, function($file) {
                return strpos($file, '_generale.jpg') !== false;
            });
            
            $results['directory_exists'] = true;
            $results['total_files'] = count($files) - 2; // Enlever . et ..
            $results['generale_photos'] = array_values($generalePhotos);
            $results['photo_count'] = count($generalePhotos);
        } else {
            $results['directory_exists'] = false;
            $results['expected_path'] = $basePhotoPath;
        }
        
        return $this->json($results);
    }

/**
 * Génère un PDF d'erreur informatif
 */
private function generateErrorPdf(string $agence, string $id, string $imageUrl, EntityManagerInterface $entityManager, string $errorMessage, array $debugInfo = [], array $clientSelectedInformations = []): Response
{
    error_log("Génération PDF d'erreur pour {$agence}/{$id}");
    
    $html = $this->renderView('pdf/equipements.html.twig', [
        'equipmentsWithPictures' => [],
        'equipementsSupplementaires' => [],
        'equipementsNonPresents' => [],
        'clientId' => $id,
        'agence' => $agence,
        'imageUrl' => $imageUrl,
        'clientAnneeFilter' => '',
        'clientVisiteFilter' => '',
        'error_mode' => true,
        'error_message' => $errorMessage,
        'debug_info' => $debugInfo,
        'isFiltered' => false,
        'dateDeDerniererVisite' => null,
        'clientSelectedInformations' => $clientSelectedInformations,
    ]);
    
    $filename = "equipements_client_{$id}_{$agence}_error.pdf";
    $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
    
    return new Response($pdfContent, Response::HTTP_OK, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => "inline; filename=\"$filename\"",
        'X-Generation-Mode' => 'error'
    ]);
}

    /**
     * Gestion spécialisée pour les gros volumes
     */
    // private function handleLargeVolumeGeneration(array $equipments, string $agence, string $id, EntityManagerInterface $entityManager, string $imageUrl, string $clientAnneeFilter, string $clientVisiteFilter): Response
    // {
    //     error_log("Mode gros volume activé pour " . count($equipments) . " équipements");
        
    //     try {
    //         // Configuration MySQL optimisée
    //         $entityManager->getConnection()->executeStatement('SET SESSION sql_mode = ""');
    //         $entityManager->getConnection()->executeStatement('SET SESSION max_execution_time = 0');
            
    //         // Augmenter les limites PHP
    //         ini_set('memory_limit', '1G');
    //         ini_set('max_execution_time', 300);
            
    //         // Traitement ultra-optimisé sans photos pour éviter le timeout
    //         $equipmentsWithPictures = [];
    //         foreach ($equipments as $equipment) {
    //             $equipmentsWithPictures[] = [
    //                 'equipment' => $equipment,
    //                 'pictures' => [], // Pas de photos pour éviter les timeouts
    //                 'photo_source' => 'disabled_for_performance'
    //             ];
    //         }
            
    //         $statistiques = $this->calculateEquipmentStatistics($equipments);
            
    //         $filename = "equipements_client_{$id}_{$agence}_performance.pdf";
    //         $clientInformations = $this->getClientInformations($agence, $id, $this->entityManager);
    //         $html = $this->renderView('pdf/equipements.html.twig', [
    //             'equipmentsWithPictures' => $equipmentsWithPictures,
    //             'equipementsSupplementaires' => [],
    //             'equipementsNonPresents' => [],
    //             'clientId' => $id,
    //             'agence' => $agence,
    //             'imageUrl' => $imageUrl, // Toujours définie
    //             'clientAnneeFilter' => $clientAnneeFilter ?: '', // Toujours défini
    //             'clientVisiteFilter' => $clientVisiteFilter ?: '', // Toujours défini
    //             'clientSelectedInformations' => $this->getClientInformations($agence, $id, $entityManager),
    //             'statistiques' => $statistiques,
    //             'performance_mode' => true,
    //             'equipment_count' => count($equipments),
    //             'isFiltered' => !empty($clientAnneeFilter) || !empty($clientVisiteFilter),
    //             'dateDeDerniererVisite' => null,
    //             'clientSelectedInformations' => $clientInformations,
    //         ]);
            
    //         $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            
    //         return new Response($pdfContent, Response::HTTP_OK, [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => "inline; filename=\"$filename\"",
    //             'X-Performance-Mode' => 'large-volume',
    //             'X-Equipment-Count' => count($equipments)
    //         ]);
            
    //     } catch (\Exception $e) {
    //         error_log("Erreur mode gros volume: " . $e->getMessage());
    //         throw $e;
    //     }
    // }

    /**
     * Traitement par batch des équipements
     */
    private function processBatchEquipments(array $equipmentBatch, EntityManagerInterface $entityManager): array
    {
        $equipmentsWithPictures = [];
        
        foreach ($equipmentBatch as $equipment) {
            try {
                // Récupération optimisée des photos
                $picturesArray = [
                    "numeroEquipement" => $equipment->getNumeroEquipement(),
                    "client" => explode("\\", $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale(),
                    "annee" => explode("\\", $equipment->getVisite())[1] ?? date('Y'),
                    "visite" => explode("\\", $equipment->getVisite())[0] ?? $equipment->getVisite()
                ];
                
                // $picturesData = $entityManager->getRepository(Form::class)
                //     ->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                // NOUVEAU CODE  
                $picturesData = $entityManager->getRepository(Form::class)
                    ->getGeneralPhotoFromLocalStorage($equipment, $entityManager);

                if (empty($picturesData)) {
                    $picturesData = $entityManager->getRepository(Form::class)
                        ->findGeneralPhotoByScanning($equipment);
                } 
            } catch (\Exception $e) {
                error_log("Erreur récupération photos équipement {$equipment->getNumeroEquipement()}: " . $e->getMessage());
                $picturesData = [];
            }
            
            $equipmentsWithPictures[] = [
                'equipment' => $equipment,
                'pictures' => $picturesData,
                'photo_source' => !empty($picturesData) ? 'local' : 'none'
            ];
        }
        
        return $equipmentsWithPictures;
    }

    /**
     * PDF simplifié en cas d'erreur
     */
    // private function generateSimplifiedPdf(string $agence, string $id, string $imageUrl, EntityManagerInterface $entityManager): Response
    // {
    //     error_log("Génération PDF simplifiée pour {$agence}/{$id}");
        
    //     $html = $this->renderView('pdf/equipements.html.twig', [
    //         'equipmentsWithPictures' => [],
    //         'equipementsSupplementaires' => [],
    //         'equipementsNonPresents' => [],
    //         'clientId' => $id,
    //         'agence' => $agence,
    //         'imageUrl' => $imageUrl, // TOUJOURS définie
    //         'clientAnneeFilter' => '', // Défini même si vide
    //         'clientVisiteFilter' => '', // Défini même si vide
    //         'error_mode' => true,
    //         'error_message' => 'Trop d\'équipements pour la génération complète. Veuillez utiliser les filtres.',
    //         'isFiltered' => false,
    //         'dateDeDerniererVisite' => null
    //     ]);
        
    //     $filename = "equipements_client_{$id}_{$agence}_error.pdf";
    //     $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
        
    //     return new Response($pdfContent, Response::HTTP_OK, [
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' => "inline; filename=\"$filename\"",
    //         'X-Generation-Mode' => 'error-fallback'
    //     ]);
    // }

    // private function getEquipmentsByAgencyFixed(string $agence, string $clientId, EntityManagerInterface $entityManager, ?string $anneeFilter = null, ?string $visiteFilter = null): array
    // {
    //     error_log("=== RÉCUPÉRATION ÉQUIPEMENTS ===");
    //     error_log("Agence: {$agence}, Client: {$clientId}");
    //     error_log("Filtres - Année: {$anneeFilter}, Visite: {$visiteFilter}");
        
    //     $equipmentEntity = "App\\Entity\\Equipement{$agence}";
        
    //     if (!class_exists($equipmentEntity)) {
    //         error_log("ERREUR: Classe d'équipement {$equipmentEntity} n'existe pas");
    //         throw new \Exception("Classe d'équipement {$equipmentEntity} introuvable");
    //     }
        
    //     try {
    //         $repository = $entityManager->getRepository($equipmentEntity);
            
    //         // D'abord, essayer de trouver des équipements sans filtres
    //         $allEquipments = $repository->findBy(['id_contact' => $clientId]);
    //         error_log("Total équipements pour client {$clientId}: " . count($allEquipments));
            
    //         if (empty($allEquipments)) {
    //             // Pas d'équipements du tout pour ce client
    //             error_log("AUCUN équipement trouvé pour le client {$clientId}");
                
    //             // Essayer de voir s'il y a des équipements dans la table
    //             $sampleEquipments = $repository->findBy([], [], 5);
    //             error_log("Échantillon d'équipements dans la table: " . count($sampleEquipments));
                
    //             if (!empty($sampleEquipments)) {
    //                 $sampleIds = array_map(function($eq) {
    //                     return method_exists($eq, 'getIdContact') ? $eq->getIdContact() : 'N/A';
    //                 }, $sampleEquipments);
    //                 error_log("IDs clients échantillon: " . implode(', ', $sampleIds));
    //             }
                
    //             return [];
    //         }
            
    //         // Si on a des équipements, appliquer les filtres
    //         $criteria = ['id_contact' => $clientId];
            
    //         // Pour les filtres, il faut connaître les noms exacts des propriétés
    //         // Regardons un équipement pour voir les propriétés disponibles
    //         $firstEquipment = $allEquipments[0];
    //         $methods = get_class_methods($firstEquipment);
    //         $getterMethods = array_filter($methods, function($method) {
    //             return strpos($method, 'get') === 0;
    //         });
            
    //         error_log("Méthodes disponibles sur l'équipement: " . implode(', ', $getterMethods));
            
    //         // Essayer différents noms de propriétés pour l'année
    //         if ($anneeFilter) {
    //             $yearProperties = ['annee', 'year', 'dateVisite', 'date_visite', 'anneeVisite'];
    //             foreach ($yearProperties as $prop) {
    //                 $getter = 'get' . ucfirst($prop);
    //                 if (method_exists($firstEquipment, $getter)) {
    //                     error_log("Propriété année trouvée: {$prop}");
    //                     // Pour l'instant, on n'applique pas le filtre année car on ne connaît pas la structure exacte
    //                     break;
    //                 }
    //             }
    //         }
            
    //         // Essayer différents noms de propriétés pour la visite
    //         if ($visiteFilter) {
    //             $visiteProperties = ['visite', 'typeVisite', 'type_visite', 'maintenance'];
    //             foreach ($visiteProperties as $prop) {
    //                 $getter = 'get' . ucfirst($prop);
    //                 if (method_exists($firstEquipment, $getter)) {
    //                     error_log("Propriété visite trouvée: {$prop}");
    //                     // Pour l'instant, on n'applique pas le filtre visite car on ne connaît pas la structure exacte
    //                     break;
    //                 }
    //             }
    //         }
            
    //         // Pour le moment, retourner tous les équipements du client
    //         // Vous pourrez affiner les filtres une fois que vous connaîtrez la structure exacte
    //         error_log("Retour de " . count($allEquipments) . " équipements");
    //         return $allEquipments;
            
    //     } catch (\Exception $e) {
    //         error_log("Erreur récupération équipements {$agence}: " . $e->getMessage());
    //         throw new \Exception("Erreur lors de la récupération des équipements: " . $e->getMessage());
    //     }
    // }

    // ===== ROUTE DE DEBUG POUR ANALYSER LA STRUCTURE DES ÉQUIPEMENTS =====
    #[Route('/api/test-equipment/{agence}/{clientId}', name: 'api_test_equipment', methods: ['GET'])]
    public function testEquipmentStructure(
        string $agence,
        string $clientId,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $equipmentEntity = "App\\Entity\\Equipement{$agence}";
            
            if (!class_exists($equipmentEntity)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => "Classe {$equipmentEntity} n'existe pas"
                ]);
            }
            
            $repository = $entityManager->getRepository($equipmentEntity);
            $equipments = $repository->findBy(['id_contact' => $clientId], [], 3); // Prendre max 3 équipements
            
            if (empty($equipments)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => "Aucun équipement trouvé pour le client {$clientId}",
                    'total_in_table' => count($repository->findAll())
                ]);
            }
            
            // Analyser la structure du premier équipement
            $firstEquipment = $equipments[0];
            $methods = get_class_methods($firstEquipment);
            $getterMethods = array_filter($methods, function($method) {
                return strpos($method, 'get') === 0;
            });
            
            // Tester les valeurs
            $testValues = [];
            foreach ($getterMethods as $method) {
                try {
                    $value = $firstEquipment->$method();
                    if (is_scalar($value) && !empty($value)) {
                        $testValues[$method] = $value;
                    }
                } catch (\Exception $e) {
                    // Ignorer les méthodes qui requirent des paramètres
                }
            }
            
            return new JsonResponse([
                'success' => true,
                'entity' => $equipmentEntity,
                'equipment_count' => count($equipments),
                'available_getters' => $getterMethods,
                'sample_values' => $testValues,
                'potential_year_fields' => array_filter($getterMethods, function($method) {
                    return stripos($method, 'annee') !== false || 
                        stripos($method, 'year') !== false ||
                        stripos($method, 'date') !== false;
                }),
                'potential_visite_fields' => array_filter($getterMethods, function($method) {
                    return stripos($method, 'visite') !== false ||
                        stripos($method, 'maintenance') !== false ||
                        stripos($method, 'type') !== false;
                })
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Route pour télécharger un PDF stocké
     */
    #[Route('/pdf/download/{agence}/{clientId}/{annee}/{visite}', name: 'pdf_download')]
    public function downloadStoredPdf(
        string $agence,
        string $clientId,
        string $annee,
        string $visite
    ): Response {
        $pdfPath = $this->pdfStorageService->getPdfPath($agence, $clientId, $annee, $visite);
        
        if (!$pdfPath) {
            throw $this->createNotFoundException('PDF non trouvé');
        }
        
        $pdfContent = file_get_contents($pdfPath);
        $filename = "client_{$clientId}_{$annee}_{$visite}.pdf";
        
        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, max-age=3600',
            'Last-Modified' => date('D, d M Y H:i:s', filemtime($pdfPath)) . ' GMT'
        ]);
    }

    /**
     * Route SÉCURISÉE pour télécharger un PDF stocké
     * IMPORTANT: Cette route ne doit PAS être exposée directement au client
     */
    #[Route('/pdf/secure-download/{agence}/{clientId}/{annee}/{visite}', name: 'pdf_secure_download')]
    public function secureDownloadPdf(
        string $agence,
        string $clientId,
        string $annee,
        string $visite,
        Request $request
    ): Response {
        // SÉCURITÉ : Vérifier que la requête vient d'un lien court valide
        $referer = $request->headers->get('referer');
        $shortCode = $request->query->get('sc'); // Short code pour validation
        
        if (!$shortCode) {
            throw $this->createAccessDeniedException('Accès non autorisé');
        }
        
        // Valider le lien court
        $shortLink = $this->shortLinkService->getByShortCode($shortCode);
        if (!$shortLink || $shortLink->isExpired()) {
            throw $this->createNotFoundException('Lien expiré ou invalide');
        }
        
        // Vérifier que les paramètres correspondent au lien court
        if ($shortLink->getAgence() !== $agence || 
            $shortLink->getClientId() !== $clientId ||
            $shortLink->getAnnee() !== $annee ||
            $shortLink->getVisite() !== $visite) {
            throw $this->createAccessDeniedException('Paramètres invalides');
        }
        
        // Récupérer le PDF
        $pdfPath = $this->pdfStorageService->getPdfPath($agence, $clientId, $annee, $visite);
        
        if (!$pdfPath) {
            throw $this->createNotFoundException('PDF non trouvé');
        }
        
        // Enregistrer l'accès
        $this->shortLinkService->recordAccess($shortLink);
        
        $pdfContent = file_get_contents($pdfPath);
        $filename = "rapport_equipements_{$clientId}_{$annee}_{$visite}.pdf";
        
        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Last-Modified' => date('D, d M Y H:i:s', filemtime($pdfPath)) . ' GMT'
        ]);
    }

    /**
     * Route de redirection des liens courts SÉCURISÉE
     */
    #[Route('/s/{shortCode}', name: 'short_link_redirect')]
    public function redirectShortLink(string $shortCode): Response
    {
        $shortLink = $this->shortLinkService->getByShortCode($shortCode);
        
        if (!$shortLink) {
            // Afficher une page d'erreur personnalisée au lieu d'une 404 technique
            return $this->render('error/link_not_found.html.twig', [
                'message' => 'Ce lien n\'est plus valide ou a expiré.'
            ], new Response('', 410)); // 410 = Gone
        }
        
        // Enregistrer l'accès
        $this->shortLinkService->recordAccess($shortLink);
        
        // SÉCURITÉ : Construire l'URL sécurisée avec le code de validation
        $secureUrl = $this->generateUrl('pdf_secure_download', [
            'agence' => $shortLink->getAgence(),
            'clientId' => $shortLink->getClientId(),
            'annee' => $shortLink->getAnnee(),
            'visite' => $shortLink->getVisite()
        ]) . '?sc=' . $shortCode;
        
        return $this->redirect($secureUrl);
    }

    /**
     * NOUVELLE ROUTE pour envoyer un PDF existant par email - VERSION CORRIGÉE
     */
    #[Route('/client/equipements/send-email/{agence}/{id}', name: 'send_pdf_email', methods: ['POST'])]
    public function sendPdfByEmail(
        Request $request,
        string $agence,
        string $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $annee = $request->request->get('annee', date('Y'));
            $visite = $request->request->get('visite', 'CEA');
            $clientEmail = $request->request->get('client_email');
            
            if (!$clientEmail) {
                throw new \InvalidArgumentException('Email client requis');
            }
            
            // Validation de l'email
            if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Format d\'email invalide');
            }
            
            // Vérifier que le PDF existe ou le générer
            $pdfPath = $this->pdfStorageService->getPdfPath($agence, $id, $annee, $visite);
            if (!$pdfPath) {
                // Générer le PDF d'abord
                $subRequest = Request::create($this->generateUrl('client_equipements_pdf', [
                    'agence' => $agence,
                    'id' => $id
                ]) . "?clientAnneeFilter={$annee}&clientVisiteFilter={$visite}");
                
                $pdfResponse = $this->generateClientEquipementsPdf($subRequest, $agence, $id, $entityManager);
                
                // Vérifier si la génération a réussi
                if (!$pdfResponse instanceof Response || $pdfResponse->getStatusCode() !== 200) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Impossible de générer le PDF'
                    ], 500);
                }
                
                // Réessayer de récupérer le chemin du PDF après génération
                $pdfPath = $this->pdfStorageService->getPdfPath($agence, $id, $annee, $visite);
                if (!$pdfPath) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'PDF généré mais non stocké'
                    ], 500);
                }
            }
            
            // Créer ou récupérer le lien court
            $originalUrl = $this->generateUrl('pdf_secure_download', [
                'agence' => $agence,
                'clientId' => $id,
                'annee' => $annee,
                'visite' => $visite
            ], true);
            
            $expiresAt = (new \DateTime())->modify('+30 days');
            
            $shortLink = $this->shortLinkService->createShortLink(
                $originalUrl,
                $agence,
                $id,
                $annee,
                $visite,
                $expiresAt
            );
            
            $shortUrl = $this->shortLinkService->getShortUrl($shortLink->getShortCode());
            
            // Récupérer les infos client (nom seulement, email vient de la requête)
            $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
            $clientName = $clientInfo['nom'] ?? "Client $id";
            
            // Générer le trigramme utilisateur
            $userTrigramme = $this->generateUserTrigramme();
            
            // ✅ CORRECTION : Utiliser $clientEmail au lieu de $clientInfo['email']
            $emailSent = $this->emailService->sendPdfLinkToClient(
                $agence,
                $clientEmail,  // ← Email de la requête, pas de la BDD
                $clientName,
                $shortUrl,
                $annee,
                $visite,
                $userTrigramme
            );
            
            // Enregistrer l'envoi avec les bonnes informations
            $this->recordEmailSent($agence, $id, $clientEmail, $shortUrl, $emailSent, $userTrigramme, $entityManager);
            
            return new JsonResponse([
                'success' => $emailSent,
                'message' => $emailSent ? 'Email envoyé avec succès' : 'Erreur lors de l\'envoi',
                'short_url' => $shortUrl,
                'short_code' => $shortLink->getShortCode(),
                'client_email' => $clientEmail,
                'client_name' => $clientName
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur sendPdfByEmail: " . $e->getMessage());
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Méthode corrigée pour enregistrer l'envoi d'email avec toutes les infos nécessaires
     */
    private function recordEmailSent(
        string $agence, 
        string $clientId, 
        string $clientEmail, 
        string $shortUrl, 
        bool $success, 
        string $userTrigramme,
        EntityManagerInterface $entityManager
    ): void {
        try {
            $mailEntity = "App\\Entity\\Mail{$agence}";
            $contactEntity = "App\\Entity\\Contact{$agence}";
            
            if (!class_exists($mailEntity)) {
                error_log("Classe Mail{$agence} n'existe pas");
                return;
            }
            
            $contact = $entityManager->getRepository($contactEntity)->findOneBy(['id_contact' => $clientId]);
            
            if (!$contact) {
                error_log("Contact {$clientId} non trouvé pour enregistrement email");
                return;
            }
            
            $mail = new $mailEntity();
            $mail->setIdContact($contact);
            $mail->setPdfUrl($shortUrl);
            $mail->setIsPdfSent($success);
            $mail->setSentAt(new \DateTimeImmutable());
            $mail->setSender($userTrigramme);
            $mail->setPdfFilename("client_{$clientId}.pdf");
            
            // Ajouter l'email de destination si la propriété existe
            if (method_exists($mail, 'setRecipientEmail')) {
                $mail->setRecipientEmail($clientEmail);
            }
            
            $entityManager->persist($mail);
            $entityManager->flush();
            
            error_log("Email enregistré avec succès pour client {$clientId}");
            
        } catch (\Exception $e) {
            error_log("Erreur enregistrement email: " . $e->getMessage());
        }
    }

    /**
     * Génère le trigramme à partir des informations de l'utilisateur connecté - VERSION CORRIGÉE
     */
    private function generateUserTrigramme(): string
    {
        $user = $this->getUser();
        
        if (!$user) {
            return 'SYS'; // Fallback si pas d'utilisateur connecté
        }
        
        try {
            // Essayer différentes méthodes selon votre entité User
            $firstName = '';
            $lastName = '';
            
            // Tester les getters possibles pour le prénom
            if (method_exists($user, 'getPrenom')) {
                $firstName = $user->getPrenom();
            }

            // Tester les getters possibles pour le nom
            if (method_exists($user, 'getNom')) {
                $lastName = $user->getNom();
            }
            
            // Nettoyer et normaliser les chaînes
            $firstName = strtoupper(trim($firstName));
            $lastName = strtoupper(trim($lastName));
            
            // Construire le trigramme
            $trigramme = '';
            
            // 1ère lettre du prénom
            if (!empty($firstName)) {
                $trigramme .= substr($firstName, 0, 1);
            } else {
                $trigramme .= 'U'; // U pour User
            }
            
            // 2 premières lettres du nom
            if (!empty($lastName)) {
                if (strlen($lastName) >= 2) {
                    $trigramme .= substr($lastName, 0, 2);
                } else {
                    $trigramme .= $lastName . 'X';
                }
            } else {
                $trigramme .= 'SR'; // SR pour SeR (utilisateur)
            }
            
            error_log("Trigramme généré: {$trigramme} (Prénom: {$firstName}, Nom: {$lastName})");
            
            return $trigramme;
            
        } catch (\Exception $e) {
            error_log("Erreur génération trigramme: " . $e->getMessage());
            return 'USR'; // Fallback générique
        }
    }
    private function getClientInfo(string $agence, string $id, EntityManagerInterface $entityManager): array
    {
        try {
            $contactEntity = "App\\Entity\\Contact{$agence}";
            
            if (class_exists($contactEntity)) {
                $contact = $entityManager->getRepository($contactEntity)->findOneBy(['id_contact' => $id]);
                
                if ($contact) {
                    $nom = '';
                    $email = '';
                    
                    // Pour ContactS50, tester les méthodes disponibles
                    // Récupération du nom/raison sociale
                    if (method_exists($contact, 'getRaisonSociale')) {
                        $nom = $contact->getRaisonSociale();
                    } elseif (method_exists($contact, 'getNom')) {
                        $nom = $contact->getNom();
                    } elseif (method_exists($contact, 'getLibelle')) {
                        $nom = $contact->getLibelle();
                    } elseif (method_exists($contact, 'getNomContact')) {
                        $nom = $contact->getNomContact();
                    }
                    
                    // Récupération de l'email - tester plusieurs possibilités
                    if (method_exists($contact, 'getEmail')) {
                        $email = $contact->getEmail();
                    } elseif (method_exists($contact, 'getEmailContact')) {
                        $email = $contact->getEmailContact();
                    } elseif (method_exists($contact, 'getMail')) {
                        $email = $contact->getMail();
                    } elseif (method_exists($contact, 'getEmailClient')) {
                        $email = $contact->getEmailClient();
                    }
                    
                    // Debug pour voir les méthodes disponibles sur ContactS50
                    error_log("DEBUG ContactS50 - Méthodes disponibles: " . implode(', ', get_class_methods($contact)));
                    error_log("DEBUG ContactS50 - Nom trouvé: " . ($nom ?: 'VIDE'));
                    error_log("DEBUG ContactS50 - Email trouvé: " . ($email ?: 'VIDE'));
                    
                    return [
                        'nom' => $nom ?: "Client {$id}",
                        'email' => $email ?: '',
                        'id_contact' => $id,
                        'agence' => $agence
                    ];
                } else {
                    error_log("DEBUG: Contact non trouvé pour ID {$id} dans {$contactEntity}");
                }
            } else {
                error_log("DEBUG: Classe {$contactEntity} n'existe pas");
            }
        } catch (\Exception $e) {
            error_log("Erreur récupération client info: " . $e->getMessage());
        }
        
        return [
            'nom' => "Client {$id}",
            'email' => '',
            'id_contact' => $id,
            'agence' => $agence
        ];
    }

    // ===== 2. AJOUT DE LA ROUTE API POUR RÉCUPÉRER LES INFOS CLIENT =====
    #[Route('/api/client-info/{agence}/{id}', name: 'api_client_info', methods: ['GET'])]
    public function getClientInfoApi(
        string $agence,
        string $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
            
            return new JsonResponse([
                'success' => true,
                'client' => $clientInfo,
                'debug' => [
                    'agence' => $agence,
                    'id' => $id,
                    'entity_class' => "App\\Entity\\Contact{$agence}"
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'client' => [
                    'nom' => "Client {$id}",
                    'email' => '',
                    'id_contact' => $id,
                    'agence' => $agence
                ]
            ], 500);
        }
    }
    private function getEquipmentsByAgency(string $agence, string $clientId, EntityManagerInterface $entityManager, ?string $anneeFilter = null, ?string $visiteFilter = null): array
    {
        $equipmentEntity = "App\\Entity\\Equipement{$agence}";
        
        if (!class_exists($equipmentEntity)) {
            error_log("ERREUR: Classe d'équipement {$equipmentEntity} n'existe pas");
            return [];
        }
        
        try {
            $criteria = ['id_contact' => $clientId];
            
            // Ajouter les filtres si spécifiés
            if ($anneeFilter) {
                $criteria['annee'] = $anneeFilter;
            }
            if ($visiteFilter) {
                $criteria['visite'] = $visiteFilter;
            }
            
            $equipments = $entityManager->getRepository($equipmentEntity)->findBy(
                $criteria,
                ['numero_equipement' => 'ASC']
            );
            
            error_log("DEBUG: Récupération équipements {$agence} pour client {$clientId} - Trouvés: " . count($equipments));
            
            if (empty($equipments)) {
                // Essayer sans les filtres pour voir s'il y a des équipements
                $allEquipments = $entityManager->getRepository($equipmentEntity)->findBy(
                    ['id_contact' => $clientId],
                    ['numero_equipement' => 'ASC']
                );
                
                error_log("DEBUG: Total équipements sans filtre pour client {$clientId}: " . count($allEquipments));
                
                // Si pas d'équipements du tout, l'erreur est légitime
                if (empty($allEquipments)) {
                    throw new \Exception("Aucun équipement trouvé pour ce client. Vérifiez l'ID client et l'agence.");
                }
                
                // Si il y a des équipements mais pas avec les filtres, utiliser tous les équipements
                return $allEquipments;
            }
            
            return $equipments;
            
        } catch (\Exception $e) {
            error_log("Erreur récupération équipements {$agence}: " . $e->getMessage());
            throw $e;
        }
    }

    // ===== 4. MÉTHODE POUR TESTER LA CONNEXION À LA BASE DE DONNÉES ContactS50 =====
    #[Route('/api/test-contact/{agence}/{id}', name: 'api_test_contact', methods: ['GET'])]
    public function testContactConnection(
        string $agence,
        string $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $contactEntity = "App\\Entity\\Contact{$agence}";
            
            if (!class_exists($contactEntity)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => "Classe {$contactEntity} n'existe pas"
                ]);
            }
            
            // Récupérer le contact
            $contact = $entityManager->getRepository($contactEntity)->findOneBy(['id_contact' => $id]);
            
            if (!$contact) {
                // Essayer de lister quelques contacts pour debug
                $allContacts = $entityManager->getRepository($contactEntity)->findBy([], [], 5);
                
                return new JsonResponse([
                    'success' => false,
                    'error' => "Contact {$id} non trouvé",
                    'debug' => [
                        'entity' => $contactEntity,
                        'total_contacts_sample' => count($allContacts),
                        'sample_ids' => array_map(function($c) {
                            return method_exists($c, 'getIdContact') ? $c->getIdContact() : 'N/A';
                        }, $allContacts)
                    ]
                ]);
            }
            
            // Analyser les méthodes disponibles
            $methods = get_class_methods($contact);
            $getterMethods = array_filter($methods, function($method) {
                return strpos($method, 'get') === 0;
            });
            
            // Tester les getters pour nom et email
            $testResults = [];
            foreach ($getterMethods as $method) {
                try {
                    $value = $contact->$method();
                    if (is_string($value) && !empty($value)) {
                        $testResults[$method] = substr($value, 0, 50); // Limiter pour l'affichage
                    }
                } catch (\Exception $e) {
                    // Ignorer les méthodes qui requirent des paramètres
                }
            }
            
            return new JsonResponse([
                'success' => true,
                'contact_found' => true,
                'entity' => $contactEntity,
                'available_getters' => $getterMethods,
                'test_values' => $testResults,
                'potential_email_methods' => array_filter($getterMethods, function($method) {
                    return stripos($method, 'email') !== false || stripos($method, 'mail') !== false;
                }),
                'potential_name_methods' => array_filter($getterMethods, function($method) {
                    return stripos($method, 'nom') !== false || 
                        stripos($method, 'raison') !== false || 
                        stripos($method, 'libelle') !== false ||
                        stripos($method, 'name') !== false;
                })
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * NOUVELLES MÉTHODES HELPER
     */

    /**
     * Détermine si le fallback vers l'API doit être utilisé
     */
    private function shouldUseFallback(): bool
    {
        // Par défaut, ne pas utiliser le fallback pour optimiser les performances
        // Peut être configuré via variable d'environnement
        return $_ENV['PDF_ENABLE_API_FALLBACK'] ?? false;
    }

    /**
     * Récupère les photos avec fallback pour équipements au contrat
     */
    private function getEquipmentPicturesWithFallback($equipment, EntityManagerInterface $entityManager): array
    {
        try {
            $picturesArray = $entityManager->getRepository(Form::class)->findBy([
                'code_equipement' => $equipment->getNumeroEquipement(),
                'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()
            ]);
            
            return $entityManager->getRepository(Form::class)
                ->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
        } catch (\Exception $e) {
            error_log("Fallback API failed for equipment {$equipment->getNumeroEquipement()}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les photos avec fallback pour équipements supplémentaires
     */
    private function getSupplementaryEquipmentPicturesWithFallback($equipment, EntityManagerInterface $entityManager): array
    {
        try {
            return $entityManager->getRepository(Form::class)
                ->getPictureArrayByIdSupplementaryEquipment($entityManager, $equipment);
        } catch (\Exception $e) {
            error_log("Fallback API failed for supplementary equipment {$equipment->getNumeroEquipement()}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcule les statistiques uniquement pour les équipements AU CONTRAT
     */
    private function calculateEquipmentStatistics(array $equipments): array
    {
        $etatsCount = [];
        $counterInexistant = 0;
        
        foreach ($equipments as $equipment) {
            // ✅ VÉRIFICATION : S'assurer qu'on ne traite que les équipements au contrat
            if (!$equipment->isEnMaintenance()) {
                continue; // Ignorer les équipements hors contrat
            }
            
            $etat = $equipment->getEtat();
            
            if ($etat === "Equipement non présent sur site" || $etat === "G") {
                $counterInexistant++;
            } elseif ($etat) {
                if (!isset($etatsCount[$etat])) {
                    $etatsCount[$etat] = 0;
                }
                $etatsCount[$etat]++;
            }
        }
        
        return [
            'etatsCount' => $etatsCount,
            'counterInexistant' => $counterInexistant,
            'totalAuContrat' => count($equipments) // ✅ AJOUT : Total des équipements au contrat
        ];
    }

    /**
     * Calcule les statistiques des équipements supplémentaires (HORS CONTRAT)
     * avec conversion des codes d'état en libellés lisibles
     */
    private function calculateSupplementaryStatistics(array $equipementsSupplementaires): array
    {
        $etatsCountSupplementaires = [];
        $totalSupplementaires = 0;
        
        foreach ($equipementsSupplementaires as $equipmentData) {
            $equipment = $equipmentData['equipment'];
            
            // ✅ VÉRIFICATION : S'assurer qu'on ne traite que les équipements hors contrat
            if ($equipment->isEnMaintenance()) {
                continue; // Ignorer les équipements au contrat
            }
            
            $etatCode = $equipment->getEtat();
            
            // ✅ CONVERSION des codes d'état en libellés lisibles
            $etatLibelle = $this->convertEtatCodeToLibelle($etatCode);
            
            if ($etatLibelle && $etatCode !== "Equipement non présent sur site" && $etatCode !== "G") {
                $totalSupplementaires++;
                
                if (!isset($etatsCountSupplementaires[$etatLibelle])) {
                    $etatsCountSupplementaires[$etatLibelle] = 0;
                }
                $etatsCountSupplementaires[$etatLibelle]++;
            }
        }
        
        return [
            'etatsCount' => $etatsCountSupplementaires,
            'total' => $totalSupplementaires
        ];
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Convertit les codes d'état en libellés lisibles
     */
    private function convertEtatCodeToLibelle(string $etatCode): string
    {
        switch ($etatCode) {
            case 'A':
                return 'Bon état';
            case 'B':
                return 'Travaux à prévoir';
            case 'C':
                return 'Travaux curatifs urgents';
            case 'D':
                return 'Equipement inaccessible';
            case 'E':
            case 'F':
                return 'Equipement à l\'arrêt';
            case 'G':
                return 'Equipement non présent sur site';
            default:
                // Si ce n'est pas un code, retourner tel quel (déjà un libellé)
                return $etatCode;
        }
    }

    /**
     * Récupère les informations client selon l'agence
     */
    private function getClientInformations(string $agence, string $id, EntityManagerInterface $entityManager)
    {
        try {
            $contact = null;
            
            switch ($agence) {
                case 'S10':
                    $contact = $entityManager->getRepository(ContactS10::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S40':
                    $contact = $entityManager->getRepository(ContactS40::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S50':
                    $contact = $entityManager->getRepository(ContactS50::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S60':
                    $contact = $entityManager->getRepository(ContactS60::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S70':
                    $contact = $entityManager->getRepository(ContactS70::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S80':
                    $contact = $entityManager->getRepository(ContactS80::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S100':
                    $contact = $entityManager->getRepository(ContactS100::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S120':
                    $contact = $entityManager->getRepository(ContactS120::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S130':
                    $contact = $entityManager->getRepository(ContactS130::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S140':
                    $contact = $entityManager->getRepository(ContactS140::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S150':
                    $contact = $entityManager->getRepository(ContactS150::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S160':
                    $contact = $entityManager->getRepository(ContactS160::class)->findOneBy(['id_contact' => $id]);
                    break;
                case 'S170':
                    $contact = $entityManager->getRepository(ContactS170::class)->findOneBy(['id_contact' => $id]);
                    break;
                default:
                    return null;
            }
            
            if (!$contact) {
                return null;
            }
            
            return [
                'nom' => $contact->getNom() ?? 'Client non trouvé',
                'adresse' => $contact->getAdressep1() ?? $contact->getAdressep2(),
                'codePostal' => $contact->getCpostalp() ?? '',
                'ville' => $contact->getVillep() ?? '',
                'telephone' => $contact->getTelephone() ?? '',
                'email' => $contact->getEmail() ?? ''
            ];
        } catch (\Exception $e) {
            error_log("Erreur récupération informations client: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Log des métriques de performance
     */
    private function logPdfGenerationMetrics(string $agence, string $clientId, int $equipmentCount, array $photoStats, float $totalTime): void
    {
        $logData = [
            'type' => 'client_pdf_generation',
            'agence' => $agence,
            'client_id' => $clientId,
            'equipment_count' => $equipmentCount,
            'photo_sources' => $photoStats,
            'total_generation_time' => $totalTime,
            'average_time_per_equipment' => $equipmentCount > 0 ? round($totalTime / $equipmentCount, 3) : 0,
            'performance_gain' => $photoStats['local'] > 0 ? 'significant' : 'none',
            'timestamp' => date('c')
        ];
        
        error_log("PDF_GENERATION_METRICS: " . json_encode($logData));
    }
    
    private function getEquipmentByAgence(string $agence, string $id, EntityManagerInterface $entityManager)
    {
        switch ($agence) {
            case 'S10':
                return $entityManager->getRepository(EquipementS10::class)->findOneBy(['id' => $id]);
            case 'S40':
                return $entityManager->getRepository(EquipementS40::class)->findOneBy(['id' => $id]);
            case 'S50':
                return $entityManager->getRepository(EquipementS50::class)->findOneBy(['id' => $id]);
            case 'S60':
                return $entityManager->getRepository(EquipementS60::class)->findOneBy(['id' => $id]);
            case 'S70':
                return $entityManager->getRepository(EquipementS70::class)->findOneBy(['id' => $id]);
            case 'S80':
                return $entityManager->getRepository(EquipementS80::class)->findOneBy(['id' => $id]);
            case 'S100':
                return $entityManager->getRepository(EquipementS100::class)->findOneBy(['id' => $id]);
            case 'S120':
                return $entityManager->getRepository(EquipementS120::class)->findOneBy(['id' => $id]);
            case 'S130':
                return $entityManager->getRepository(EquipementS130::class)->findOneBy(['id' => $id]);
            case 'S140':
                return $entityManager->getRepository(EquipementS140::class)->findOneBy(['id' => $id]);
            case 'S150':
                return $entityManager->getRepository(EquipementS150::class)->findOneBy(['id' => $id]);
            case 'S160':
                return $entityManager->getRepository(EquipementS160::class)->findOneBy(['id' => $id]);
            case 'S170':
                return $entityManager->getRepository(EquipementS170::class)->findOneBy(['id' => $id]);
            default:
                return null;
        }
    }
    
    // private function getEquipmentsByClientAndAgence(string $agence, string $id, EntityManagerInterface $entityManager)
    // {
    //     switch ($agence) {
    //         case 'S10':
    //             return $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S40':
    //             return $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S50':
    //             return $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S60':
    //             return $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S70':
    //             return $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S80':
    //             return $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S100':
    //             return $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S120':
    //             return $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S130':
    //             return $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S140':
    //             return $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S150':
    //             return $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S160':
    //             return $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         case 'S170':
    //             return $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
    //         default:
    //             return [];
    //     }
    // }

    /**
     * Méthode simplifiée pour récupérer les équipements sans filtrage
     * CORRECTION: Ne plus appeler getEquipmentsByAgencyFixed avec des filtres
     */
    private function getEquipmentsByClientAndAgence(string $agence, string $clientId, EntityManagerInterface $entityManager): array
    {
        try {
            error_log("Récupération équipements pour agence: {$agence}, client: {$clientId}");
            
            // Utiliser la méthode appropriée selon l'agence
            switch ($agence) {
                case 'S10':
                    return $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S40':
                    return $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S50':
                    return $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S60':
                    return $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S70':
                    return $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S80':
                    return $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S100':
                    return $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S120':
                    return $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S130':
                    return $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S140':
                    return $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S150':
                    return $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S160':
                    return $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                case 'S170':
                    return $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $clientId], ['numero_equipement' => 'ASC']);
                default:
                    return [];
            }
            
            error_log("Trouvé " . count($equipments) . " équipements pour {$agence}/{$clientId}");
            return $equipments;
            
        } catch (\Exception $e) {
            error_log("Erreur récupération équipements {$agence}/{$clientId}: " . $e->getMessage());
            return [];
        }
    }

    private function getImageUrlForAgency(string $agencyName): string
    {
        // Assurer que cela renvoie un chemin absolu
        $basePath = 'https://www.pdf.somafi-group.fr/background/';

        // Assurez-vous d'ajouter vos conditions pour les URL spécifiques
        switch ($agencyName) {
            case 'S10':
                return 'https://www.pdf.somafi-group.fr/background/group.jpg';
            case 'S40':
                return 'https://www.pdf.somafi-group.fr/background/st-etienne.jpg';
            case 'S50':
                return 'https://www.pdf.somafi-group.fr/background/grenoble.jpg';
            case 'S60':
                return 'https://www.pdf.somafi-group.fr/background/lyon.jpg';
            case 'S70':
                return 'https://www.pdf.somafi-group.fr/background/bordeaux.jpg';
            case 'S80':
                return 'https://www.pdf.somafi-group.fr/background/paris.jpg';
            case 'S100':
                return 'https://www.pdf.somafi-group.fr/background/montpellier.jpg';
            case 'S120':
                return 'https://www.pdf.somafi-group.fr/background/portland.jpg';
            case 'S130':
                return 'https://www.pdf.somafi-group.fr/background/toulouse.jpg';
            case 'S140':
                return 'https://www.pdf.somafi-group.fr/background/grand-est.jpg';
            case 'S150':
                return 'https://www.pdf.somafi-group.fr/background/paca.jpg';
            case 'S160':
                return 'https://www.pdf.somafi-group.fr/background/rouen.jpg';
            case 'S170':
                return 'https://www.pdf.somafi-group.fr/background/rennes.jpg';
            default:
                return 'https://www.pdf.somafi-group.fr/background/group.jpg'; // Image par défaut
        }
    }

    /**
     * Méthode mise à jour pour récupérer uniquement les photos générales
     */
    private function getGeneralPhotosForEquipment($equipment, $formRepository, EntityManagerInterface $entityManager): array
    {
        // Méthode 1 : Utiliser le service de stockage
        $photos = $formRepository->getGeneralPhotoFromLocalStorage($equipment, $entityManager);
        
        // Méthode 2 : Si la première méthode ne fonctionne pas, essayer le scan
        if (empty($photos)) {
            error_log("🔄 Tentative de scan pour {$equipment->getNumeroEquipement()}");
            $photos = $formRepository->findGeneralPhotoByScanning($equipment);
        }
        
        // Méthode 3 : Fallback vers l'API si aucune photo locale trouvée
        if (empty($photos)) {
            error_log("🔄 Fallback API pour {$equipment->getNumeroEquipement()}");
            $photos = $this->fallbackToApiForGeneralPhoto($equipment, $formRepository, $entityManager);
        }
        
        return $photos;
    }

    /**
     * Fallback vers l'API pour récupérer la photo générale
     */
    private function fallbackToApiForGeneralPhoto($equipment, $formRepository, EntityManagerInterface $entityManager): array
    {
        try {
            // Récupérer toutes les photos via l'API
            $allPhotos = $formRepository->getPictureArrayByIdEquipment([], $entityManager, $equipment);
            
            // Filtrer pour ne garder que les photos générales
            $generalPhotos = [];
            foreach ($allPhotos as $photo) {
                // Ajouter un identifiant pour marquer comme photo générale
                $photo->photo_type = 'generale_api';
                $photo->equipment_number = $equipment->getNumeroEquipement();
                $generalPhotos[] = $photo;
                break; // Ne prendre que la première photo comme générale
            }
            
            return $generalPhotos;
            
        } catch (\Exception $e) {
            error_log("Erreur fallback API pour {$equipment->getNumeroEquipement()}: " . $e->getMessage());
            return [];
        }
    }
}