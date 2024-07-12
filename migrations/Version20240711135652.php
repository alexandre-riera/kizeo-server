<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711135652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD seuil_surel_sup_a_5 VARCHAR(50) DEFAULT NULL, ADD marquage_parties_surelevees_non_visibles VARCHAR(50) DEFAULT NULL, ADD portail_immobile_toutes_positions_en_manuel VARCHAR(50) DEFAULT NULL, ADD dur_meca_en_manuel VARCHAR(50) DEFAULT NULL, ADD distance_barreaux_cloture VARCHAR(50) DEFAULT NULL, DROP anomalies');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE portail ADD anomalies LONGTEXT DEFAULT NULL, DROP seuil_surel_sup_a_5, DROP marquage_parties_surelevees_non_visibles, DROP portail_immobile_toutes_positions_en_manuel, DROP dur_meca_en_manuel, DROP distance_barreaux_cloture');
    }
}
