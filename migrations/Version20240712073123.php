<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240712073123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail_auto ADD visibilite_clignotant_2_cotes VARCHAR(255) DEFAULT NULL, ADD preavis_clignotant_min_2_sec VARCHAR(255) DEFAULT NULL, ADD marquage_au_sol VARCHAR(255) DEFAULT NULL, ADD marquage_zone_refoulement VARCHAR(255) DEFAULT NULL, ADD etat_marquage VARCHAR(255) DEFAULT NULL, ADD conformite_marquage_sol_bandes_jaunes_noirs_45_deg VARCHAR(255) DEFAULT NULL, ADD fonctionnement_cellules VARCHAR(255) DEFAULT NULL, ADD cote_en_a_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_b_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_c_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_d_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_a_prime_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_b_prime_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_c_prime_mm VARCHAR(255) DEFAULT NULL, ADD cote_en_d_prime_mm VARCHAR(255) DEFAULT NULL, ADD protection_bord_primaire VARCHAR(255) DEFAULT NULL, ADD protection_bord_secondaire VARCHAR(255) DEFAULT NULL, ADD protection_surface_vantail VARCHAR(255) DEFAULT NULL, ADD protection_air_refoulement VARCHAR(255) DEFAULT NULL, ADD position_des_poteaux VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_a VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_a1 VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_b VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_b1 VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_c VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_c1 VARCHAR(255) DEFAULT NULL, ADD protection_cisaillement_m VARCHAR(255) DEFAULT NULL, ADD zone_ecrasement_fin_ouverture_inf_500_mm VARCHAR(255) DEFAULT NULL, ADD distance_zone_fin_ouverture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail_auto DROP visibilite_clignotant_2_cotes, DROP preavis_clignotant_min_2_sec, DROP marquage_au_sol, DROP marquage_zone_refoulement, DROP etat_marquage, DROP conformite_marquage_sol_bandes_jaunes_noirs_45_deg, DROP fonctionnement_cellules, DROP cote_en_a_mm, DROP cote_en_b_mm, DROP cote_en_c_mm, DROP cote_en_d_mm, DROP cote_en_a_prime_mm, DROP cote_en_b_prime_mm, DROP cote_en_c_prime_mm, DROP cote_en_d_prime_mm, DROP protection_bord_primaire, DROP protection_bord_secondaire, DROP protection_surface_vantail, DROP protection_air_refoulement, DROP position_des_poteaux, DROP protection_cisaillement_a, DROP protection_cisaillement_a1, DROP protection_cisaillement_b, DROP protection_cisaillement_b1, DROP protection_cisaillement_c, DROP protection_cisaillement_c1, DROP protection_cisaillement_m, DROP zone_ecrasement_fin_ouverture_inf_500_mm, DROP distance_zone_fin_ouverture');
    }
}
