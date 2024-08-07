<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
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
    private ?string $date_previsionnelle_visite_1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_previsionnelle_visite_2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_effective_1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_effective_2 = null;

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

    public function getDatePrevisionnelleVisite1(): ?string
    {
        return $this->date_previsionnelle_visite_1;
    }

    public function setDatePrevisionnelleVisite1(?string $date_previsionnelle_visite_1): static
    {
        $this->date_previsionnelle_visite_1 = $date_previsionnelle_visite_1;

        return $this;
    }

    public function getDatePrevisionnelleVisite2(): ?string
    {
        return $this->date_previsionnelle_visite_2;
    }

    public function setDatePrevisionnelleVisite2(?string $date_previsionnelle_visite_2): static
    {
        $this->date_previsionnelle_visite_2 = $date_previsionnelle_visite_2;

        return $this;
    }

    public function getDateEffective1(): ?string
    {
        return $this->date_effective_1;
    }

    public function setDateEffective1(?string $date_effective_1): static
    {
        $this->date_effective_1 = $date_effective_1;

        return $this;
    }

    public function getDateEffective2(): ?string
    {
        return $this->date_effective_2;
    }

    public function setDateEffective2(?string $date_effective_2): static
    {
        $this->date_effective_2 = $date_effective_2;

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
}
