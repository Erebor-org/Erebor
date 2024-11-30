<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241130095500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characters (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, user_id INT DEFAULT NULL, pseudo VARCHAR(255) NOT NULL, ankama_pseudo VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, level INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_archived BOOLEAN NOT NULL, rank_id INT NOT NULL, recruiter_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3A29410E7616678F ON characters (rank_id)');
        $this->addSql('CREATE INDEX IDX_3A29410E156BE243 ON characters (recruiter_id)');
        $this->addSql('CREATE TABLE ranks (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(255) NOT NULL, required_days INT NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410E7616678F FOREIGN KEY (rank_id) REFERENCES ranks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410E156BE243 FOREIGN KEY (recruiter_id) REFERENCES characters (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE characters DROP CONSTRAINT FK_3A29410E7616678F');
        $this->addSql('ALTER TABLE characters DROP CONSTRAINT FK_3A29410E156BE243');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE ranks');
    }
}
