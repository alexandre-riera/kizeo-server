<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711135315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD espace_inf_8mm_rail_protection_galets VARCHAR(50) DEFAULT NULL, ADD distance_bas_portail_rail_inferieur_sol VARCHAR(50) DEFAULT NULL, ADD espace_haut_portail_platine_galets_guidage VARCHAR(50) DEFAULT NULL, ADD espace_vantail_galets_guidage_inf_8 VARCHAR(50) DEFAULT NULL, ADD butee_meca_avant_sur_vantail VARCHAR(50) DEFAULT NULL, ADD butee_meca_arriere_sur_vantail VARCHAR(50) DEFAULT NULL, ADD efficacite_butees_en_manuel VARCHAR(50) DEFAULT NULL, ADD systeme_anti_chutes VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail DROP espace_inf_8mm_rail_protection_galets, DROP distance_bas_portail_rail_inferieur_sol, DROP espace_haut_portail_platine_galets_guidage, DROP espace_vantail_galets_guidage_inf_8, DROP butee_meca_avant_sur_vantail, DROP butee_meca_arriere_sur_vantail, DROP efficacite_butees_en_manuel, DROP systeme_anti_chutes');
    }
}
