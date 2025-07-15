<?php

namespace App\Entity;

use App\Repository\ContactS40Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactS40Repository::class)]
class ContactS40
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

    /**
     * @var Collection<int, ContratS40>
     */
    #[ORM\OneToMany(targetEntity: ContratS40::class, mappedBy: 'contact')]
    private Collection $contratS40s;

    /**
     * @var Collection<int, MailS40>
     */
    #[ORM\OneToMany(targetEntity: MailS40::class, mappedBy: 'id_contact')]
    private Collection $mailS40s;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function __construct()
    {
        $this->contratS40s = new ArrayCollection();
        $this->mailS40s = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, ContratS40>
     */
    public function getContratS40s(): Collection
    {
        return $this->contratS40s;
    }

    public function addContratS40(ContratS40 $contratS40): static
    {
        if (!$this->contratS40s->contains($contratS40)) {
            $this->contratS40s->add($contratS40);
            $contratS40->setContact($this);
        }

        return $this;
    }

    public function removeContratS40(ContratS40 $contratS40): static
    {
        if ($this->contratS40s->removeElement($contratS40)) {
            // set the owning side to null (unless already changed)
            if ($contratS40->getContact() === $this) {
                $contratS40->setContact(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MailS40>
     */
    public function getMailS40s(): Collection
    {
        return $this->mailS40s;
    }

    public function addMailS40(MailS40 $mailS40): static
    {
        if (!$this->mailS40s->contains($mailS40)) {
            $this->mailS40s->add($mailS40);
            $mailS40->setIdContact($this);
        }

        return $this;
    }

    public function removeMailS40(MailS40 $mailS40): static
    {
        if ($this->mailS40s->removeElement($mailS40)) {
            // set the owning side to null (unless already changed)
            if ($mailS40->getIdContact() === $this) {
                $mailS40->setIdContact(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
