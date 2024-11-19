<?php

namespace App\Controller;

use DateTime;
use DOMDocument;
use DateInterval;
use App\Entity\Agency;
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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_front')]
    public function index(CacheInterface $cache,EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request): Response
    {
        // GET CONTACTS BY AGENCY
        $clientsGroup  =  $entityManager->getRepository(ContactS10::class)->findAll();
        $clientsStEtienne  =  $entityManager->getRepository(ContactS40::class)->findAll();
        $clientsGrenoble  =  $entityManager->getRepository(ContactS50::class)->findAll();
        $clientsLyon  =  $entityManager->getRepository(ContactS60::class)->findAll();
        $clientsBordeaux  =  $entityManager->getRepository(ContactS70::class)->findAll();
        $clientsParisNord  =  $entityManager->getRepository(ContactS80::class)->findAll();
        $clientsMontpellier  =  $entityManager->getRepository(ContactS100::class)->findAll();
        $clientsHautsDeFrance  =  $entityManager->getRepository(ContactS120::class)->findAll();
        $clientsToulouse  =  $entityManager->getRepository(ContactS130::class)->findAll();
        $clientsEpinal  =  $entityManager->getRepository(ContactS140::class)->findAll();
        $clientsPaca  =  $entityManager->getRepository(ContactS150::class)->findAll();
        $clientsRouen  =  $entityManager->getRepository(ContactS160::class)->findAll();
        $clientsRennes  =  $entityManager->getRepository(ContactS170::class)->findAll();
        $agenciesArray = $entityManager->getRepository(Agency::class)->findAll();

        // GET CLIENT SELECTED INFORMATION BY AGENCY BY HIS RAISON_SOCIALE
        $clientSelectedInformations  = "";
        // GET CLIENT SELECTED EQUIPMENTS BY AGENCY BY HIS ID_CONTACT
        $clientSelectedEquipments  = [];
        $clientSelectedEquipmentsFiltered = [];
        // GET VALUE OF AGENCY SELECTED
        $agenceSelected = "";
         
        // // GET VALUE OF CLIENT SELECTED
        $clientSelected = "";

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
            } else {  
                echo 'Please select the value.';
            }  
        }
        dump($clientSelected);
        dump($agenceSelected);
        // ENLEVER LE NOM DE L'AGENCE ET L'ESPACE A LA FIN DU NOM DU CLIENT SÉLECTIONNÉ
        $clientSelected = rtrim($clientSelected, "\S10\S40\S50\S60\S70\S80\S100\S120\S130\S140\S150\S160\S170\ ");
        dump($clientSelected);
        
        if ($clientSelected != NULL) {
            switch ($agenceSelected) {
                case 'S10':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS10::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S10':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS10::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS10::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                case 'S40':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS40::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S40':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS40::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS40::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S50':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS50::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() != NULL) {
                            array_push($clientSelectedEquipmentsFiltered, $equipment);
                        }
                    }
                    break;
                case ' S50':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS50::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS50::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    
                    foreach ($clientSelectedEquipments as $equipment) {
                        if ($equipment->getDerniereVisite() != NULL) {
                            array_push($clientSelectedEquipmentsFiltered, $equipment);
                        }
                    }
                    break;
                case 'S60':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS60::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S60':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS60::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS60::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S70':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS70::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S70':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS70::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS70::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S80':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS80::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S80':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS80::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS80::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S100':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS100::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S100':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS100::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS100::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S120':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS120::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S120':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS120::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS120::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S130':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS130::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S130':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS130::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS130::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S140':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS140::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S140':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS140::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS140::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S150':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS150::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S150':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS150::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS150::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S160':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS160::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S160':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS160::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS160::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case 'S170':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS170::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                case ' S170':
                    $clientSelectedInformations  =  $entityManager->getRepository(ContactS170::class)->findOneBy(['raison_sociale' => $clientSelected]);
                    $clientSelectedEquipments  = $entityManager->getRepository(EquipementS170::class)->findBy(['id_contact' => $clientSelectedInformations->getIdContact()]);
                    break;
                
                default:
                    break;
            }
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
        ]);
    }
}
