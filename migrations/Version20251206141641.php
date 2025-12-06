<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206141641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE queue (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, expiry_minutes INT DEFAULT NULL, maximum_entries_per_user INT DEFAULT NULL, created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NOT NULL, UNIQUE INDEX UNIQ_7FFD7F635E237E06A7A91E0B (name, domain), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE queued_user (id INT AUTO_INCREMENT NOT NULL, user_id VARCHAR(255) NOT NULL, expires_at DATETIME(6) DEFAULT NULL, created_at DATETIME(6) NOT NULL, updated_at DATETIME(6) NOT NULL, queue_id BIGINT DEFAULT NULL, INDEX IDX_EA70565D477B5BAE (queue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE queued_user ADD CONSTRAINT FK_EA70565D477B5BAE FOREIGN KEY (queue_id) REFERENCES queue (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE queued_user DROP FOREIGN KEY FK_EA70565D477B5BAE');
        $this->addSql('DROP TABLE queue');
        $this->addSql('DROP TABLE queued_user');
    }
}
