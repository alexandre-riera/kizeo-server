<?php

namespace App\Entity;

use App\Repository\EquipementS50Repository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementS50Repository::class)]
class EquipementS50
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero_equipement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mode_fonctionnement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $repere_site_client = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mise_en_service = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero_de_serie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hauteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $largeur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plaque_signaletique = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $anomalies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dernière_visite = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $trigramme_tech = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code_societe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signature_tech = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ifExistDB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code_agence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hauteur_nacelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modele_nacelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raison_sociale = null;

    #[ORM\Column(length: 15)]
    private ?string $test = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_plaque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_choc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_choc_montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_panneau_intermediaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_panneau_bas_inter_ext = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_lame_basse_int_ext = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_lame_intermediaire_int = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_environnement_equipement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_bache = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_marquage_au_sol = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_environnement_eclairage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_coffret_de_commande = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_carte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_rail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_equerre_rail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_fixation_coulisse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_moteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_deformation_plateau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_deformation_plaque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_deformation_structure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_deformation_chassis = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_deformation_levre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_fissure_cordon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_joue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_butoir = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_vantail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_linteau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_bariere = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_tourniquet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_sas = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_marquage_au_sol_portail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut_de_maintenance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_enregistrement = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroEquipement(): ?string
    {
        return $this->numero_equipement;
    }

    public function setNumeroEquipement(string $numero_equipement): static
    {
        $this->numero_equipement = $numero_equipement;

        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(string $nature): static
    {
        $this->nature = $nature;

        return $this;
    }

    public function getModeFonctionnement(): ?string
    {
        return $this->mode_fonctionnement;
    }

    public function setModeFonctionnement(string $mode_fonctionnement): static
    {
        $this->mode_fonctionnement = $mode_fonctionnement;

        return $this;
    }

    public function getRepereSiteClient(): ?string
    {
        return $this->repere_site_client;
    }

    public function setRepereSiteClient(string $repere_site_client): static
    {
        $this->repere_site_client = $repere_site_client;

        return $this;
    }

    public function getMiseEnService(): ?string
    {
        return $this->mise_en_service;
    }

    public function setMiseEnService(string $mise_en_service): static
    {
        $this->mise_en_service = $mise_en_service;

        return $this;
    }

    public function getNumeroDeSerie(): ?string
    {
        return $this->numero_de_serie;
    }

    public function setNumeroDeSerie(string $numero_de_serie): static
    {
        $this->numero_de_serie = $numero_de_serie;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getHauteur(): ?string
    {
        return $this->hauteur;
    }

    public function setHauteur(string $hauteur): static
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getLargeur(): ?string
    {
        return $this->largeur;
    }

    public function setLargeur(string $largeur): static
    {
        $this->largeur = $largeur;

        return $this;
    }

    public function getPlaqueSignaletique(): ?string
    {
        return $this->plaque_signaletique;
    }

    public function setPlaqueSignaletique(string $plaque_signaletique): static
    {
        $this->plaque_signaletique = $plaque_signaletique;

        return $this;
    }

    public function getAnomalies(): ?string
    {
        return $this->anomalies;
    }

    public function setAnomalies(?string $anomalies): static
    {
        $this->anomalies = $anomalies;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDernièreVisite(): ?string
    {
        return $this->dernière_visite;
    }

    public function setDernièreVisite(?string $dernière_visite): static
    {
        $this->dernière_visite = $dernière_visite;

        return $this;
    }

    public function getTrigrammeTech(): ?string
    {
        return $this->trigramme_tech;
    }

    public function setTrigrammeTech(string $trigramme_tech): static
    {
        $this->trigramme_tech = $trigramme_tech;

        return $this;
    }

    public function getIdContact(): ?string
    {
        return $this->id_contact;
    }

    public function setIdContact(?string $id_contact): static
    {
        $this->id_contact = $id_contact;

        return $this;
    }

    public function getCodeSociete(): ?string
    {
        return $this->code_societe;
    }

    public function setCodeSociete(?string $code_societe): static
    {
        $this->code_societe = $code_societe;

        return $this;
    }

    public function getSignatureTech(): ?string
    {
        return $this->signature_tech;
    }

    public function setSignatureTech(?string $signature_tech): static
    {
        $this->signature_tech = $signature_tech;

        return $this;
    }

    public function getIfExistDB(): ?string
    {
        return $this->ifExistDB;
    }

    public function setIfExistDB(?string $ifExistDB): static
    {
        $this->ifExistDB = $ifExistDB;

        return $this;
    }

    public function getCodeAgence(): ?string
    {
        return $this->code_agence;
    }

    public function setCodeAgence(?string $code_agence): static
    {
        $this->code_agence = $code_agence;

        return $this;
    }

    public function getHauteurNacelle(): ?string
    {
        return $this->hauteur_nacelle;
    }

    public function setHauteurNacelle(?string $hauteur_nacelle): static
    {
        $this->hauteur_nacelle = $hauteur_nacelle;

        return $this;
    }

    public function getModeleNacelle(): ?string
    {
        return $this->modele_nacelle;
    }

    public function setModeleNacelle(?string $modele_nacelle): static
    {
        $this->modele_nacelle = $modele_nacelle;

        return $this;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raison_sociale;
    }

    public function setRaisonSociale(?string $raison_sociale): static
    {
        $this->raison_sociale = $raison_sociale;

        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(string $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getPhotoPlaque(): ?string
    {
        return $this->photo_plaque;
    }

    public function setPhotoPlaque(?string $photo_plaque): static
    {
        $this->photo_plaque = $photo_plaque;

        return $this;
    }

    public function getPhotoChoc(): ?string
    {
        return $this->photo_choc;
    }

    public function setPhotoChoc(?string $photo_choc): static
    {
        $this->photo_choc = $photo_choc;

        return $this;
    }

    public function getPhotoChocMontant(): ?string
    {
        return $this->photo_choc_montant;
    }

    public function setPhotoChocMontant(?string $photo_choc_montant): static
    {
        $this->photo_choc_montant = $photo_choc_montant;

        return $this;
    }

    public function getPhotoPanneauIntermediaire(): ?string
    {
        return $this->photo_panneau_intermediaire;
    }

    public function setPhotoPanneauIntermediaire(?string $photo_panneau_intermediaire): static
    {
        $this->photo_panneau_intermediaire = $photo_panneau_intermediaire;

        return $this;
    }

    public function getPhotoPanneauBasInterExt(): ?string
    {
        return $this->photo_panneau_bas_inter_ext;
    }

    public function setPhotoPanneauBasInterExt(?string $photo_panneau_bas_inter_ext): static
    {
        $this->photo_panneau_bas_inter_ext = $photo_panneau_bas_inter_ext;

        return $this;
    }

    public function getPhotoLameBasseIntExt(): ?string
    {
        return $this->photo_lame_basse_int_ext;
    }

    public function setPhotoLameBasseIntExt(?string $photo_lame_basse_int_ext): static
    {
        $this->photo_lame_basse_int_ext = $photo_lame_basse_int_ext;

        return $this;
    }

    public function getPhotoLameIntermediaireInt(): ?string
    {
        return $this->photo_lame_intermediaire_int;
    }

    public function setPhotoLameIntermediaireInt(?string $photo_lame_intermediaire_int): static
    {
        $this->photo_lame_intermediaire_int = $photo_lame_intermediaire_int;

        return $this;
    }

    public function getPhotoEnvironnementEquipement(): ?string
    {
        return $this->photo_environnement_equipement;
    }

    public function setPhotoEnvironnementEquipement(?string $photo_environnement_equipement): static
    {
        $this->photo_environnement_equipement = $photo_environnement_equipement;

        return $this;
    }

    public function getPhotoBache(): ?string
    {
        return $this->photo_bache;
    }

    public function setPhotoBache(?string $photo_bache): static
    {
        $this->photo_bache = $photo_bache;

        return $this;
    }

    public function getPhotoMarquageAuSol(): ?string
    {
        return $this->photo_marquage_au_sol;
    }

    public function setPhotoMarquageAuSol(?string $photo_marquage_au_sol): static
    {
        $this->photo_marquage_au_sol = $photo_marquage_au_sol;

        return $this;
    }

    public function getPhotoEnvironnementEclairage(): ?string
    {
        return $this->photo_environnement_eclairage;
    }

    public function setPhotoEnvironnementEclairage(?string $photo_environnement_eclairage): static
    {
        $this->photo_environnement_eclairage = $photo_environnement_eclairage;

        return $this;
    }

    public function getPhotoCoffretDeCommande(): ?string
    {
        return $this->photo_coffret_de_commande;
    }

    public function setPhotoCoffretDeCommande(?string $photo_coffret_de_commande): static
    {
        $this->photo_coffret_de_commande = $photo_coffret_de_commande;

        return $this;
    }

    public function getPhotoCarte(): ?string
    {
        return $this->photo_carte;
    }

    public function setPhotoCarte(?string $photo_carte): static
    {
        $this->photo_carte = $photo_carte;

        return $this;
    }

    public function getPhotoRail(): ?string
    {
        return $this->photo_rail;
    }

    public function setPhotoRail(?string $photo_rail): static
    {
        $this->photo_rail = $photo_rail;

        return $this;
    }

    public function getPhotoEquerreRail(): ?string
    {
        return $this->photo_equerre_rail;
    }

    public function setPhotoEquerreRail(?string $photo_equerre_rail): static
    {
        $this->photo_equerre_rail = $photo_equerre_rail;

        return $this;
    }

    public function getPhotoFixationCoulisse(): ?string
    {
        return $this->photo_fixation_coulisse;
    }

    public function setPhotoFixationCoulisse(?string $photo_fixation_coulisse): static
    {
        $this->photo_fixation_coulisse = $photo_fixation_coulisse;

        return $this;
    }

    public function getPhotoMoteur(): ?string
    {
        return $this->photo_moteur;
    }

    public function setPhotoMoteur(?string $photo_moteur): static
    {
        $this->photo_moteur = $photo_moteur;

        return $this;
    }

    public function getPhotoDeformationPlateau(): ?string
    {
        return $this->photo_deformation_plateau;
    }

    public function setPhotoDeformationPlateau(?string $photo_deformation_plateau): static
    {
        $this->photo_deformation_plateau = $photo_deformation_plateau;

        return $this;
    }

    public function getPhotoDeformationPlaque(): ?string
    {
        return $this->photo_deformation_plaque;
    }

    public function setPhotoDeformationPlaque(?string $photo_deformation_plaque): static
    {
        $this->photo_deformation_plaque = $photo_deformation_plaque;

        return $this;
    }

    public function getPhotoDeformationStructure(): ?string
    {
        return $this->photo_deformation_structure;
    }

    public function setPhotoDeformationStructure(?string $photo_deformation_structure): static
    {
        $this->photo_deformation_structure = $photo_deformation_structure;

        return $this;
    }

    public function getPhotoDeformationChassis(): ?string
    {
        return $this->photo_deformation_chassis;
    }

    public function setPhotoDeformationChassis(?string $photo_deformation_chassis): static
    {
        $this->photo_deformation_chassis = $photo_deformation_chassis;

        return $this;
    }

    public function getPhotoDeformationLevre(): ?string
    {
        return $this->photo_deformation_levre;
    }

    public function setPhotoDeformationLevre(?string $photo_deformation_levre): static
    {
        $this->photo_deformation_levre = $photo_deformation_levre;

        return $this;
    }

    public function getPhotoFissureCordon(): ?string
    {
        return $this->photo_fissure_cordon;
    }

    public function setPhotoFissureCordon(?string $photo_fissure_cordon): static
    {
        $this->photo_fissure_cordon = $photo_fissure_cordon;

        return $this;
    }

    public function getPhotoJoue(): ?string
    {
        return $this->photo_joue;
    }

    public function setPhotoJoue(?string $photo_joue): static
    {
        $this->photo_joue = $photo_joue;

        return $this;
    }

    public function getPhotoButoir(): ?string
    {
        return $this->photo_butoir;
    }

    public function setPhotoButoir(?string $photo_butoir): static
    {
        $this->photo_butoir = $photo_butoir;

        return $this;
    }

    public function getPhotoVantail(): ?string
    {
        return $this->photo_vantail;
    }

    public function setPhotoVantail(?string $photo_vantail): static
    {
        $this->photo_vantail = $photo_vantail;

        return $this;
    }

    public function getPhotoLinteau(): ?string
    {
        return $this->photo_linteau;
    }

    public function setPhotoLinteau(?string $photo_linteau): static
    {
        $this->photo_linteau = $photo_linteau;

        return $this;
    }

    public function getPhotoBariere(): ?string
    {
        return $this->photo_bariere;
    }

    public function setPhotoBariere(?string $photo_bariere): static
    {
        $this->photo_bariere = $photo_bariere;

        return $this;
    }

    public function getPhotoTourniquet(): ?string
    {
        return $this->photo_tourniquet;
    }

    public function setPhotoTourniquet(?string $photo_tourniquet): static
    {
        $this->photo_tourniquet = $photo_tourniquet;

        return $this;
    }

    public function getPhotoSas(): ?string
    {
        return $this->photo_sas;
    }

    public function setPhotoSas(?string $photo_sas): static
    {
        $this->photo_sas = $photo_sas;

        return $this;
    }

    public function getPhotoMarquageAuSolPortail(): ?string
    {
        return $this->photo_marquage_au_sol_portail;
    }

    public function setPhotoMarquageAuSolPortail(?string $photo_marquage_au_sol_portail): static
    {
        $this->photo_marquage_au_sol_portail = $photo_marquage_au_sol_portail;

        return $this;
    }

    public function getStatutDeMaintenance(): ?string
    {
        return $this->statut_de_maintenance;
    }

    public function setStatutDeMaintenance(?string $statut_de_maintenance): static
    {
        $this->statut_de_maintenance = $statut_de_maintenance;

        return $this;
    }

    public function getDateEnregistrement(): ?string
    {
        return $this->date_enregistrement;
    }

    public function setDateEnregistrement(?string $date_enregistrement): static
    {
        $this->date_enregistrement = $date_enregistrement;

        return $this;
    }
}
