<?php

namespace App\Entity;

use App\Repository\ContactS120Repository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactS120Repository::class)]
class ContactS120
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adressep_1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adressep_2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cpostalp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $villep = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rib = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_site = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raison_sociale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_societe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdressep1(): ?string
    {
        return $this->adressep_1;
    }

    public function setAdressep1(string $adressep_1): static
    {
        $this->adressep_1 = $adressep_1;

        return $this;
    }

    public function getAdressep2(): ?string
    {
        return $this->adressep_2;
    }

    public function setAdressep2(?string $adressep_2): static
    {
        $this->adressep_2 = $adressep_2;

        return $this;
    }

    public function getCpostalp(): ?string
    {
        return $this->cpostalp;
    }

    public function setCpostalp(string $cpostalp): static
    {
        $this->cpostalp = $cpostalp;

        return $this;
    }

    public function getVillep(): ?string
    {
        return $this->villep;
    }

    public function setVillep(string $villep): static
    {
        $this->villep = $villep;

        return $this;
    }

    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(?string $rib): static
    {
        $this->rib = $rib;

        return $this;
    }

    public function getContactSite(): ?string
    {
        return $this->contact_site;
    }

    public function setContactSite(string $contact_site): static
    {
        $this->contact_site = $contact_site;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

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

    public function getRaisonSociale(): ?string
    {
        return $this->raison_sociale;
    }

    public function setRaisonSociale(?string $raison_sociale): static
    {
        $this->raison_sociale = $raison_sociale;

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
}
