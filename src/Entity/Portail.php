<?php

namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use App\Repository\PortailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortailRepository::class)]
class Portail
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
    private ?string $longueur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plaque_signaletique = null;

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
    private ?string $modele = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombres_vantaux = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom_client = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $presence_carnet_entretien = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $presence_notice_fabricant = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $portillon_sur_vantail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type_de_guidage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type_portail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $espace_inf_8mm_rail_protection_galets = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $distance_bas_portail_rail_inferieur_sol = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $espace_haut_portail_platine_galets_guidage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $espace_vantail_galets_guidage_inf_8 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $butee_meca_avant_sur_vantail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $butee_meca_arriere_sur_vantail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $efficacite_butees_en_manuel = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $systeme_anti_chutes = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $seuil_surel_sup_a_5 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $marquage_parties_surelevees_non_visibles = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $portail_immobile_toutes_positions_en_manuel = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $dur_meca_en_manuel = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $distance_barreaux_cloture = null;


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

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getNombresVantaux(): ?string
    {
        return $this->nombres_vantaux;
    }

    public function setNombresVantaux(?string $nombres_vantaux): static
    {
        $this->nombres_vantaux = $nombres_vantaux;

        return $this;
    }

    public function getLongueur(): ?string
    {
        return $this->longueur;
    }

    public function setLongueur(?string $longueur): static
    {
        $this->longueur = $longueur;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nom_client;
    }

    public function setNomClient(?string $nom_client): static
    {
        $this->nom_client = $nom_client;

        return $this;
    }

    public function getPresenceCarnetEntretien(): ?string
    {
        return $this->presence_carnet_entretien;
    }

    public function setPresenceCarnetEntretien(?string $presence_carnet_entretien): static
    {
        $this->presence_carnet_entretien = $presence_carnet_entretien;

        return $this;
    }

    public function getPresenceNoticeFabricant(): ?string
    {
        return $this->presence_notice_fabricant;
    }

    public function setPresenceNoticeFabricant(?string $presence_notice_fabricant): static
    {
        $this->presence_notice_fabricant = $presence_notice_fabricant;

        return $this;
    }

    public function getPortillonSurVantail(): ?string
    {
        return $this->portillon_sur_vantail;
    }

    public function setPortillonSurVantail(?string $portillon_sur_vantail): static
    {
        $this->portillon_sur_vantail = $portillon_sur_vantail;

        return $this;
    }

    public function getTypeDeGuidage(): ?string
    {
        return $this->type_de_guidage;
    }

    public function setTypeDeGuidage(?string $type_de_guidage): static
    {
        $this->type_de_guidage = $type_de_guidage;

        return $this;
    }

    public function getTypePortail(): ?string
    {
        return $this->type_portail;
    }

    public function setTypePortail(?string $type_portail): static
    {
        $this->type_portail = $type_portail;

        return $this;
    }

    public function getEspaceInf8mmRailProtectionGalets(): ?string
    {
        return $this->espace_inf_8mm_rail_protection_galets;
    }

    public function setEspaceInf8mmRailProtectionGalets(?string $espace_inf_8mm_rail_protection_galets): static
    {
        $this->espace_inf_8mm_rail_protection_galets = $espace_inf_8mm_rail_protection_galets;

        return $this;
    }

    public function getDistanceBasPortailRailInferieurSol(): ?string
    {
        return $this->distance_bas_portail_rail_inferieur_sol;
    }

    public function setDistanceBasPortailRailInferieurSol(?string $distance_bas_portail_rail_inferieur_sol): static
    {
        $this->distance_bas_portail_rail_inferieur_sol = $distance_bas_portail_rail_inferieur_sol;

        return $this;
    }

    public function getEspaceHautPortailPlatineGaletsGuidage(): ?string
    {
        return $this->espace_haut_portail_platine_galets_guidage;
    }

    public function setEspaceHautPortailPlatineGaletsGuidage(?string $espace_haut_portail_platine_galets_guidage): static
    {
        $this->espace_haut_portail_platine_galets_guidage = $espace_haut_portail_platine_galets_guidage;

        return $this;
    }

    public function getEspaceVantailGaletsGuidageInf8(): ?string
    {
        return $this->espace_vantail_galets_guidage_inf_8;
    }

    public function setEspaceVantailGaletsGuidageInf8(?string $espace_vantail_galets_guidage_inf_8): static
    {
        $this->espace_vantail_galets_guidage_inf_8 = $espace_vantail_galets_guidage_inf_8;

        return $this;
    }

    public function getButeeMecaAvantSurVantail(): ?string
    {
        return $this->butee_meca_avant_sur_vantail;
    }

    public function setButeeMecaAvantSurVantail(?string $butee_meca_avant_sur_vantail): static
    {
        $this->butee_meca_avant_sur_vantail = $butee_meca_avant_sur_vantail;

        return $this;
    }

    public function getButeeMecaArriereSurVantail(): ?string
    {
        return $this->butee_meca_arriere_sur_vantail;
    }

    public function setButeeMecaArriereSurVantail(?string $butee_meca_arriere_sur_vantail): static
    {
        $this->butee_meca_arriere_sur_vantail = $butee_meca_arriere_sur_vantail;

        return $this;
    }

    public function getEfficaciteButeesEnManuel(): ?string
    {
        return $this->efficacite_butees_en_manuel;
    }

    public function setEfficaciteButeesEnManuel(?string $efficacite_butees_en_manuel): static
    {
        $this->efficacite_butees_en_manuel = $efficacite_butees_en_manuel;

        return $this;
    }

    public function getSystemeAntiChutes(): ?string
    {
        return $this->systeme_anti_chutes;
    }

    public function setSystemeAntiChutes(?string $systeme_anti_chutes): static
    {
        $this->systeme_anti_chutes = $systeme_anti_chutes;

        return $this;
    }

    public function getSeuilSurelSupA5(): ?string
    {
        return $this->seuil_surel_sup_a_5;
    }

    public function setSeuilSurelSupA5(?string $seuil_surel_sup_a_5): static
    {
        $this->seuil_surel_sup_a_5 = $seuil_surel_sup_a_5;

        return $this;
    }

    public function getMarquagePartiesSureleveesNonVisibles(): ?string
    {
        return $this->marquage_parties_surelevees_non_visibles;
    }

    public function setMarquagePartiesSureleveesNonVisibles(?string $marquage_parties_surelevees_non_visibles): static
    {
        $this->marquage_parties_surelevees_non_visibles = $marquage_parties_surelevees_non_visibles;

        return $this;
    }

    public function getPortailImmobileToutesPositionsEnManuel(): ?string
    {
        return $this->portail_immobile_toutes_positions_en_manuel;
    }

    public function setPortailImmobileToutesPositionsEnManuel(?string $portail_immobile_toutes_positions_en_manuel): static
    {
        $this->portail_immobile_toutes_positions_en_manuel = $portail_immobile_toutes_positions_en_manuel;

        return $this;
    }

    public function getDurMecaEnManuel(): ?string
    {
        return $this->dur_meca_en_manuel;
    }

    public function setDurMecaEnManuel(?string $dur_meca_en_manuel): static
    {
        $this->dur_meca_en_manuel = $dur_meca_en_manuel;

        return $this;
    }

    public function getDistanceBarreauxCloture(): ?string
    {
        return $this->distance_barreaux_cloture;
    }

    public function setDistanceBarreauxCloture(?string $distance_barreaux_cloture): static
    {
        $this->distance_barreaux_cloture = $distance_barreaux_cloture;

        return $this;
    }
}
