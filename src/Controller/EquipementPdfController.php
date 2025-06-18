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
use App\Service\PdfGenerator;
use App\Entity\EquipementS100;
use App\Entity\EquipementS120;
use App\Entity\EquipementS130;
use App\Entity\EquipementS140;
use App\Entity\EquipementS150;
use App\Entity\EquipementS160;
use App\Entity\EquipementS170;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EquipementPdfController extends AbstractController
{
    private $pdfGenerator;
    
    public function __construct(PdfGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
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
     * 
     */
    #[Route('/client/equipements/pdf/{agence}/{id}', name: 'client_equipements_pdf')]
    public function generateClientEquipementsPdf(Request $request, string $agence, string $id, EntityManagerInterface $entityManager): Response
    {
        try {
            // Augmenter les limites pour éviter les timeouts
            ini_set('memory_limit', '1G');
            set_time_limit(300);
            
            // Récupérer les filtres
            $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
            $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            
            // Récupérer les équipements
            $equipments = $this->getEquipmentsByClientAndAgence($agence, $id, $entityManager);
            
            if (empty($equipments)) {
                throw $this->createNotFoundException('Aucun équipement trouvé pour ce client');
            }
            
            // Appliquer les filtres
            if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
                $equipments = array_filter($equipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                    $matches = true;
                    
                    if (!empty($clientAnneeFilter)) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        $matches = $matches && ($annee_date_equipment == $clientAnneeFilter);
                    }
                    
                    if (!empty($clientVisiteFilter)) {
                        $matches = $matches && ($equipment->getVisite() == $clientVisiteFilter);
                    }
                    
                    return $matches;
                });
            }
            
            if (empty($equipments)) {
                throw $this->createNotFoundException('Aucun équipement trouvé avec les critères sélectionnés');
            }
            
            // Limiter le nombre d'équipements pour éviter les timeouts
            $maxEquipments = 50; // Limiter à 50 équipements max
            if (count($equipments) > $maxEquipments) {
                $equipments = array_slice($equipments, 0, $maxEquipments);
                $this->addFlash('warning', "Le PDF a été limité aux {$maxEquipments} premiers équipements pour éviter les problèmes de génération.");
            }
            
            // Récupérer les informations client
            $clientSelectedInformations = $this->getClientInformations($agence, $id, $entityManager);
            
            // Calculer les statistiques
            $statistiques = $this->calculateStatistics($equipments);
            
            // Récupérer les photos avec limitation
            $equipmentsWithPictures = [];
            $dateDeDerniererVisite = null;
            
            foreach ($equipments as $equipment) {
                // Limiter à 2 photos max par équipement pour éviter les surcharges
                $picturesArray = $entityManager->getRepository(Form::class)->findBy([
                    'code_equipement' => $equipment->getNumeroEquipement(), 
                    'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()
                ], [], 2); // Limiter à 2 résultats
                
                $picturesData = [];
                foreach ($picturesArray as $pictureForm) {
                    try {
                        $pictures = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment([$pictureForm], $entityManager, $equipment);
                        $picturesData = array_merge($picturesData, $pictures);
                        // Limiter à 2 photos par équipement
                        if (count($picturesData) >= 2) {
                            break;
                        }
                    } catch (\Exception $e) {
                        // Ignorer les erreurs de récupération d'images
                        error_log("Erreur récupération image: " . $e->getMessage());
                    }
                }
                
                $equipmentsWithPictures[] = [
                    'equipment' => $equipment,
                    'pictures' => $picturesData
                ];
                
                if (!$dateDeDerniererVisite) {
                    $dateDeDerniererVisite = $equipment->getDerniereVisite();
                }
            }
            
            // Calculer les catégories d'équipements
            $equipementsSupplementaires = array_filter($equipmentsWithPictures, function($equipement) {
                return $equipement['equipment']->isEnMaintenance() === false;
            });
            
            $equipementsNonPresents = array_filter($equipmentsWithPictures, function($equipement) {
                $etat = $equipement['equipment']->getEtat();
                return $etat === "Equipement non présent sur site" || $etat === "G";
            });
            
            // Utiliser le template optimisé
            $html = $this->renderView('pdf/equipements_optimized.html.twig', [
                'equipmentsWithPictures' => $equipmentsWithPictures,
                'equipementsSupplementaires' => $equipementsSupplementaires,
                'equipementsNonPresents' => $equipementsNonPresents,
                'clientId' => $id,
                'agence' => $agence,
                'clientAnneeFilter' => $clientAnneeFilter,
                'clientVisiteFilter' => $clientVisiteFilter,
                'statistiques' => $statistiques,
                'dateDeDerniererVisite' => $dateDeDerniererVisite,
                'clientSelectedInformations' => $clientSelectedInformations,
                'isFiltered' => !empty($clientAnneeFilter) || !empty($clientVisiteFilter)
            ]);
            
            // Générer le nom de fichier
            $filename = "equipements_client_{$id}_{$agence}";
            if (!empty($clientAnneeFilter) || !empty($clientVisiteFilter)) {
                $filename .= '_filtered';
                if (!empty($clientAnneeFilter)) {
                    $filename .= '_' . $clientAnneeFilter;
                }
                if (!empty($clientVisiteFilter)) {
                    $filename .= '_' . str_replace(' ', '_', $clientVisiteFilter);
                }
            }
            $filename .= '.pdf';
            
            // Générer le PDF avec gestion d'erreur
            try {
                $pdfContent = $this->pdfGenerator->generatePdf($html, $filename);
            } catch (\Exception $e) {
                // En cas d'erreur, essayer avec une version encore plus simplifiée
                error_log("Erreur génération PDF: " . $e->getMessage());
                
                // Template de fallback sans images
                $fallbackHtml = $this->renderView('pdf/equipements_fallback.html.twig', [
                    'equipments' => $equipments,
                    'clientId' => $id,
                    'agence' => $agence,
                    'clientAnneeFilter' => $clientAnneeFilter,
                    'clientVisiteFilter' => $clientVisiteFilter,
                    'statistiques' => $statistiques,
                    'dateDeDerniererVisite' => $dateDeDerniererVisite,
                    'clientSelectedInformations' => $clientSelectedInformations,
                    'isFiltered' => !empty($clientAnneeFilter) || !empty($clientVisiteFilter)
                ]);
                
                $pdfContent = $this->pdfGenerator->generatePdf($fallbackHtml, $filename);
            }
            
            return new Response(
                $pdfContent,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "inline; filename=\"$filename\""
                ]
            );
            
        } catch (\Exception $e) {
            // Log l'erreur
            error_log("Erreur génération PDF: " . $e->getMessage());
            
            // Retourner une réponse d'erreur utilisateur
            $this->addFlash('error', 'Impossible de générer le PDF. Essayez de réduire le nombre d\'équipements ou contactez le support.');
            return $this->redirectToRoute('client_equipements_pdf'); // Remplacez par votre route
        }
    }

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

    private function calculateStatistics($equipments)
    {
        $etatsCount = [];
        
        foreach ($equipments as $equipment) {
            $etat = $equipment->getEtat();
            if ($etat) {
                if (!isset($etatsCount[$etat])) {
                    $etatsCount[$etat] = 0;
                }
                $etatsCount[$etat]++;
            }
        }
        
        return [
            'etatsCount' => $etatsCount
        ];
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
                return $basePath . 'group.jpg';
            case 'S40':
                return $basePath . 'st-etienne.jpg';
            case 'S50':
                return $basePath . 'grenoble.jpg';
            case 'S60':
                return $basePath . 'lyon.jpg';
            case 'S70':
                return $basePath . 'bordeaux.jpg';
            case 'S80':
                return $basePath . 'paris.jpg';
            case 'S100':
                return $basePath . 'montpellier.jpg';
            case 'S120':
                return $basePath . 'portland.jpg';
            case 'S130':
                return $basePath . 'toulouse.jpg';
            case 'S140':
                return $basePath . 'grand-est.jpg';
            case 'S150':
                return $basePath . 'paca.jpg';
            case 'S160':
                return $basePath . 'rouen.jpg';
            case 'S170':
                return $basePath . 'rennes.jpg';
            default:
                return $basePath . 'default.jpg'; // Image par défaut
        }
    }
}