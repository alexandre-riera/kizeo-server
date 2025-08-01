<?php

namespace App\Entity;

use App\Repository\ContactS70Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactS70Repository::class)]
class ContactS70
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
     * @var Collection<int, ContratS70>
     */
    #[ORM\OneToMany(targetEntity: ContratS70::class, mappedBy: 'contact')]
    private Collection $contratS70s;

    /**
     * @var Collection<int, MailS70>
     */
    #[ORM\OneToMany(targetEntity: MailS70::class, mappedBy: 'id_contact')]
    private Collection $mailS70s;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function __construct()
    {
        $this->contratS70s = new ArrayCollection();
        $this->mailS70s = new ArrayCollection();
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
     * @return Collection<int, ContratS70>
     */
    public function getContratS70s(): Collection
    {
        return $this->contratS70s;
    }

    public function addContratS70(ContratS70 $contratS70): static
    {
        if (!$this->contratS70s->contains($contratS70)) {
            $this->contratS70s->add($contratS70);
            $contratS70->setContact($this);
        }

        return $this;
    }

    public function removeContratS70(ContratS70 $contratS70): static
    {
        if ($this->contratS70s->removeElement($contratS70)) {
            // set the owning side to null (unless already changed)
            if ($contratS70->getContact() === $this) {
                $contratS70->setContact(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MailS70>
     */
    public function getMailS70s(): Collection
    {
        return $this->mailS70s;
    }

    public function addMailS70(MailS70 $mailS70): static
    {
        if (!$this->mailS70s->contains($mailS70)) {
            $this->mailS70s->add($mailS70);
            $mailS70->setIdContact($this);
        }

        return $this;
    }

    public function removeMailS70(MailS70 $mailS70): static
    {
        if ($this->mailS70s->removeElement($mailS70)) {
            // set the owning side to null (unless already changed)
            if ($mailS70->getIdContact() === $this) {
                $mailS70->setIdContact(null);
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
