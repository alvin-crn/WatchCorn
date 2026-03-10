<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310151847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_follow (follower_id INT NOT NULL, followed_id INT NOT NULL, INDEX IDX_D665F4DAC24F853 (follower_id), INDEX IDX_D665F4DD956F010 (followed_id), PRIMARY KEY (follower_id, followed_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_follow ADD CONSTRAINT FK_D665F4DAC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_follow ADD CONSTRAINT FK_D665F4DD956F010 FOREIGN KEY (followed_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_follow DROP FOREIGN KEY FK_D665F4DAC24F853');
        $this->addSql('ALTER TABLE user_follow DROP FOREIGN KEY FK_D665F4DD956F010');
        $this->addSql('DROP TABLE user_follow');
    }
}
