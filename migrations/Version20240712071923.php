<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240712071923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE portail_auto (id INT AUTO_INCREMENT NOT NULL, id_contact VARCHAR(10) DEFAULT NULL, id_societe VARCHAR(10) DEFAULT NULL, numero_equipement VARCHAR(100) DEFAULT NULL, marque_motorisation VARCHAR(100) DEFAULT NULL, modele_motorisation VARCHAR(100) DEFAULT NULL, marque_coffret_de_commannde VARCHAR(100) DEFAULT NULL, modele_coffret_de_commannde VARCHAR(100) DEFAULT NULL, contact_securite_portillon VARCHAR(255) DEFAULT NULL, presence_boitier_pompiers VARCHAR(255) DEFAULT NULL, protection_pignon_moteur VARCHAR(255) DEFAULT NULL, espace_protection_pignon_cremaillere_inf_egal_8mm VARCHAR(255) DEFAULT NULL, manipulable_manuel_coupure_courant VARCHAR(255) DEFAULT NULL, manoeuvre_depannage VARCHAR(255) DEFAULT NULL, instruction_manoeuvre_depannage VARCHAR(255) DEFAULT NULL, dispositif_coupure_elec_proximite VARCHAR(255) DEFAULT NULL, raccordement_terre VARCHAR(255) DEFAULT NULL, mesure_tension_phase_et_terre VARCHAR(255) DEFAULT NULL, eclairage_zone_debattement VARCHAR(255) DEFAULT NULL, fonctionnement_eclairage_zone VARCHAR(255) DEFAULT NULL, presence_feu_clignotant_orange VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE portail_auto');
    }
}
