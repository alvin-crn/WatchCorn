<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209133424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(30) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, restricted TINYINT NOT NULL, profil_pic VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE watched_episode (id INT AUTO_INCREMENT NOT NULL, episode_id INT NOT NULL, watched_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE watched_movie (id INT AUTO_INCREMENT NOT NULL, added_at DATETIME NOT NULL, watched_at DATETIME DEFAULT NULL, movie_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_29C2D8FEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE watched_show (id INT AUTO_INCREMENT NOT NULL, added_at DATETIME NOT NULL, show_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C0FDB468A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE watched_movie ADD CONSTRAINT FK_29C2D8FEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE watched_show ADD CONSTRAINT FK_C0FDB468A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE watched_movie DROP FOREIGN KEY FK_29C2D8FEA76ED395');
        $this->addSql('ALTER TABLE watched_show DROP FOREIGN KEY FK_C0FDB468A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE watched_episode');
        $this->addSql('DROP TABLE watched_movie');
        $this->addSql('DROP TABLE watched_show');
    }
}
