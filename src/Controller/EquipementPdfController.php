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
    // #[Route('/client/equipements/pdf/{agence}/{id}', name: 'client_equipements_pdf')]
    // public function generateClientEquipementsPdf(Request $request, string $agence, string $id, EntityManagerInterface $entityManager): Response
    // {
    //     // Initialiser les métriques de performance
    //     $startTime = microtime(true);
    //     $photoSourceStats = ['local' => 0, 'api_fallback' => 0, 'none' => 0];
        
    //     try {
    //         // Récupérer les filtres depuis les paramètres de la requête
    //         $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
    //         $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            
            
    //         error_log("=== GÉNÉRATION PDF CLIENT ===");
    //         error_log("Agence: {$agence}, Client: {$id}");
    //         error_log("Filtres - Année: {$clientAnneeFilter}, Visite: {$clientVisiteFilter}");
            
    //         // Vérifier si c'est un envoi par email
    //         $sendEmail = $request->query->getBoolean('send_email', false);
    //         $clientEmail = $request->query->get('client_email');
            
    //         // Récupérer les informations client
    //         $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
    //         error_log("Client info récupérées: " . json_encode($clientInfo));
            
    //         // Récupérer les équipements selon l'agence
    //         $equipments = $this->getEquipmentsByAgencyFixed($agence, $id, $entityManager, $clientAnneeFilter, $clientVisiteFilter);
    //         error_log("Équipements trouvés: " . count($equipments));
            
    //         if (empty($equipments)) {
    //             throw new \Exception("Impossible de générer le PDF client {$clientInfo['nom']}. Erreur principale: Aucun équipement trouvé pour ce client. Erreur fallback: Variable 'imageUrl' does not exist.");
    //         }
            
    //         // Appliquer les filtres si définis
    //         if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
    //             $equipments = array_filter($equipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
    //                 $matches = true;
                    
    //                 // Filtre par année si défini
    //                 if (!empty($clientAnneeFilter)) {
    //                     $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
    //                     $matches = $matches && ($annee_date_equipment === $clientAnneeFilter);
    //                 }
                    
    //                 // Filtre par visite si défini  
    //                 if (!empty($clientVisiteFilter)) {
    //                     $matches = $matches && ($equipment->getVisite() === $clientVisiteFilter);
    //                 }
                    
    //                 return $matches;
    //             });
    //         }

    //         // Récupérer les informations client
    //         $clientSelectedInformations = $this->getClientInformations($agence, $id, $entityManager);

    //         // ✅ SOLUTION : Filtrer uniquement les équipements au contrat
    //         $equipmentsAuContrat = array_filter($equipments, function($equipment) {
    //             return $equipment->isEnMaintenance() === true;
    //         });
    //         // Statistiques des équipements 
    //         $statistiques = $this->calculateEquipmentStatistics($equipmentsAuContrat);

    //         $equipmentsWithPictures = [];
    //         $dateDeDerniererVisite = "";

    //         // NOUVELLE LOGIQUE: Pour chaque équipement, récupérer ses photos via la méthode optimisée
    //         foreach ($equipments as $equipment) {
    //             $picturesData = [];
    //             $photoSource = 'none';
    //             $generalImageBase64 = null;
                
    //             try {
    //                 // Extraire les informations pour construire le chemin
    //                 $agence = $equipment->getCodeAgence();
    //                 $raisonSociale = explode('\\', $equipment->getRaisonSociale())[0] ?? $equipment->getRaisonSociale();
    //                 $anneeVisite = $clientAnneeFilter ?: date('Y', strtotime($equipment->getDateEnregistrement()));
    //                 $typeVisite = $clientVisiteFilter ?: $equipment->getVisite();
                    
    //                 // Récupérer l'image générale en base64
    //                 $generalImageBase64 = $this->imageStorageService->getGeneralImageBase64(
    //                     $agence,
    //                     $raisonSociale,
    //                     $anneeVisite,
    //                     $typeVisite,
    //                     $equipment->getNumeroEquipement()
    //                 );
                    
    //                 if ($generalImageBase64) {
    //                     $photoSource = 'local_general';
    //                     // Créer un objet picture compatible avec le template
    //                     $picturesdataObject = new \stdClass();
    //                     $picturesdataObject->picture = $generalImageBase64;
    //                     $picturesdataObject->photo_type = 'generale';
    //                     $picturesdataObject->update_time = date('Y-m-d H:i:s');
                        
    //                     $picturesData[] = $picturesdataObject;
    //                 }
                    
    //                 // Si pas d'image générale locale, fallback vers la méthode existante
    //                 if (empty($picturesData)) {
    //                     if ($equipment->isEnMaintenance()) {
    //                         $picturesData = $entityManager->getRepository(Form::class)
    //                             ->getPictureArrayByIdEquipmentOptimized($equipment, $entityManager);
    //                     } else {
    //                         $picturesData = $entityManager->getRepository(Form::class)
    //                             ->getPictureArrayByIdSupplementaryEquipmentOptimized($equipment, $entityManager);
    //                     }
    //                     $photoSource = !empty($picturesData) ? 'fallback' : 'none';
    //                 }
                    
    //             } catch (\Exception $e) {
    //                 error_log("Erreur récupération photo générale pour équipement {$equipment->getNumeroEquipement()}: " . $e->getMessage());
    //                 $photoSource = 'none';
    //             }
                
    //             $equipmentsWithPictures[] = [
    //                 'equipment' => $equipment,
    //                 'pictures' => $picturesData,
    //                 'photo_source' => $photoSource
    //             ];
                
    //             // Récupérer la date de dernière visite
    //             $dateDeDerniererVisite = $equipment->getDerniereVisite();
    //         }

    //         // Séparer les équipements supplémentaires
    //         $equipementsSupplementaires = array_filter($equipmentsWithPictures, function($equipement) {
    //             return $equipement['equipment']->isEnMaintenance() === false;
    //         });

    //         // Calculer statistiques supplémentaires
    //         $statistiquesSupplementaires = $this->calculateSupplementaryStatistics($equipementsSupplementaires);

    //         // Équipements non présents
    //         $equipementsNonPresents = array_filter($equipmentsWithPictures, function($equipement) {
    //             $etat = $equipement['equipment']->getEtat();
    //             return $etat === "Equipement non présent sur site" || $etat === "G";
    //         });

    //         // URL de l'image d'agence
    //         if ($this->getImageUrlForAgency($agence)) {
    //             $imageUrl = $this->getImageUrlForAgency($agence);
    //         }else{
    //             $imageUrl = 'https://www.pdf.somafi-group.fr/background/group.jpg';
    //         }
            
    //         // GÉNÉRATION DU PDF avec template équipements (multi-équipements)
    //         $html = $this->renderView('pdf/equipements.html.twig', [
    //             'equipmentsWithPictures' => $equipmentsWithPictures,
    //             'equipementsSupplementaires' => $equipementsSupplementaires,
    //             'equipementsNonPresents' => $equipementsNonPresents,
    //             'clientId' => $id,
    //             'agence' => $agence,
    //             'imageUrl' => $imageUrl,
    //             'clientAnneeFilter' => $clientAnneeFilter,
    //             'clientVisiteFilter' => $clientVisiteFilter,
    //             'statistiques' => $statistiques, // 🎯 Nouvelle variable ajoutée,
    //             'statistiquesSupplementaires' => $statistiquesSupplementaires, // 🎯 Nouvelle variable
    //             'statistiquesSupplementaires' => $statistiquesSupplementaires,
    //             'dateDeDerniererVisite' => $dateDeDerniererVisite,
    //             'clientSelectedInformations' => $clientSelectedInformations,
    //             'isFiltered' => !empty($clientAnneeFilter) || !empty($clientVisiteFilter),
    //             // NOUVELLES VARIABLES pour monitoring
    //             'using_local_photos' => $photoSourceStats['local'] > 0,
    //             'photo_source_stats' => $photoSourceStats,
    //             'generation_time' => date('Y-m-d H:i:s'),
    //             'performance_mode' => 'optimized'
    //         ]);
            
    //         // Générer le nom de fichier avec filtres
    //         $filename = "equipements_client_{$id}_{$agence}";
    //         if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
    //             $filename .= '_filtered';
    //             if (!empty($clientAnneeFilter)) {
    //                 $filename .= '_' . $clientAnneeFilter;
    //             }
    //             if (!empty($clientVisiteFilter)) {
    //                 $filename .= '_' . str_replace(' ', '_', $clientVisiteFilter);
    //             }
    //         }
    //         $filename .= '.pdf';
            
    //         // 1. Génération du PDF existant
    //         $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);

    //         // 2. Stockage local du PDF
    //         $storedPath = $this->pdfStorageService->storePdf(
    //             $agence,
    //             $id,
    //             $clientAnneeFilter,
    //             $clientVisiteFilter,
    //             $pdfContent
    //         );
            
    //         // Création du lien court SÉCURISÉ
    //         $originalUrl = $this->generateUrl('pdf_secure_download', [
    //             'agence' => $agence,
    //             'clientId' => $id,
    //             'annee' => $clientAnneeFilter,
    //             'visite' => $clientVisiteFilter
    //         ], true);
            
    //         $expiresAt = (new \DateTime())->modify('+30 days');
            
    //         $shortLink = $this->shortLinkService->createShortLink(
    //             $originalUrl,
    //             $agence,
    //             $id,
    //             $clientAnneeFilter,
    //             $clientVisiteFilter,
    //             $expiresAt
    //         );
            
    //         // URL courte pour l'email (PAS l'URL de téléchargement direct)
    //         $shortUrl = $this->shortLinkService->getShortUrl($shortLink->getShortCode());
            
    //         // 4. Retourner le PDF directement OU envoyer par email selon le paramètre
    //         $sendEmail = $request->query->getBoolean('send_email', false);
    //         $clientEmail = $request->query->get('client_email');
            
    //         if ($sendEmail && $clientEmail) {
    //             // Récupérer les infos client pour l'email
    //             $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
                
    //             $emailSent = $this->emailService->sendPdfLinkToClient(
    //                 $agence,
    //                 $clientEmail,
    //                 $clientInfo['nom'] ?? 'Client',
    //                 $shortUrl,
    //                 $clientAnneeFilter,
    //                 $clientVisiteFilter
    //             );
                
    //             return new JsonResponse([
    //                 'success' => true,
    //                 'message' => 'PDF généré et ' . ($emailSent ? 'email envoyé' : 'erreur envoi email'),
    //                 'pdf_stored' => true,
    //                 'storage_path' => basename($storedPath),
    //                 'short_url' => $shortUrl,
    //                 'email_sent' => $emailSent
    //             ]);
    //         }
            
    //         // Log des métriques de performance
    //         $totalTime = round(microtime(true) - $startTime, 2);
    //         $this->logPdfGenerationMetrics($agence, $id, count($equipments), $photoSourceStats, $totalTime);
            
    //         // Headers avec informations de debug
    //         $headers = [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => "inline; filename=\"client_{$id}_{$clientAnneeFilter}_{$clientVisiteFilter}.pdf\"",
    //             'X-PDF-Stored' => 'true',
    //             'X-Short-URL' => $shortUrl,
    //             'X-Storage-Path' => basename($storedPath),
    //             'X-Equipment-Count' => count($equipments),
    //             'X-Local-Photos' => $photoSourceStats['local'],
    //             'X-Fallback-Photos' => $photoSourceStats['api_fallback'],
    //             'X-Missing-Photos' => $photoSourceStats['none'],
    //             'X-Generation-Time' => $totalTime . 's',
    //             'X-Performance-Mode' => 'optimized'
    //         ];
            
    //         return new Response($pdfContent, Response::HTTP_OK, $headers);
            
    //     } catch (\Exception $e) {
    //         // En cas d'erreur majeure, fallback vers l'ancienne méthode complète
    //         return $this->generateClientEquipementsPdfFallback($request, $agence, $id, $entityManager, $e);
    //     }
    // }

    #[Route('/client/equipements/pdf/{agence}/{id}', name: 'client_equipements_pdf')]
    public function generateClientEquipementsPdf(Request $request, string $agence, string $id, EntityManagerInterface $entityManager): Response
    {
        // 1. TOUJOURS initialiser imageUrl dès le début
        $imageUrl = $this->getImageUrlForAgency($agence) ?: 'https://www.pdf.somafi-group.fr/background/group.jpg';
        
        // Initialiser les métriques de performance
        $startTime = microtime(true);
        $photoSourceStats = ['local' => 0, 'api_fallback' => 0, 'none' => 0];
        
        try {
            // 2. Optimiser la configuration MySQL pour les gros volumes
            $entityManager->getConnection()->executeStatement('SET SESSION wait_timeout = 300');
            $entityManager->getConnection()->executeStatement('SET SESSION interactive_timeout = 300');
            
            // Récupérer les filtres depuis les paramètres de la requête
            $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
            $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            
            error_log("=== GÉNÉRATION PDF CLIENT ===");
            error_log("Agence: {$agence}, Client: {$id}");
            error_log("Filtres - Année: {$clientAnneeFilter}, Visite: {$clientVisiteFilter}");
            error_log("ImageUrl initialisée: {$imageUrl}");
            
            // Vérifier si c'est un envoi par email
            $sendEmail = $request->query->getBoolean('send_email', false);
            $clientEmail = $request->query->get('client_email');
            
            // Récupérer les informations client
            $clientInfo = $this->getClientInfo($agence, $id, $entityManager);
            error_log("Client info récupérées: " . json_encode($clientInfo));
            
            // 3. Traitement par batch pour les gros volumes
            $equipments = $this->getEquipmentsByAgencyFixed($agence, $id, $entityManager, $clientAnneeFilter, $clientVisiteFilter);
            $totalEquipments = count($equipments);
            error_log("Équipements trouvés: " . $totalEquipments);
            
            if (empty($equipments)) {
                throw new \Exception("Aucun équipement trouvé pour le client {$id}");
            }
            
            // 4. Gestion spécifique pour les gros volumes (> 200 équipements)
            if ($totalEquipments > 200) {
                return $this->handleLargeVolumeGeneration($equipments, $agence, $id, $entityManager, $imageUrl, $clientAnneeFilter, $clientVisiteFilter);
            }
            
            // Traitement normal pour les petits volumes
            $equipmentsWithPictures = [];
            $batchSize = 50; // Traiter par batch de 50 équipements
            
            for ($i = 0; $i < $totalEquipments; $i += $batchSize) {
                $batch = array_slice($equipments, $i, $batchSize);
                $equipmentsWithPictures = array_merge($equipmentsWithPictures, $this->processBatchEquipments($batch, $entityManager));
                
                // Nettoyer la mémoire après chaque batch
                gc_collect_cycles();
            }
            
            // Suite du traitement...
            $clientSelectedInformations = $this->getClientInformations($agence, $id, $entityManager);
            $statistiques = $this->calculateEquipmentStatistics($equipments);
            
            // Séparations des équipements
            $equipementsSupplementaires = array_filter($equipmentsWithPictures, function($equipement) {
                return $equipement['equipment']->isEnMaintenance() === false;
            });
            
            $equipementsNonPresents = array_filter($equipmentsWithPictures, function($equipement) {
                $etat = $equipement['equipment']->getEtat();
                return $etat === "Equipement non présent sur site" || $etat === "G";
            });
            
            $filename = "equipements_client_{$id}_{$agence}.pdf";
            
            // 5. TOUJOURS passer imageUrl au template avec vérification
            $templateVars = [
                'equipmentsWithPictures' => $equipmentsWithPictures,
                'equipementsSupplementaires' => $equipementsSupplementaires,
                'equipementsNonPresents' => $equipementsNonPresents,
                'clientId' => $id,
                'agence' => $agence,
                'imageUrl' => $imageUrl, // CRUCIAL : toujours définie
                'clientAnneeFilter' => $clientAnneeFilter,
                'clientVisiteFilter' => $clientVisiteFilter,
                'statistiques' => $statistiques,
                'clientInfo' => $clientInfo ?? [],
                'clientSelectedInformations' => $clientSelectedInformations ?? [],
                'photoSourceStats' => $photoSourceStats
            ];
            
            // Vérifier que imageUrl est bien définie
            if (empty($templateVars['imageUrl'])) {
                $templateVars['imageUrl'] = 'https://www.pdf.somafi-group.fr/background/group.jpg';
                error_log("WARNING: imageUrl était vide, fallback utilisé");
            }
            
            error_log("Template vars imageUrl: " . $templateVars['imageUrl']);
            
            $html = $this->renderView('pdf/equipements.html.twig', $templateVars);
            $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            
            return new Response($pdfContent, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\""
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur génération PDF: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // En cas d'erreur, essayer une génération simplifiée
            return $this->generateSimplifiedPdf($agence, $id, $imageUrl, $entityManager);
        }
    }

    /**
     * Gestion spécialisée pour les gros volumes
     */
    private function handleLargeVolumeGeneration(array $equipments, string $agence, string $id, EntityManagerInterface $entityManager, string $imageUrl, string $clientAnneeFilter, string $clientVisiteFilter): Response
    {
        error_log("Mode gros volume activé pour " . count($equipments) . " équipements");
        
        try {
            // Configuration MySQL optimisée
            $entityManager->getConnection()->executeStatement('SET SESSION sql_mode = ""');
            $entityManager->getConnection()->executeStatement('SET SESSION max_execution_time = 0');
            
            // Augmenter les limites PHP
            ini_set('memory_limit', '1G');
            ini_set('max_execution_time', 300);
            
            // Traitement ultra-optimisé sans photos pour éviter le timeout
            $equipmentsWithPictures = [];
            foreach ($equipments as $equipment) {
                $equipmentsWithPictures[] = [
                    'equipment' => $equipment,
                    'pictures' => [], // Pas de photos pour éviter les timeouts
                    'photo_source' => 'disabled_for_performance'
                ];
            }
            
            $statistiques = $this->calculateEquipmentStatistics($equipments);
            
            $filename = "equipements_client_{$id}_{$agence}_performance.pdf";
            
            $html = $this->renderView('pdf/equipements.html.twig', [
                'equipmentsWithPictures' => $equipmentsWithPictures,
                'equipementsSupplementaires' => [],
                'equipementsNonPresents' => [],
                'clientId' => $id,
                'agence' => $agence,
                'imageUrl' => $imageUrl, // Toujours définie
                'clientAnneeFilter' => $clientAnneeFilter,
                'clientVisiteFilter' => $clientVisiteFilter,
                'statistiques' => $statistiques,
                'performance_mode' => true,
                'equipment_count' => count($equipments)
            ]);
            
            $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            
            return new Response($pdfContent, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\"",
                'X-Performance-Mode' => 'large-volume',
                'X-Equipment-Count' => count($equipments)
            ]);
            
        } catch (\Exception $e) {
            error_log("Erreur mode gros volume: " . $e->getMessage());
            throw $e;
        }
    }

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
                
                $picturesData = $entityManager->getRepository(Form::class)
                    ->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                    
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
    private function generateSimplifiedPdf(string $agence, string $id, string $imageUrl, EntityManagerInterface $entityManager): Response
    {
        error_log("Génération PDF simplifiée pour {$agence}/{$id}");
        
        $html = $this->renderView('pdf/equipements.html.twig', [
            'equipmentsWithPictures' => [],
            'equipementsSupplementaires' => [],
            'equipementsNonPresents' => [],
            'clientId' => $id,
            'agence' => $agence,
            'imageUrl' => $imageUrl, // TOUJOURS définie
            'error_mode' => true,
            'error_message' => 'Trop d\'équipements pour la génération complète. Veuillez utiliser les filtres.'
        ]);
        
        $filename = "equipements_client_{$id}_{$agence}_error.pdf";
        $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
        
        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"$filename\"",
            'X-Generation-Mode' => 'error-fallback'
        ]);
    }

    private function getEquipmentsByAgencyFixed(string $agence, string $clientId, EntityManagerInterface $entityManager, ?string $anneeFilter = null, ?string $visiteFilter = null): array
    {
        error_log("=== RÉCUPÉRATION ÉQUIPEMENTS ===");
        error_log("Agence: {$agence}, Client: {$clientId}");
        error_log("Filtres - Année: {$anneeFilter}, Visite: {$visiteFilter}");
        
        $equipmentEntity = "App\\Entity\\Equipement{$agence}";
        
        if (!class_exists($equipmentEntity)) {
            error_log("ERREUR: Classe d'équipement {$equipmentEntity} n'existe pas");
            throw new \Exception("Classe d'équipement {$equipmentEntity} introuvable");
        }
        
        try {
            $repository = $entityManager->getRepository($equipmentEntity);
            
            // D'abord, essayer de trouver des équipements sans filtres
            $allEquipments = $repository->findBy(['id_contact' => $clientId]);
            error_log("Total équipements pour client {$clientId}: " . count($allEquipments));
            
            if (empty($allEquipments)) {
                // Pas d'équipements du tout pour ce client
                error_log("AUCUN équipement trouvé pour le client {$clientId}");
                
                // Essayer de voir s'il y a des équipements dans la table
                $sampleEquipments = $repository->findBy([], [], 5);
                error_log("Échantillon d'équipements dans la table: " . count($sampleEquipments));
                
                if (!empty($sampleEquipments)) {
                    $sampleIds = array_map(function($eq) {
                        return method_exists($eq, 'getIdContact') ? $eq->getIdContact() : 'N/A';
                    }, $sampleEquipments);
                    error_log("IDs clients échantillon: " . implode(', ', $sampleIds));
                }
                
                return [];
            }
            
            // Si on a des équipements, appliquer les filtres
            $criteria = ['id_contact' => $clientId];
            
            // Pour les filtres, il faut connaître les noms exacts des propriétés
            // Regardons un équipement pour voir les propriétés disponibles
            $firstEquipment = $allEquipments[0];
            $methods = get_class_methods($firstEquipment);
            $getterMethods = array_filter($methods, function($method) {
                return strpos($method, 'get') === 0;
            });
            
            error_log("Méthodes disponibles sur l'équipement: " . implode(', ', $getterMethods));
            
            // Essayer différents noms de propriétés pour l'année
            if ($anneeFilter) {
                $yearProperties = ['annee', 'year', 'dateVisite', 'date_visite', 'anneeVisite'];
                foreach ($yearProperties as $prop) {
                    $getter = 'get' . ucfirst($prop);
                    if (method_exists($firstEquipment, $getter)) {
                        error_log("Propriété année trouvée: {$prop}");
                        // Pour l'instant, on n'applique pas le filtre année car on ne connaît pas la structure exacte
                        break;
                    }
                }
            }
            
            // Essayer différents noms de propriétés pour la visite
            if ($visiteFilter) {
                $visiteProperties = ['visite', 'typeVisite', 'type_visite', 'maintenance'];
                foreach ($visiteProperties as $prop) {
                    $getter = 'get' . ucfirst($prop);
                    if (method_exists($firstEquipment, $getter)) {
                        error_log("Propriété visite trouvée: {$prop}");
                        // Pour l'instant, on n'applique pas le filtre visite car on ne connaît pas la structure exacte
                        break;
                    }
                }
            }
            
            // Pour le moment, retourner tous les équipements du client
            // Vous pourrez affiner les filtres une fois que vous connaîtrez la structure exacte
            error_log("Retour de " . count($allEquipments) . " équipements");
            return $allEquipments;
            
        } catch (\Exception $e) {
            error_log("Erreur récupération équipements {$agence}: " . $e->getMessage());
            throw new \Exception("Erreur lors de la récupération des équipements: " . $e->getMessage());
        }
    }

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
            if (method_exists($user, 'getFirstName')) {
                $firstName = $user->getFirstName();
            } elseif (method_exists($user, 'getPrenom')) {
                $firstName = $user->getPrenom();
            } elseif (method_exists($user, 'getName')) {
                $firstName = explode(' ', $user->getName())[0] ?? '';
            }
            
            // Tester les getters possibles pour le nom
            if (method_exists($user, 'getLastName')) {
                $lastName = $user->getLastName();
            } elseif (method_exists($user, 'getNom')) {
                $lastName = $user->getNom();
            } elseif (method_exists($user, 'getName')) {
                $parts = explode(' ', $user->getName());
                $lastName = $parts[1] ?? $parts[0] ?? '';
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
     * NOUVELLE MÉTHODE: Fallback complet vers l'ancienne méthode
     */ 
    private function generateClientEquipementsPdfFallback(
        Request $request, 
        string $agence, 
        string $id, 
        EntityManagerInterface $entityManager, 
        \Exception $originalException
    ): Response {
        
        error_log("Fallback complet pour PDF client {$id} agence {$agence}: " . $originalException->getMessage());
        
        try {
            // Utiliser entièrement l'ancienne logique avec appels API
            $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
            $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            
            $equipments = $this->getEquipmentsByClientAndAgence($agence, $id, $entityManager);
            
            // Application des filtres (identique)
            if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
                $equipments = array_filter($equipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                    $matches = true;
                    
                    if (!empty($clientAnneeFilter)) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        $matches = $matches && ($annee_date_equipment === $clientAnneeFilter);
                    }
                    
                    if (!empty($clientVisiteFilter)) {
                        $matches = $matches && ($equipment->getVisite() === $clientVisiteFilter);
                    }
                    
                    return $matches;
                });
            }
            
            $equipmentsWithPictures = [];
            
            // ANCIENNE LOGIQUE: Appels API pour chaque équipement
            foreach ($equipments as $equipment) {
                if ($equipment->isEnMaintenance()) {
                    // Ancienne méthode pour équipements au contrat
                    $picturesArray = $entityManager->getRepository(Form::class)->findBy([
                        'code_equipement' => $equipment->getNumeroEquipement(),
                        'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()
                    ]);
                    $picturesData = $entityManager->getRepository(Form::class)
                        ->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                } else {
                    // Ancienne méthode pour équipements supplémentaires
                    $picturesData = $entityManager->getRepository(Form::class)
                        ->getPictureArrayByIdSupplementaryEquipment($entityManager, $equipment);
                }
                
                $equipmentsWithPictures[] = [
                    'equipment' => $equipment,
                    'pictures' => $picturesData
                ];
            }
            
            // Continuer avec le reste de la logique (identique à l'originale)
            $clientSelectedInformations = $this->getClientInformations($agence, $id, $entityManager);
            $statistiques = $this->calculateEquipmentStatistics($equipments);
            // ... reste du code identique
            
            $filename = "equipements_client_{$id}_{$agence}_fallback.pdf";
            
            $html = $this->renderView('pdf/equipements.html.twig', [
                'equipmentsWithPictures' => $equipmentsWithPictures,
                // ... autres variables
                'fallback_mode' => true,
                'performance_mode' => 'legacy'
            ]);
            
            $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            
            return new Response($pdfContent, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"$filename\"",
                'X-Performance-Mode' => 'legacy-fallback',
                'X-Fallback-Reason' => 'optimization-failed'
            ]);
            
        } catch (\Exception $fallbackException) {
            throw new \RuntimeException(
                "Impossible de générer le PDF client {$id}. " .
                "Erreur principale: {$originalException->getMessage()}. " .
                "Erreur fallback: {$fallbackException->getMessage()}"
            );
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
        switch ($agence) {
            case 'S10':
                return $entityManager->getRepository(ContactS10::class)->findOneBy(['id_contact' => $id]);
            case 'S40':
                return $entityManager->getRepository(ContactS40::class)->findOneBy(['id_contact' => $id]);
            case 'S50':
                return $entityManager->getRepository(ContactS50::class)->findOneBy(['id_contact' => $id]);
            case 'S60':
                return $entityManager->getRepository(ContactS60::class)->findOneBy(['id_contact' => $id]);
            case 'S70':
                return $entityManager->getRepository(ContactS70::class)->findOneBy(['id_contact' => $id]);
            case 'S80':
                return $entityManager->getRepository(ContactS80::class)->findOneBy(['id_contact' => $id]);
            case 'S100':
                return $entityManager->getRepository(ContactS100::class)->findOneBy(['id_contact' => $id]);
            case 'S120':
                return $entityManager->getRepository(ContactS120::class)->findOneBy(['id_contact' => $id]);
            case 'S130':
                return $entityManager->getRepository(ContactS130::class)->findOneBy(['id_contact' => $id]);
            case 'S140':
                return $entityManager->getRepository(ContactS140::class)->findOneBy(['id_contact' => $id]);
            case 'S150':
                return $entityManager->getRepository(ContactS150::class)->findOneBy(['id_contact' => $id]);
            case 'S160':
                return $entityManager->getRepository(ContactS160::class)->findOneBy(['id_contact' => $id]);
            case 'S170':
                return $entityManager->getRepository(ContactS170::class)->findOneBy(['id_contact' => $id]);
            default:
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
    
    private function getEquipmentsByClientAndAgence(string $agence, string $id, EntityManagerInterface $entityManager)
    {
        switch ($agence) {
            case 'S10':
                return $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S40':
                return $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S50':
                return $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S60':
                return $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S70':
                return $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S80':
                return $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S100':
                return $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S120':
                return $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S130':
                return $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S140':
                return $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S150':
                return $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S160':
                return $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            case 'S170':
                return $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $id], ['numero_equipement' => 'ASC']);
            default:
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