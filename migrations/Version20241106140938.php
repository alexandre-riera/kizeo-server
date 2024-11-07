<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106140938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipement_s10 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s100 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s120 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s130 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s140 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s150 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s160 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s170 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s40 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s50 ADD is_etat_des_lieux_fait TINYINT(1) DEFAULT NULL, ADD is_en_maintenance TINYINT(1) DEFAULT NULL, CHANGE test test VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s60 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s70 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s80 CHANGE dernière_visite derniere_visite VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipement_s10 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s100 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s120 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s130 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s140 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s150 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s160 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s170 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s40 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s50 DROP is_etat_des_lieux_fait, DROP is_en_maintenance, CHANGE test test VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE equipement_s60 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s70 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipement_s80 CHANGE derniere_visite dernière_visite VARCHAR(255) DEFAULT NULL');
    }
}
