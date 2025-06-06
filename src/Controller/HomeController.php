<?php

namespace App\Controller;

use DateTime;
use DOMDocument;
use DateInterval;
use App\Entity\Form;
use App\Entity\Agency;
use App\Entity\User;
use App\Form\AgencyType;
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
use App\Entity\EquipementS100;
use App\Entity\EquipementS120;
use App\Entity\EquipementS130;
use App\Entity\EquipementS140;
use App\Entity\EquipementS150;
use App\Entity\EquipementS160;
use App\Entity\EquipementS170;
use Doctrine\ORM\EntityManager;
use App\Repository\HomeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_front')]
    public function index(CacheInterface $cache,EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, HomeRepository $homeRepository): Response
    {
        // GET CONTACTS KIZEO BY AGENCY
        
        $clientsGroup = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_GROUP"]);
        $clientsStEtienne = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_ST_ETIENNE"]);
        $clientsGrenoble = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_GRENOBLE"]);
        $clientsLyon = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_LYON"]);
        $clientsBordeaux = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_BORDEAUX"]);
        $clientsParisNord = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_PARIS_NORD"]);
        $clientsMontpellier = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_MONTPELLIER"]);
        $clientsHautsDeFrance = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_HAUTS_DE_FRANCE"]);
        $clientsToulouse = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_TOULOUSE"]);
        $clientsEpinal = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_EPINAL"]);
        $clientsPaca = $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_PACA"]);
        $clientsRouen =  $homeRepository->getListClientFromKizeoById($_ENV["PROD_CLIENTS_ROUEN"]);
        $clientsRennes = $homeRepository->getListClientFromKizeoById($_ENV["TEST_CLIENTS_RENNES"]);
        
        // GET AGENCIES FROM DATABASE
        $agenciesArray =  $cache->get('agency_array', function (ItemInterface $item) use ($entityManager)  {
            $item->expiresAfter(900); // 15 minutes in cache
            $agencies = $entityManager->getRepository(Agency::class)->findAll();
            return $agencies;
        });

        // GET CLIENT SELECTED INFORMATION BY AGENCY BY HIS RAISON_SOCIALE
        $clientSelectedInformations  = "";
        // GET CLIENT SELECTED EQUIPMENTS BY AGENCY BY HIS ID_CONTACT
        $clientSelectedEquipments  = [];
        $clientSelectedEquipmentsFiltered = [];
        // GET VALUE OF AGENCY SELECTED
        $agenceSelected = "";
        // // GET VALUE OF CLIENT SELECTED
        $clientSelected = "";
        // GET directories and files OF CLIENT SELECTED
        $directoriesLists = [];

        // Récupération de l'agence sélectionnée nécessaire pour charger la liste client de l'agence
        if(isset($_POST['submitAgence'])){  
            if(!empty($_POST['agenceName'])) {
                $agenceSelected = $_POST['agenceName'];
            } else {  
                echo 'Please select the value.';
            }  
        }
        // Récupération du client sélectionné et SET de $agenceSelected par les 4 derniers caractères de $clientSelected
        if(isset($_POST['submitClient'])){  
            if(!empty($_POST['clientName'])) {  
                $clientSelected = $_POST['clientName'];
                $agenceSelected = substr($clientSelected, -4);
                $agenceSelected = trim($agenceSelected);
            } else {  
                echo 'Please select the value.';
            }  
        }
        
        // ENLEVER LE NOM DE L'AGENCE ET L'ESPACE A LA FIN DU NOM DU CLIENT SÉLECTIONNÉ
        $idClientSelected = "";
        if ($clientSelected != "") {
            $clientSelectedSplitted = preg_split("/[-]/",$clientSelected);
            $idClientSelected = $clientSelectedSplitted[0];
            $clientSelected = trim($clientSelectedSplitted[1]);
            $idClientSelected = rtrim($idClientSelected, "\ ");
        }
        $visiteDuClient = "";
        if ($clientSelected != NULL) {
            switch ($agenceSelected) {
                case 'S10':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS10::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S10':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS10::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S40':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS40::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S40':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS40::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S50':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS50::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S50':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS50::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S60':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS60::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S60':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS60::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S70':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS70::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S70':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS70::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S80':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS80::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S80':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS80::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S100':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS100::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S100':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS100::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S120':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS120::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S120':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS120::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S130':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS130::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S130':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS130::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S140':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS140::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Vérifier si une date a été trouvée
                    if ($absoluteLatestVisitDate !== null) {
                        // Calculer la date limite inférieure (2 mois avant la date la plus récente)
                        $twoMonthsAgo = clone $absoluteLatestVisitDate;
                        $twoMonthsAgo->modify('-2 months');
                        $twoMonthsAgo = $twoMonthsAgo->format('Y-m-d');
                    } else {
                        // Gérer le cas où aucune date n'a été trouvée
                        $twoMonthsAgo = null;
                    }

                    // Filtrer les équipements dans l'intervalle
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($absoluteLatestVisitDate, $twoMonthsAgo) {
                        $equipmentDate = $equipment->getDerniereVisite();
                        
                        return $equipmentDate !== null && 
                            $equipmentDate <= $absoluteLatestVisitDate && 
                            $equipmentDate >= $twoMonthsAgo;
                    });
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $currentVisit = "";
                    if (isset($clientSelectedEquipmentsFiltered[1])) {
                        $currentVisit = $clientSelectedEquipmentsFiltered[1]->getVisite();
                    }else if (isset($clientSelectedEquipmentsFiltered[0])){
                        $currentVisit = $clientSelectedEquipmentsFiltered[0]->getVisite();
                    }else {
                        $currentVisit = null;
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $currentVisit, $agenceSelected, $dateArray);
                    break;
                case ' S140':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS140::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S150':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS150::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S150':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS150::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S160':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS160::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S160':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS160::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case 'S170':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS170::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                case ' S170':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS170::class)->findOneBy(['id_contact' => $idClientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                    $dateArray = [];
                    // Trouver la date de visite la plus récente
                    $absoluteLatestVisitDate = null;
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $currentDate = new DateTime($equipment->getDerniereVisite());
                            
                            // Comparer et garder la date la plus récente
                            if ($absoluteLatestVisitDate === null || $currentDate > $absoluteLatestVisitDate) {
                                $absoluteLatestVisitDate = $currentDate;
                            }
                        }
                    }

                    // Déterminer l'année et la visite par défaut
                    $defaultYear = $absoluteLatestVisitDate ? $absoluteLatestVisitDate->format('Y') : '';
                    $defaultVisit = "";

                    // Trouver la visite correspondant à la date la plus récente
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() !== null) {
                            $equipmentDate = new DateTime($equipment->getDerniereVisite());
                            if ($equipmentDate == $absoluteLatestVisitDate) {
                                $defaultVisit = $equipment->getVisite();
                                break;
                            }
                        }
                    }

                    // Récupérer les filtres depuis la requête ou utiliser les valeurs par défaut
                    $clientAnneeFilter = $request->query->get('clientAnneeFilter', $defaultYear);
                    $clientVisiteFilter = $request->query->get('clientVisiteFilter', $defaultVisit);

                    // Filtrer les équipements par défaut avec les valeurs de la dernière visite
                    $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                        $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite()));
                        return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                    });

                    // Si aucun équipement n'est trouvé avec les filtres par défaut, montrer tous les équipements
                    if (empty($clientSelectedEquipmentsFiltered)) {
                        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
                    }
                    foreach($clientSelectedEquipmentsFiltered as $equipment){
                        if(!in_array($equipment->getDerniereVisite(), $dateArray)){
                            $dateArray[] = $equipment->getDerniereVisite();
                        }
                    }
                    $directoriesLists = $homeRepository->getListOfPdf($clientSelected, $defaultVisit, $agenceSelected, $dateArray);
                    break;
                
                default:
                    break;
            }
        }
        $agenceSelected = trim($agenceSelected);

        $clientAnneeFilterArray = []; // Je filtre les résultats des filtres d'année
        foreach ($clientSelectedEquipments as $equipment) {
            if ($equipment->getDerniereVisite() !== null) { // Vérifier que la date existe
                $date_equipment = date("Y", strtotime($equipment->getDerniereVisite())); // ✅ CORRECT
                if (!in_array($date_equipment, $clientAnneeFilterArray)) {
                    $clientAnneeFilterArray [] = $date_equipment;
                }
            }
        }
        $clientVisiteFilterArray = []; // Je filtre les résultats des filtres de visite
        foreach ($clientSelectedEquipments as $equipment) {
            $visite_equipment = $equipment->getVisite();
            if (!in_array($visite_equipment, $clientVisiteFilterArray)) {
                $clientVisiteFilterArray [] = $visite_equipment;
            }
        }
        $clientAnneeFilter = "";
        $clientVisiteFilter = "";

        // Récupération des filtres via la requête
        if ($request->query->get('submitFilters')) {
            $clientAnneeFilter = $request->query->get('clientAnneeFilter', '');
            $clientVisiteFilter = $request->query->get('clientVisiteFilter', '');
            $clientSelectedEquipmentsFiltered = $clientSelectedEquipments; // Initialisez avec tous les équipements

            // Validation des filtres
            if (empty($clientAnneeFilter)) {
                $this->addFlash('error', 'Sélectionnez l\'année.');
            }

            if (empty($clientVisiteFilter)) {
                $this->addFlash('error', 'Sélectionnez la visite.');
            }

            // Filtrage des équipements
            if (!empty($clientAnneeFilter) && !empty($clientVisiteFilter)) {
                $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                    $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite())); // ✅ CORRECT
                    return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
                });
            }
        }
        // Définir les variables par défaut si elles ne sont pas encore définies
        if (!isset($defaultYear)) {
            $defaultYear = '';
        }
        if (!isset($defaultVisit)) {
            $defaultVisit = '';
        }
        if (!isset($clientAnneeFilter)) {
            $clientAnneeFilter = $defaultYear;
        }
        if (!isset($clientVisiteFilter)) {
            $clientVisiteFilter = $defaultVisit;
        }

        return $this->render('home/index.html.twig', [
            'clientsGroup' => $clientsGroup,  // Array of Contacts
            'clientsStEtienne' => $clientsStEtienne,  // Array of Contacts
            'clientsGrenoble' => $clientsGrenoble,  // Array of Contacts
            'clientsLyon' => $clientsLyon,  // Array of Contacts
            'clientsBordeaux' => $clientsBordeaux,  // Array of Contacts
            'clientsParisNord' => $clientsParisNord,  // Array of Contacts
            'clientsMontpellier' => $clientsMontpellier,  // Array of Contacts
            'clientsHautsDeFrance' => $clientsHautsDeFrance,  // Array of Contacts
            'clientsToulouse' => $clientsToulouse,  // Array of Contacts
            'clientsEpinal' => $clientsEpinal,  // Array of Contacts
            'clientsPaca' => $clientsPaca,  // Array of Contacts
            'clientsRouen' => $clientsRouen,  // Array of Contacts
            'clientsRennes' => $clientsRennes,  // Array of Contacts
            'clientSelected' => $clientSelected, // String
            'agenceSelected' => $agenceSelected, // String
            'agenciesArray' => $agenciesArray, // Array of all agencies (params : code, agence)
            'clientSelectedInformations'  => $clientSelectedInformations, // Selected Entity Contact
            'clientSelectedEquipmentsFiltered'  => $clientSelectedEquipmentsFiltered, // Selected Entity Equipement where last visit is superior 3 months ago
            'totalClientSelectedEquipmentsFiltered'  => count($clientSelectedEquipmentsFiltered), // Total Selected Entity Equipement where last visit is superior 3 months ago
            'directoriesLists' => $directoriesLists, // Array with Objects $myFile with path and annee properties in it
            'clientSelectedEquipments' =>  $clientSelectedEquipments,
            'visiteDuClient' =>  $visiteDuClient,
            'idClientSelected' =>  $idClientSelected,
            'clientAnneeFilterArray' =>  $clientAnneeFilterArray,
            'clientAnneeFilter' =>  $clientAnneeFilter,
            'clientVisiteFilterArray' =>  $clientVisiteFilterArray,
            'clientVisiteFilter' =>  $clientVisiteFilter,
            'defaultYear' => $defaultYear,
            'defaultVisit' => $defaultVisit,
        ]);
    }
    
    #[Route('/filter-equipments', name: 'app_filter_equipments')]
    public function filterEquipments(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clientAnneeFilter = $request->request->get('clientAnneeFilter', '');
        $clientVisiteFilter = $request->request->get('clientVisiteFilter', '');
        $agenceSelected = $request->request->get('agenceSelected', '');
        $idClientSelected = $request->request->get('idClientSelected', '');
        $errors = [];
        
        // Récupérez les équipements du client (comme vous le faites déjà)
        $clientSelectedEquipments = [];
        switch ($agenceSelected) {
            case 'S10':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S40':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S50':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S60':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S70':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S80':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S100':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S120':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S130':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S140':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S150':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S160':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
            case 'S170':
                $clientSelectedEquipments = $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $idClientSelected], ['numero_equipement' => 'ASC']);
                break;
        }
        $clientSelectedEquipmentsFiltered = $clientSelectedEquipments;
        
        // Validation des filtres
        if (empty($clientAnneeFilter)) {
            $errors[] = 'Sélectionnez l\'année.';
        }

        if (empty($clientVisiteFilter)) {
            $errors[] = 'Sélectionnez la visite.';
        }

        // Filtrage des équipements
        if (!empty($clientAnneeFilter) && !empty($clientVisiteFilter)) {
            $clientSelectedEquipmentsFiltered = array_filter($clientSelectedEquipments, function($equipment) use ($clientAnneeFilter, $clientVisiteFilter) {
                $annee_date_equipment = date("Y", strtotime($equipment->getDerniereVisite())); // ✅ CORRECT
                return ($annee_date_equipment == $clientAnneeFilter && $equipment->getVisite() == $clientVisiteFilter);
            });
        }
        
        if ($errors) {
            return $this->json(['errors' => $errors]);
        }
        
        // Préparer l'HTML pour la réponse
        $html = $this->renderView('components/equipment_list.html.twig', [
            'equipments' => $clientSelectedEquipmentsFiltered
        ]);
        
        return $this->json(['html' => $html]);
    }

    #[Route('/save/modal/equipement', name: 'app_save_modal_equipement')]
    public function saveModalInDatabase(EntityManagerInterface $entityManager, Request $request)//: Response
    {

        // Récupération de l'équipement édité dans la modal
        if(isset($_POST['saveEquipmentFromModal'])){
            $equipmentidEquipementAndIdRow= $_POST['id'];
            $equipmentNom = $_POST['nom'];
            $equipmentPrenom = $_POST['prenom'];
            $equipmentLibelle = $_POST['libelle'];
            $equipmentVisite = $_POST['visite'];
            $equipmentRaisonSociale = $_POST['raisonSociale'];
            $equipmentModeleNacelle = $_POST['modeleNacelle'];
            $equipmentHauteurNacelle = $_POST['hauteurNacelle'];
            $equipmentIfExistDB = $_POST['ifExistDB'];
            $equipmentSignatureTech = $_POST['signatureTech'];
            $equipmentTrigrammeTech = $_POST['trigrammeTech'];
            $equipmentAnomalies = $_POST['anomalies'];
            $equipmentIdContact = $_POST['idContact'];
            $equipmentIdSociete = $_POST['idSociete'];
            $equipmentCodeAgence = $_POST['codeAgence'];
            $equipmentTrigramme = $_POST['trigramme'];
            $equipmentModeFonctionnement = $_POST['modefonctionnement'];
            $equipmentRepereSiteClient = $_POST['reperesiteclient'];
            $equipmentMiseEnService = $_POST['miseenservice'];
            $equipmentNumeroDeSerie = $_POST['numerodeserie'];
            $equipmentMarque = $_POST['marque'];
            $equipmentHauteur = $_POST['hauteur'];
            $equipmentLargeur = $_POST['largeur'];
            $equipmentLongueur = $_POST['longueur'];
            $equipmentPlaqueSignaletique = $_POST['plaquesignaletique'];
            $equipmentEtat = $_POST['etat'];
            $equipmentDerniereVisiteDeMaintenance = $_POST['dernierevisitedemaintenance'];
            $equipmentOldStatut = $_POST['oldstatut'];
            $equipmentNewStatutClient = $_POST['newstatutclient'];
            $equipmentCarnetEntretien = $_POST['carnetentretien'];
            $equipmentStatutConformite = $_POST['statutconformite'];

            // Save IT
            $entityAgency = null;
            switch ($equipmentCodeAgence) {
                case 'S10':
                    $entityAgency = EquipementS10::class;
                    break;
                case 'S40':
                    $entityAgency = EquipementS40::class;
                    break;
                case 'S50':
                    $entityAgency = EquipementS50::class;
                    break;
                case 'S60':
                    $entityAgency = EquipementS60::class;
                    break;
                case 'S70':
                    $entityAgency = EquipementS70::class;
                    break;
                case 'S80':
                    $entityAgency = EquipementS80::class;
                    break;
                case 'S100':
                    $entityAgency = EquipementS100::class;
                    break;
                case 'S120':
                    $entityAgency = EquipementS120::class;
                    break;
                case 'S130':
                    $entityAgency = EquipementS130::class;
                    break;
                case 'S140':
                    $entityAgency = EquipementS140::class;
                    break;
                case 'S150':
                    $entityAgency = EquipementS150::class;
                    break;
                case 'S160':
                    $entityAgency = EquipementS160::class;
                    break;
                case 'S170':
                    $entityAgency = EquipementS170::class;
                    break;
                
                default:
                    # code...
                    break;
            }
            // $equipement = new $entityAgency;
            $equipement = $entityManager->getRepository($entityAgency)->findOneBy(['id' => $equipmentidEquipementAndIdRow]);
            $equipement->setIdContact($equipmentIdContact);
            // Pour avoir l'heure exacte de l'enregistrement
            $date = new DateTime(date("Y-m-d H:i:s"));
            $dateValable = clone $date;
            $dateValable->modify('+2 hour');
            $equipement->setDateEnregistrement($dateValable->format('d-m-Y H:i:s'));
            $equipement->setCodeSociete($equipmentIdSociete);
            $equipement->setCodeAgence($equipmentCodeAgence);
            $equipement->setDerniereVisite($equipmentDerniereVisiteDeMaintenance);
            if (empty($equipmentNom) && empty($equipmentPrenom)) {
                $equipement->setTrigrammeTech($equipmentTrigrammeTech);
            }else{
                $equipement->setTrigrammeTech($equipmentNom . " " . $equipmentPrenom);
            }
            $equipement->setSignatureTech($equipmentSignatureTech);
            $equipement->setVisite($equipmentVisite);
            $equipement->setNumeroEquipement($equipmentTrigramme);
            $equipement->setIfExistDB($equipmentIfExistDB);
            $equipement->setLibelleEquipement(strtolower($equipmentLibelle));
            $equipement->setModeFonctionnement($equipmentModeFonctionnement);
            $equipement->setRepereSiteClient($equipmentRepereSiteClient);
            $equipement->setMiseEnService($equipmentMiseEnService);
            $equipement->setNumeroDeSerie($equipmentNumeroDeSerie);
            $equipement->setMarque($equipmentMarque);
            $equipement->setLargeur($equipmentLargeur);
            $equipement->setHauteur($equipmentHauteur);
            $equipement->setLongueur($equipmentLongueur);
            $equipement->setPlaqueSignaletique($equipmentPlaqueSignaletique);
            $equipement->setAnomalies($equipmentAnomalies);
            $equipement->setEtat($equipmentEtat);
            $equipement->setHauteurNacelle($equipmentHauteurNacelle);
            $equipement->setModeleNacelle($equipmentModeleNacelle);
            if (isset($equipmentNewStatutClient) && $equipmentNewStatutClient != "Choose...") {
                $equipement->setStatutDeMaintenance($equipmentNewStatutClient);
            }else{
                $equipement->setStatutDeMaintenance($equipmentOldStatut);
            }
            $equipement->setRaisonSociale($equipmentRaisonSociale);
            $equipement->setPresenceCarnetEntretien($equipmentCarnetEntretien);
            $equipement->setStatutConformite($equipmentStatutConformite);
            $equipement->setEnMaintenance(true);
            
            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($equipement);
            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
        }
        $this->addFlash('success', 'L\'équipement a été mit à jour avec succès !');
        return $this->redirectToRoute('app_show_equipement_details_by_id', [
            'agence' => $equipmentCodeAgence,
            'id' => $equipmentidEquipementAndIdRow
        ]);
        // return new Response("<html><body><p  style='font-size:18px; font-weight:bold;'>L'équipement édité dans la modal a bien été enregistré en base de données</p></body></html>", Response::HTTP_OK, [], true);
        // return $this->redirect($request->getUri());
    }

    #[Route('/show/equipement/details/{agence}/{id}', name: 'app_show_equipement_details_by_id')]
    public function showEquipmentDetailsById(string $agence, string $id, EntityManagerInterface $entityManager){
        switch ($agence) {
            case 'S10':
                $equipment = $entityManager->getRepository(EquipementS10::class)->findOneBy(['id' => $id]); // L'ID remonté est bon 
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S40':
                $equipment = $entityManager->getRepository(EquipementS40::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S50':
                $equipment = $entityManager->getRepository(EquipementS50::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S60':
                $equipment = $entityManager->getRepository(EquipementS60::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S70':
                $equipment = $entityManager->getRepository(EquipementS70::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S80':
                $equipment = $entityManager->getRepository(EquipementS80::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S100':
                $equipment = $entityManager->getRepository(EquipementS100::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S120':
                $equipment = $entityManager->getRepository(EquipementS120::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
            
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                
                break;
            case 'S130':
                $equipment = $entityManager->getRepository(EquipementS130::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S140':
                $equipment = $entityManager->getRepository(EquipementS140::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S150':
                $equipment = $entityManager->getRepository(EquipementS150::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S160':
                $equipment = $entityManager->getRepository(EquipementS160::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            case 'S170':
                $equipment = $entityManager->getRepository(EquipementS170::class)->findOneBy(['id' => $id]); // L'ID remonté est bon
                $picturesArray = $entityManager->getRepository(Form::class)->findBy(array('code_equipement' => $equipment->getNumeroEquipement(), 'raison_sociale_visite' => $equipment->getRaisonSociale() . "\\" . $equipment->getVisite()));
                $picturesData = $entityManager->getRepository(Form::class)->getPictureArrayByIdEquipment($picturesArray, $entityManager, $equipment);
                break;
            
            default:
                break;
        }
        return $this->render('home/show-equipment-details.html.twig', [
            "equipment" => $equipment,
            "picturesData" => $picturesData,
        ]);
    }
}
