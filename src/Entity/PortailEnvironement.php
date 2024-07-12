<?php

namespace App\Entity;

use App\Repository\PortailEnvironementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortailEnvironementRepository::class)]
class PortailEnvironement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_societe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero_equipement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $distance_cloture_ext_et_vantail_d1_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensions_mailles_grillage_ext_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $distance_grillage_et_vantail_int_d2_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensions_mailles_grillage_int_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensions_mailles_tablier_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $distance_barreaux_vantail_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valeurs_mesurees_point_1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valeurs_mesurees_point_2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valeurs_mesurees_point_3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valeurs_mesurees_point_4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valeurs_mesurees_point_5 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire_supp_si_necessaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_sup_si_necessaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $if_exist_db = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdSociete(): ?string
    {
        return $this->id_societe;
    }

    public function setIdSociete(?string $id_societe): static
    {
        $this->id_societe = $id_societe;

        return $this;
    }

    public function getNumeroEquipement(): ?string
    {
        return $this->numero_equipement;
    }

    public function setNumeroEquipement(?string $numero_equipement): static
    {
        $this->numero_equipement = $numero_equipement;

        return $this;
    }

    public function getDistanceClotureExtEtVantailD1Mm(): ?string
    {
        return $this->distance_cloture_ext_et_vantail_d1_mm;
    }

    public function setDistanceClotureExtEtVantailD1Mm(?string $distance_cloture_ext_et_vantail_d1_mm): static
    {
        $this->distance_cloture_ext_et_vantail_d1_mm = $distance_cloture_ext_et_vantail_d1_mm;

        return $this;
    }

    public function getDimensionsMaillesGrillageExtMm(): ?string
    {
        return $this->dimensions_mailles_grillage_ext_mm;
    }

    public function setDimensionsMaillesGrillageExtMm(?string $dimensions_mailles_grillage_ext_mm): static
    {
        $this->dimensions_mailles_grillage_ext_mm = $dimensions_mailles_grillage_ext_mm;

        return $this;
    }

    public function getDistanceGrillageEtVantailIntD2Mm(): ?string
    {
        return $this->distance_grillage_et_vantail_int_d2_mm;
    }

    public function setDistanceGrillageEtVantailIntD2Mm(?string $distance_grillage_et_vantail_int_d2_mm): static
    {
        $this->distance_grillage_et_vantail_int_d2_mm = $distance_grillage_et_vantail_int_d2_mm;

        return $this;
    }

    public function getDimensionsMaillesGrillageIntMm(): ?string
    {
        return $this->dimensions_mailles_grillage_int_mm;
    }

    public function setDimensionsMaillesGrillageIntMm(?string $dimensions_mailles_grillage_int_mm): static
    {
        $this->dimensions_mailles_grillage_int_mm = $dimensions_mailles_grillage_int_mm;

        return $this;
    }

    public function getDimensionsMaillesTablierMm(): ?string
    {
        return $this->dimensions_mailles_tablier_mm;
    }

    public function setDimensionsMaillesTablierMm(?string $dimensions_mailles_tablier_mm): static
    {
        $this->dimensions_mailles_tablier_mm = $dimensions_mailles_tablier_mm;

        return $this;
    }

    public function getDistanceBarreauxVantailMm(): ?string
    {
        return $this->distance_barreaux_vantail_mm;
    }

    public function setDistanceBarreauxVantailMm(?string $distance_barreaux_vantail_mm): static
    {
        $this->distance_barreaux_vantail_mm = $distance_barreaux_vantail_mm;

        return $this;
    }

    public function getValeursMesureesPoint1(): ?string
    {
        return $this->valeurs_mesurees_point_1;
    }

    public function setValeursMesureesPoint1(?string $valeurs_mesurees_point_1): static
    {
        $this->valeurs_mesurees_point_1 = $valeurs_mesurees_point_1;

        return $this;
    }

    public function getValeursMesureesPoint2(): ?string
    {
        return $this->valeurs_mesurees_point_2;
    }

    public function setValeursMesureesPoint2(?string $valeurs_mesurees_point_2): static
    {
        $this->valeurs_mesurees_point_2 = $valeurs_mesurees_point_2;

        return $this;
    }

    public function getValeursMesureesPoint3(): ?string
    {
        return $this->valeurs_mesurees_point_3;
    }

    public function setValeursMesureesPoint3(?string $valeurs_mesurees_point_3): static
    {
        $this->valeurs_mesurees_point_3 = $valeurs_mesurees_point_3;

        return $this;
    }

    public function getValeursMesureesPoint4(): ?string
    {
        return $this->valeurs_mesurees_point_4;
    }

    public function setValeursMesureesPoint4(?string $valeurs_mesurees_point_4): static
    {
        $this->valeurs_mesurees_point_4 = $valeurs_mesurees_point_4;

        return $this;
    }

    public function getValeursMesureesPoint5(): ?string
    {
        return $this->valeurs_mesurees_point_5;
    }

    public function setValeursMesureesPoint5(?string $valeurs_mesurees_point_5): static
    {
        $this->valeurs_mesurees_point_5 = $valeurs_mesurees_point_5;

        return $this;
    }

    public function getCommentaireSuppSiNecessaire(): ?string
    {
        return $this->commentaire_supp_si_necessaire;
    }

    public function setCommentaireSuppSiNecessaire(string $commentaire_supp_si_necessaire): static
    {
        $this->commentaire_supp_si_necessaire = $commentaire_supp_si_necessaire;

        return $this;
    }

    public function getPhotoSupSiNecessaire(): ?string
    {
        return $this->photo_sup_si_necessaire;
    }

    public function setPhotoSupSiNecessaire(?string $photo_sup_si_necessaire): static
    {
        $this->photo_sup_si_necessaire = $photo_sup_si_necessaire;

        return $this;
    }

    public function getIfExistDb(): ?string
    {
        return $this->if_exist_db;
    }

    public function setIfExistDb(?string $if_exist_db): static
    {
        $this->if_exist_db = $if_exist_db;

        return $this;
    }
}
