<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260505112123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certification (id INT AUTO_INCREMENT NOT NULL, obtained_at DATETIME NOT NULL, user_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_6C3C6D75A76ED395 (user_id), INDEX IDX_6C3C6D7559027487 (theme_id), UNIQUE INDEX user_theme_unique (user_id, theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cursus (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(160) NOT NULL, price NUMERIC(8, 2) NOT NULL, theme_id INT NOT NULL, UNIQUE INDEX UNIQ_255A0C3989D9B62 (slug), INDEX IDX_255A0C359027487 (theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(160) NOT NULL, price NUMERIC(8, 2) NOT NULL, position INT NOT NULL, content LONGTEXT DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, cursus_id INT NOT NULL, UNIQUE INDEX UNIQ_F87474F3989D9B62 (slug), INDEX IDX_F87474F340AEF4B9 (cursus_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson_progress (id INT AUTO_INCREMENT NOT NULL, is_completed TINYINT DEFAULT 0 NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_6A46B85FA76ED395 (user_id), INDEX IDX_6A46B85FCDF80196 (lesson_id), UNIQUE INDEX user_lesson_unique (user_id, lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, amount NUMERIC(8, 2) NOT NULL, stripe_payment_id VARCHAR(100) DEFAULT NULL, purchased_at DATETIME NOT NULL, user_id INT NOT NULL, cursus_id INT DEFAULT NULL, lesson_id INT DEFAULT NULL, INDEX IDX_6117D13BA76ED395 (user_id), INDEX IDX_6117D13B40AEF4B9 (cursus_id), INDEX IDX_6117D13BCDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, UNIQUE INDEX UNIQ_9775E708989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, is_verified TINYINT DEFAULT 0 NOT NULL, activation_token VARCHAR(64) DEFAULT NULL, activation_token_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D7559027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE cursus ADD CONSTRAINT FK_255A0C359027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F340AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE lesson_progress ADD CONSTRAINT FK_6A46B85FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lesson_progress ADD CONSTRAINT FK_6A46B85FCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B40AEF4B9 FOREIGN KEY (cursus_id) REFERENCES cursus (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75A76ED395');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D7559027487');
        $this->addSql('ALTER TABLE cursus DROP FOREIGN KEY FK_255A0C359027487');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F340AEF4B9');
        $this->addSql('ALTER TABLE lesson_progress DROP FOREIGN KEY FK_6A46B85FA76ED395');
        $this->addSql('ALTER TABLE lesson_progress DROP FOREIGN KEY FK_6A46B85FCDF80196');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B40AEF4B9');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BCDF80196');
        $this->addSql('DROP TABLE certification');
        $this->addSql('DROP TABLE cursus');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE lesson_progress');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
