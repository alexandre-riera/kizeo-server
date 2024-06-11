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

    #[ORM\Column]
    private ?int $numero_equipement = null;

    #[ORM\Column(length: 255)]
    private ?string $nature = null;

    #[ORM\Column(length: 255)]
    private ?string $mode_fonctionnement = null;

    #[ORM\Column(length: 255)]
    private ?string $repere_site_client = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $mise_en_service = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_de_serie = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $hauteur = null;

    #[ORM\Column(length: 255)]
    private ?string $largeur = null;

    #[ORM\Column(length: 255)]
    private ?string $plaque_signaletique = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $anomalies = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dernière_visite = null;

    #[ORM\Column(length: 3)]
    private ?string $trigramme_tech = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_previsionnelle_visite_1 = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_previsionnelle_visite_2 = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_effective_1 = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_effective_2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroEquipement(): ?int
    {
        return $this->numero_equipement;
    }

    public function setNumeroEquipement(int $numero_equipement): static
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

    public function getMiseEnService(): ?\DateTimeInterface
    {
        return $this->mise_en_service;
    }

    public function setMiseEnService(\DateTimeInterface $mise_en_service): static
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

    public function getDernièreVisite(): ?\DateTimeInterface
    {
        return $this->dernière_visite;
    }

    public function setDernièreVisite(?\DateTimeInterface $dernière_visite): static
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

    public function getDatePrevisionnelleVisite2(): ?\DateTimeInterface
    {
        return $this->date_previsionnelle_visite_2;
    }

    public function setDatePrevisionnelleVisite2(?\DateTimeInterface $date_previsionnelle_visite_2): static
    {
        $this->date_previsionnelle_visite_2 = $date_previsionnelle_visite_2;

        return $this;
    }

    public function getDateEffective1(): ?\DateTimeInterface
    {
        return $this->date_effective_1;
    }

    public function setDateEffective1(?\DateTimeInterface $date_effective_1): static
    {
        $this->date_effective_1 = $date_effective_1;

        return $this;
    }

    public function getDateEffective2(): ?\DateTimeInterface
    {
        return $this->date_effective_2;
    }

    public function setDateEffective2(?\DateTimeInterface $date_effective_2): static
    {
        $this->date_effective_2 = $date_effective_2;

        return $this;
    }
}
