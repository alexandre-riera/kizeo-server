<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240712073919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portail_environement (id INT AUTO_INCREMENT NOT NULL, id_contact VARCHAR(255) DEFAULT NULL, id_societe VARCHAR(255) DEFAULT NULL, numero_equipement VARCHAR(255) DEFAULT NULL, distance_cloture_ext_et_vantail_d1_mm VARCHAR(255) DEFAULT NULL, dimensions_mailles_grillage_ext_mm VARCHAR(255) DEFAULT NULL, distance_grillage_et_vantail_int_d2_mm VARCHAR(255) DEFAULT NULL, dimensions_mailles_grillage_int_mm VARCHAR(255) DEFAULT NULL, dimensions_mailles_tablier_mm VARCHAR(255) DEFAULT NULL, distance_barreaux_vantail_mm VARCHAR(255) DEFAULT NULL, valeurs_mesurees_point_1 VARCHAR(255) DEFAULT NULL, valeurs_mesurees_point_2 VARCHAR(255) DEFAULT NULL, valeurs_mesurees_point_3 VARCHAR(255) DEFAULT NULL, valeurs_mesurees_point_4 VARCHAR(255) DEFAULT NULL, valeurs_mesurees_point_5 VARCHAR(255) DEFAULT NULL, commentaire_supp_si_necessaire VARCHAR(255) NOT NULL, photo_sup_si_necessaire VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE portail_environement');
    }
}
