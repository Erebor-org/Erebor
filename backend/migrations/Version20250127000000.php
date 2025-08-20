<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les nouvelles colonnes à dofusdle_monster
 */
final class Version20250127000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les nouvelles colonnes pour les zones et stats aux monstres Dofusdle';
    }

    public function up(Schema $schema): void
    {
        // Ajout des nouvelles colonnes pour les zones
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN super_area VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN area VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN subarea VARCHAR(255) DEFAULT NULL');
        
        // Ajout des nouvelles colonnes pour les stats
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN level_min INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN level_max INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN hp_min INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN hp_max INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN ap_min INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN ap_max INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN mp_min INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN mp_max INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN resistances_max JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN race VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN image_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE dofusdle_monster ADD COLUMN dominant_color VARCHAR(7) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Suppression des colonnes ajoutées
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN super_area');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN area');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN subarea');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN level_min');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN level_max');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN hp_min');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN hp_max');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN ap_min');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN ap_max');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN mp_min');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN mp_max');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN resistances_max');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN race');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN image_url');
        $this->addSql('ALTER TABLE dofusdle_monster DROP COLUMN dominant_color');
    }
}
