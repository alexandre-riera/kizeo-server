<?php

namespace App\Entity;

use App\Repository\PortailAutoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortailAutoRepository::class)]
class PortailAuto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $id_contact = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $id_societe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numero_equipement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $marque_motorisation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $modele_motorisation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $marque_coffret_de_commannde = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $modele_coffret_de_commannde = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact_securite_portillon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $presence_boitier_pompiers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_pignon_moteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $espace_protection_pignon_cremaillere_inf_egal_8mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manipulable_manuel_coupure_courant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manoeuvre_depannage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instruction_manoeuvre_depannage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dispositif_coupure_elec_proximite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raccordement_terre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mesure_tension_phase_et_terre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eclairage_zone_debattement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonctionnement_eclairage_zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $presence_feu_clignotant_orange = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $visibilite_clignotant_2_cotes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preavis_clignotant_min_2_sec = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marquage_au_sol = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marquage_zone_refoulement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat_marquage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $conformite_marquage_sol_bandes_jaunes_noirs_45_deg = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonctionnement_cellules = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_a_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_b_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_c_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_d_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_a_prime_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_b_prime_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_c_prime_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cote_en_d_prime_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_bord_primaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_bord_secondaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_surface_vantail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_air_refoulement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position_des_poteaux = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_a = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_a1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_b = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_b1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_c = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_c1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $protection_cisaillement_m = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zone_ecrasement_fin_ouverture_inf_500_mm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $distance_zone_fin_ouverture = null;

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

    public function getMarqueMotorisation(): ?string
    {
        return $this->marque_motorisation;
    }

    public function setMarqueMotorisation(?string $marque_motorisation): static
    {
        $this->marque_motorisation = $marque_motorisation;

        return $this;
    }

    public function getModeleMotorisation(): ?string
    {
        return $this->modele_motorisation;
    }

    public function setModeleMotorisation(?string $modele_motorisation): static
    {
        $this->modele_motorisation = $modele_motorisation;

        return $this;
    }

    public function getMarqueCoffretDeCommannde(): ?string
    {
        return $this->marque_coffret_de_commannde;
    }

    public function setMarqueCoffretDeCommannde(?string $marque_coffret_de_commannde): static
    {
        $this->marque_coffret_de_commannde = $marque_coffret_de_commannde;

        return $this;
    }

    public function getModeleCoffretDeCommannde(): ?string
    {
        return $this->modele_coffret_de_commannde;
    }

    public function setModeleCoffretDeCommannde(?string $modele_coffret_de_commannde): static
    {
        $this->modele_coffret_de_commannde = $modele_coffret_de_commannde;

        return $this;
    }

    public function getContactSecuritePortillon(): ?string
    {
        return $this->contact_securite_portillon;
    }

    public function setContactSecuritePortillon(?string $contact_securite_portillon): static
    {
        $this->contact_securite_portillon = $contact_securite_portillon;

        return $this;
    }

    public function getPresenceBoitierPompiers(): ?string
    {
        return $this->presence_boitier_pompiers;
    }

    public function setPresenceBoitierPompiers(?string $presence_boitier_pompiers): static
    {
        $this->presence_boitier_pompiers = $presence_boitier_pompiers;

        return $this;
    }

    public function getProtectionPignonMoteur(): ?string
    {
        return $this->protection_pignon_moteur;
    }

    public function setProtectionPignonMoteur(?string $protection_pignon_moteur): static
    {
        $this->protection_pignon_moteur = $protection_pignon_moteur;

        return $this;
    }

    public function getEspaceProtectionPignonCremaillereInfEgal8mm(): ?string
    {
        return $this->espace_protection_pignon_cremaillere_inf_egal_8mm;
    }

    public function setEspaceProtectionPignonCremaillereInfEgal8mm(?string $espace_protection_pignon_cremaillere_inf_egal_8mm): static
    {
        $this->espace_protection_pignon_cremaillere_inf_egal_8mm = $espace_protection_pignon_cremaillere_inf_egal_8mm;

        return $this;
    }

    public function getManipulableManuelCoupureCourant(): ?string
    {
        return $this->manipulable_manuel_coupure_courant;
    }

    public function setManipulableManuelCoupureCourant(?string $manipulable_manuel_coupure_courant): static
    {
        $this->manipulable_manuel_coupure_courant = $manipulable_manuel_coupure_courant;

        return $this;
    }

    public function getManoeuvreDepannage(): ?string
    {
        return $this->manoeuvre_depannage;
    }

    public function setManoeuvreDepannage(?string $manoeuvre_depannage): static
    {
        $this->manoeuvre_depannage = $manoeuvre_depannage;

        return $this;
    }

    public function getInstructionManoeuvreDepannage(): ?string
    {
        return $this->instruction_manoeuvre_depannage;
    }

    public function setInstructionManoeuvreDepannage(?string $instruction_manoeuvre_depannage): static
    {
        $this->instruction_manoeuvre_depannage = $instruction_manoeuvre_depannage;

        return $this;
    }

    public function getDispositifCoupureElecProximite(): ?string
    {
        return $this->dispositif_coupure_elec_proximite;
    }

    public function setDispositifCoupureElecProximite(?string $dispositif_coupure_elec_proximite): static
    {
        $this->dispositif_coupure_elec_proximite = $dispositif_coupure_elec_proximite;

        return $this;
    }

    public function getRaccordementTerre(): ?string
    {
        return $this->raccordement_terre;
    }

    public function setRaccordementTerre(?string $raccordement_terre): static
    {
        $this->raccordement_terre = $raccordement_terre;

        return $this;
    }

    public function getMesureTensionPhaseEtTerre(): ?string
    {
        return $this->mesure_tension_phase_et_terre;
    }

    public function setMesureTensionPhaseEtTerre(?string $mesure_tension_phase_et_terre): static
    {
        $this->mesure_tension_phase_et_terre = $mesure_tension_phase_et_terre;

        return $this;
    }

    public function getEclairageZoneDebattement(): ?string
    {
        return $this->eclairage_zone_debattement;
    }

    public function setEclairageZoneDebattement(?string $eclairage_zone_debattement): static
    {
        $this->eclairage_zone_debattement = $eclairage_zone_debattement;

        return $this;
    }

    public function getFonctionnementEclairageZone(): ?string
    {
        return $this->fonctionnement_eclairage_zone;
    }

    public function setFonctionnementEclairageZone(?string $fonctionnement_eclairage_zone): static
    {
        $this->fonctionnement_eclairage_zone = $fonctionnement_eclairage_zone;

        return $this;
    }

    public function getPresenceFeuClignotantOrange(): ?string
    {
        return $this->presence_feu_clignotant_orange;
    }

    public function setPresenceFeuClignotantOrange(?string $presence_feu_clignotant_orange): static
    {
        $this->presence_feu_clignotant_orange = $presence_feu_clignotant_orange;

        return $this;
    }

    public function getVisibiliteClignotant2Cotes(): ?string
    {
        return $this->visibilite_clignotant_2_cotes;
    }

    public function setVisibiliteClignotant2Cotes(?string $visibilite_clignotant_2_cotes): static
    {
        $this->visibilite_clignotant_2_cotes = $visibilite_clignotant_2_cotes;

        return $this;
    }

    public function getPreavisClignotantMin2Sec(): ?string
    {
        return $this->preavis_clignotant_min_2_sec;
    }

    public function setPreavisClignotantMin2Sec(?string $preavis_clignotant_min_2_sec): static
    {
        $this->preavis_clignotant_min_2_sec = $preavis_clignotant_min_2_sec;

        return $this;
    }

    public function getMarquageAuSol(): ?string
    {
        return $this->marquage_au_sol;
    }

    public function setMarquageAuSol(?string $marquage_au_sol): static
    {
        $this->marquage_au_sol = $marquage_au_sol;

        return $this;
    }

    public function getMarquageZoneRefoulement(): ?string
    {
        return $this->marquage_zone_refoulement;
    }

    public function setMarquageZoneRefoulement(?string $marquage_zone_refoulement): static
    {
        $this->marquage_zone_refoulement = $marquage_zone_refoulement;

        return $this;
    }

    public function getEtatMarquage(): ?string
    {
        return $this->etat_marquage;
    }

    public function setEtatMarquage(?string $etat_marquage): static
    {
        $this->etat_marquage = $etat_marquage;

        return $this;
    }

    public function getConformiteMarquageSolBandesJaunesNoirs45Deg(): ?string
    {
        return $this->conformite_marquage_sol_bandes_jaunes_noirs_45_deg;
    }

    public function setConformiteMarquageSolBandesJaunesNoirs45Deg(?string $conformite_marquage_sol_bandes_jaunes_noirs_45_deg): static
    {
        $this->conformite_marquage_sol_bandes_jaunes_noirs_45_deg = $conformite_marquage_sol_bandes_jaunes_noirs_45_deg;

        return $this;
    }

    public function getFonctionnementCellules(): ?string
    {
        return $this->fonctionnement_cellules;
    }

    public function setFonctionnementCellules(?string $fonctionnement_cellules): static
    {
        $this->fonctionnement_cellules = $fonctionnement_cellules;

        return $this;
    }

    public function getCoteEnAMm(): ?string
    {
        return $this->cote_en_a_mm;
    }

    public function setCoteEnAMm(?string $cote_en_a_mm): static
    {
        $this->cote_en_a_mm = $cote_en_a_mm;

        return $this;
    }

    public function getCoteEnBMm(): ?string
    {
        return $this->cote_en_b_mm;
    }

    public function setCoteEnBMm(?string $cote_en_b_mm): static
    {
        $this->cote_en_b_mm = $cote_en_b_mm;

        return $this;
    }

    public function getCoteEnCMm(): ?string
    {
        return $this->cote_en_c_mm;
    }

    public function setCoteEnCMm(?string $cote_en_c_mm): static
    {
        $this->cote_en_c_mm = $cote_en_c_mm;

        return $this;
    }

    public function getCoteEnDMm(): ?string
    {
        return $this->cote_en_d_mm;
    }

    public function setCoteEnDMm(?string $cote_en_d_mm): static
    {
        $this->cote_en_d_mm = $cote_en_d_mm;

        return $this;
    }

    public function getCoteEnAPrimeMm(): ?string
    {
        return $this->cote_en_a_prime_mm;
    }

    public function setCoteEnAPrimeMm(?string $cote_en_a_prime_mm): static
    {
        $this->cote_en_a_prime_mm = $cote_en_a_prime_mm;

        return $this;
    }

    public function getCoteEnBPrimeMm(): ?string
    {
        return $this->cote_en_b_prime_mm;
    }

    public function setCoteEnBPrimeMm(?string $cote_en_b_prime_mm): static
    {
        $this->cote_en_b_prime_mm = $cote_en_b_prime_mm;

        return $this;
    }

    public function getCoteEnCPrimeMm(): ?string
    {
        return $this->cote_en_c_prime_mm;
    }

    public function setCoteEnCPrimeMm(?string $cote_en_c_prime_mm): static
    {
        $this->cote_en_c_prime_mm = $cote_en_c_prime_mm;

        return $this;
    }

    public function getCoteEnDPrimeMm(): ?string
    {
        return $this->cote_en_d_prime_mm;
    }

    public function setCoteEnDPrimeMm(?string $cote_en_d_prime_mm): static
    {
        $this->cote_en_d_prime_mm = $cote_en_d_prime_mm;

        return $this;
    }

    public function getProtectionBordPrimaire(): ?string
    {
        return $this->protection_bord_primaire;
    }

    public function setProtectionBordPrimaire(?string $protection_bord_primaire): static
    {
        $this->protection_bord_primaire = $protection_bord_primaire;

        return $this;
    }

    public function getProtectionBordSecondaire(): ?string
    {
        return $this->protection_bord_secondaire;
    }

    public function setProtectionBordSecondaire(?string $protection_bord_secondaire): static
    {
        $this->protection_bord_secondaire = $protection_bord_secondaire;

        return $this;
    }

    public function getProtectionSurfaceVantail(): ?string
    {
        return $this->protection_surface_vantail;
    }

    public function setProtectionSurfaceVantail(?string $protection_surface_vantail): static
    {
        $this->protection_surface_vantail = $protection_surface_vantail;

        return $this;
    }

    public function getProtectionAirRefoulement(): ?string
    {
        return $this->protection_air_refoulement;
    }

    public function setProtectionAirRefoulement(?string $protection_air_refoulement): static
    {
        $this->protection_air_refoulement = $protection_air_refoulement;

        return $this;
    }

    public function getPositionDesPoteaux(): ?string
    {
        return $this->position_des_poteaux;
    }

    public function setPositionDesPoteaux(?string $position_des_poteaux): static
    {
        $this->position_des_poteaux = $position_des_poteaux;

        return $this;
    }

    public function getProtectionCisaillementA(): ?string
    {
        return $this->protection_cisaillement_a;
    }

    public function setProtectionCisaillementA(?string $protection_cisaillement_a): static
    {
        $this->protection_cisaillement_a = $protection_cisaillement_a;

        return $this;
    }

    public function getProtectionCisaillementA1(): ?string
    {
        return $this->protection_cisaillement_a1;
    }

    public function setProtectionCisaillementA1(?string $protection_cisaillement_a1): static
    {
        $this->protection_cisaillement_a1 = $protection_cisaillement_a1;

        return $this;
    }

    public function getProtectionCisaillementB(): ?string
    {
        return $this->protection_cisaillement_b;
    }

    public function setProtectionCisaillementB(?string $protection_cisaillement_b): static
    {
        $this->protection_cisaillement_b = $protection_cisaillement_b;

        return $this;
    }

    public function getProtectionCisaillementB1(): ?string
    {
        return $this->protection_cisaillement_b1;
    }

    public function setProtectionCisaillementB1(?string $protection_cisaillement_b1): static
    {
        $this->protection_cisaillement_b1 = $protection_cisaillement_b1;

        return $this;
    }

    public function getProtectionCisaillementC(): ?string
    {
        return $this->protection_cisaillement_c;
    }

    public function setProtectionCisaillementC(?string $protection_cisaillement_c): static
    {
        $this->protection_cisaillement_c = $protection_cisaillement_c;

        return $this;
    }

    public function getProtectionCisaillementC1(): ?string
    {
        return $this->protection_cisaillement_c1;
    }

    public function setProtectionCisaillementC1(?string $protection_cisaillement_c1): static
    {
        $this->protection_cisaillement_c1 = $protection_cisaillement_c1;

        return $this;
    }

    public function getProtectionCisaillementM(): ?string
    {
        return $this->protection_cisaillement_m;
    }

    public function setProtectionCisaillementM(?string $protection_cisaillement_m): static
    {
        $this->protection_cisaillement_m = $protection_cisaillement_m;

        return $this;
    }

    public function getZoneEcrasementFinOuvertureInf500Mm(): ?string
    {
        return $this->zone_ecrasement_fin_ouverture_inf_500_mm;
    }

    public function setZoneEcrasementFinOuvertureInf500Mm(?string $zone_ecrasement_fin_ouverture_inf_500_mm): static
    {
        $this->zone_ecrasement_fin_ouverture_inf_500_mm = $zone_ecrasement_fin_ouverture_inf_500_mm;

        return $this;
    }

    public function getDistanceZoneFinOuverture(): ?string
    {
        return $this->distance_zone_fin_ouverture;
    }

    public function setDistanceZoneFinOuverture(?string $distance_zone_fin_ouverture): static
    {
        $this->distance_zone_fin_ouverture = $distance_zone_fin_ouverture;

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
