<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311133356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE follow (id INT AUTO_INCREMENT NOT NULL, followed_at DATETIME NOT NULL, follower_id INT NOT NULL, following_id INT NOT NULL, INDEX IDX_68344470AC24F853 (follower_id), INDEX IDX_683444701816E3A3 (following_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_683444701816E3A3 FOREIGN KEY (following_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_follow DROP FOREIGN KEY `FK_D665F4DAC24F853`');
        $this->addSql('ALTER TABLE user_follow DROP FOREIGN KEY `FK_D665F4DD956F010`');
        $this->addSql('DROP TABLE user_follow');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_follow (follower_id INT NOT NULL, followed_id INT NOT NULL, INDEX IDX_D665F4DAC24F853 (follower_id), INDEX IDX_D665F4DD956F010 (followed_id), PRIMARY KEY (follower_id, followed_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_follow ADD CONSTRAINT `FK_D665F4DAC24F853` FOREIGN KEY (follower_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user_follow ADD CONSTRAINT `FK_D665F4DD956F010` FOREIGN KEY (followed_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_68344470AC24F853');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_683444701816E3A3');
        $this->addSql('DROP TABLE follow');
    }
}
