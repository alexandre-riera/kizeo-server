<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919085712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD photo_etat_general_portail VARCHAR(255) DEFAULT NULL, ADD photo_plaque_identification VARCHAR(255) DEFAULT NULL, ADD photo_motorisation_et_carte VARCHAR(255) DEFAULT NULL, ADD photo_espace_entre_rail_et_galet VARCHAR(255) DEFAULT NULL, ADD photo_galets_guidage VARCHAR(255) DEFAULT NULL, ADD photo_butee_avant VARCHAR(255) DEFAULT NULL, ADD photo_butee_arriere VARCHAR(255) DEFAULT NULL, ADD photo_systeme_anti_chute VARCHAR(255) DEFAULT NULL, ADD photo_du_seuil_et_surelevation VARCHAR(255) DEFAULT NULL, ADD photo_du_marquage_au_sol VARCHAR(255) DEFAULT NULL, ADD photo_cellules_cote_moteur VARCHAR(255) DEFAULT NULL, ADD photo_general_bord_primaire VARCHAR(255) DEFAULT NULL, ADD photo_general_bord_secondaire VARCHAR(255) DEFAULT NULL, ADD photo_moteur_portail_ouvert VARCHAR(255) DEFAULT NULL, ADD photo_zone_ecrasement VARCHAR(255) DEFAULT NULL, ADD photos_supplementaires VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail DROP photo_etat_general_portail, DROP photo_plaque_identification, DROP photo_motorisation_et_carte, DROP photo_espace_entre_rail_et_galet, DROP photo_galets_guidage, DROP photo_butee_avant, DROP photo_butee_arriere, DROP photo_systeme_anti_chute, DROP photo_du_seuil_et_surelevation, DROP photo_du_marquage_au_sol, DROP photo_cellules_cote_moteur, DROP photo_general_bord_primaire, DROP photo_general_bord_secondaire, DROP photo_moteur_portail_ouvert, DROP photo_zone_ecrasement, DROP photos_supplementaires');
    }
}
