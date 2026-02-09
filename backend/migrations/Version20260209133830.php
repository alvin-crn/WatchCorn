<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209133830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE watched_episode ADD watched_show_id INT NOT NULL');
        $this->addSql('ALTER TABLE watched_episode ADD CONSTRAINT FK_6E4B5ACB6320BA75 FOREIGN KEY (watched_show_id) REFERENCES watched_show (id)');
        $this->addSql('CREATE INDEX IDX_6E4B5ACB6320BA75 ON watched_episode (watched_show_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE watched_episode DROP FOREIGN KEY FK_6E4B5ACB6320BA75');
        $this->addSql('DROP INDEX IDX_6E4B5ACB6320BA75 ON watched_episode');
        $this->addSql('ALTER TABLE watched_episode DROP watched_show_id');
    }
}
