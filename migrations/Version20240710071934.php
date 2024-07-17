<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240710071934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD numero_equipement VARCHAR(255) DEFAULT NULL, ADD nature VARCHAR(255) DEFAULT NULL, ADD mode_fonctionnement VARCHAR(255) DEFAULT NULL, ADD repere_site_client VARCHAR(255) DEFAULT NULL, ADD mise_en_service VARCHAR(255) DEFAULT NULL, ADD numero_de_serie VARCHAR(255) DEFAULT NULL, ADD marque VARCHAR(255) DEFAULT NULL, ADD hauteur VARCHAR(255) DEFAULT NULL, ADD largeur VARCHAR(255) DEFAULT NULL, ADD plaque_signaletique VARCHAR(255) DEFAULT NULL, ADD anomalies LONGTEXT DEFAULT NULL, ADD etat VARCHAR(255) DEFAULT NULL, ADD dernière_visite VARCHAR(255) DEFAULT NULL, ADD trigramme_tech VARCHAR(3) DEFAULT NULL, ADD date_previsionnelle_visite_1 VARCHAR(255) DEFAULT NULL, ADD date_previsionnelle_visite_2 VARCHAR(255) DEFAULT NULL, ADD date_effective_1 VARCHAR(255) DEFAULT NULL, ADD date_effective_2 VARCHAR(255) DEFAULT NULL, ADD id_contact VARCHAR(255) DEFAULT NULL, ADD code_societe VARCHAR(255) DEFAULT NULL, ADD signature_tech VARCHAR(255) DEFAULT NULL, ADD if_exist_db VARCHAR(255) DEFAULT NULL, ADD code_agence VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD reference_equipement VARCHAR(255) DEFAULT NULL, ADD localisation_sur_site VARCHAR(255) DEFAULT NULL, ADD presence_carnet_entretien VARCHAR(255) DEFAULT NULL, ADD presence_notice_fabricant VARCHAR(255) DEFAULT NULL, ADD etat_general_equipement1 VARCHAR(255) DEFAULT NULL, ADD photo2 VARCHAR(255) DEFAULT NULL, ADD plaque_identification VARCHAR(255) DEFAULT NULL, ADD photo_plaque_identification VARCHAR(255) DEFAULT NULL, ADD marques VARCHAR(255) DEFAULT NULL, ADD marques1 VARCHAR(255) DEFAULT NULL, ADD annee_installation_portail VARCHAR(255) DEFAULT NULL, ADD rappel_normes_et_directives_p1 VARCHAR(255) DEFAULT NULL, ADD normes_et_directives VARCHAR(255) DEFAULT NULL, ADD numero_serrie VARCHAR(255) DEFAULT NULL, ADD modele VARCHAR(255) DEFAULT NULL, ADD date_installation VARCHAR(255) DEFAULT NULL, ADD types_de_portails VARCHAR(255) DEFAULT NULL, ADD types_de_portails1 VARCHAR(255) DEFAULT NULL, ADD nombres_vantaux VARCHAR(255) DEFAULT NULL, ADD types_de_fonctionnement VARCHAR(255) DEFAULT NULL, ADD marque_et_modele_motorisation VARCHAR(255) DEFAULT NULL, ADD marque_et_modele_coffret_comm VARCHAR(255) DEFAULT NULL, ADD photo_motorisation_et_carte VARCHAR(255) DEFAULT NULL, ADD dimension_largeur_passage_uti VARCHAR(255) DEFAULT NULL, ADD dimension_longueur_vantail VARCHAR(255) DEFAULT NULL, ADD dimension_hauteur_vantail VARCHAR(255) DEFAULT NULL, ADD presence_portillon_sur_le_van VARCHAR(255) DEFAULT NULL, ADD contact_securite_sur_portillo VARCHAR(255) DEFAULT NULL, ADD organe_de_commande VARCHAR(255) DEFAULT NULL, ADD presence_boitier_pompiers VARCHAR(255) DEFAULT NULL, ADD types_de_guidage VARCHAR(255) DEFAULT NULL, ADD protection_des_rails_de_roule1 VARCHAR(255) DEFAULT NULL, ADD espace_inferieur_ou_egal_a_8 VARCHAR(255) DEFAULT NULL, ADD photo_espace_entre_rail_et_ga VARCHAR(255) DEFAULT NULL, ADD distance_entre_le_bas_du_poprt VARCHAR(255) DEFAULT NULL, ADD protection_pignon_moteur VARCHAR(255) DEFAULT NULL, ADD espace_entre_la_protection_du VARCHAR(255) DEFAULT NULL, ADD protection_des_galets_en_part VARCHAR(255) DEFAULT NULL, ADD distance_entre_le_haut_du_por1 VARCHAR(255) DEFAULT NULL, ADD espace_entre_le_vantail_et_le VARCHAR(255) DEFAULT NULL, ADD photo_galets_guidage VARCHAR(255) DEFAULT NULL, ADD protection_contre_la_sortie_d VARCHAR(255) DEFAULT NULL, ADD butees_mecaniques_sur_vantail VARCHAR(255) DEFAULT NULL, ADD photo_butee_avant VARCHAR(255) DEFAULT NULL, ADD presence_de_la_butee_mecaniq VARCHAR(255) DEFAULT NULL, ADD photo_butee_arriere VARCHAR(255) DEFAULT NULL, ADD verification_de_l_efficacite VARCHAR(255) DEFAULT NULL, ADD presence_systeme_anti_chutes VARCHAR(255) DEFAULT NULL, ADD photo_systeme_anti_chutes VARCHAR(255) DEFAULT NULL, ADD protection_contre_le_trebuche VARCHAR(255) DEFAULT NULL, ADD absence_de_seuil_ou_surelevat VARCHAR(255) DEFAULT NULL, ADD photo_du_seuil_ou_de_la_surel VARCHAR(255) DEFAULT NULL, ADD marquage_des_parties_sureleve VARCHAR(255) DEFAULT NULL, ADD man_uvre_manuel VARCHAR(255) DEFAULT NULL, ADD portail_manipulable_manuellem1 VARCHAR(255) DEFAULT NULL, ADD man_uvre_de_depannage VARCHAR(255) DEFAULT NULL, ADD en_toute_position_a_arret_le_1 VARCHAR(255) DEFAULT NULL, ADD presence_instruction_manoeuvr VARCHAR(255) DEFAULT NULL, ADD absence_de_dur_mecanique VARCHAR(255) DEFAULT NULL, ADD presence_dispositif_de_coupur VARCHAR(255) DEFAULT NULL, ADD types_de_dispositifs_de_coupu VARCHAR(255) DEFAULT NULL, DROP numero_equipement, DROP nature, DROP mode_fonctionnement, DROP repere_site_client, DROP mise_en_service, DROP numero_de_serie, DROP marque, DROP hauteur, DROP largeur, DROP plaque_signaletique, DROP anomalies, DROP etat, DROP dernière_visite, DROP trigramme_tech, DROP date_previsionnelle_visite_1, DROP date_previsionnelle_visite_2, DROP date_effective_1, DROP date_effective_2, DROP id_contact, DROP code_societe, DROP signature_tech, DROP if_exist_db, DROP code_agence');
    }
}
