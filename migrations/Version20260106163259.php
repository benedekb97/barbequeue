<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106163259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM administrator');
        $this->addSql('ALTER TABLE administrator DROP user_id');
        $this->addSql('ALTER TABLE administrator ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE administrator ADD CONSTRAINT FK_58DF0651A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_58DF0651A76ED395 ON administrator (user_id)');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d6494b09e92c');
        $this->addSql('DROP INDEX uniq_8d93d6494b09e92c');
        $this->addSql('ALTER TABLE "user" DROP administrator_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM administrator');
        $this->addSql('ALTER TABLE administrator DROP CONSTRAINT FK_58DF0651A76ED395');
        $this->addSql('DROP INDEX UNIQ_58DF0651A76ED395');
        $this->addSql('ALTER TABLE administrator ALTER user_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE administrator ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD administrator_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d6494b09e92c FOREIGN KEY (administrator_id) REFERENCES administrator (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d6494b09e92c ON "user" (administrator_id)');
    }
}
