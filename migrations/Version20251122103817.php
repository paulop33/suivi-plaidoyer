<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122103817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE association (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(7) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidate_list (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, name_list VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, global_comment LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_7CE1C0BA989D9B62 (slug), INDEX IDX_7CE1C0BA8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, bareme INT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2D5B0234989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city_association (city_id INT NOT NULL, association_id INT NOT NULL, INDEX IDX_DE313FC38BAC62AF (city_id), INDEX IDX_DE313FC3EFB9C8A5 (association_id), PRIMARY KEY(city_id, association_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city_specificity (city_id INT NOT NULL, specificity_id INT NOT NULL, INDEX IDX_C994505F8BAC62AF (city_id), INDEX IDX_C994505F5F69A929 (specificity_id), PRIMARY KEY(city_id, specificity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commitment (id INT AUTO_INCREMENT NOT NULL, candidate_list_id INT NOT NULL, proposition_id INT NOT NULL, creation_date DATETIME NOT NULL, update_date DATETIME NOT NULL, comment_candidate_list LONGTEXT DEFAULT NULL, comment_association LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_F3E0CCBBB27FBB2A (candidate_list_id), INDEX IDX_F3E0CCBBDB96F9E (proposition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposition (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, bareme INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, common_expectation LONGTEXT DEFAULT NULL, INDEX IDX_C7CDC35312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specific_expectation (id INT AUTO_INCREMENT NOT NULL, proposition_id INT NOT NULL, specificity_id INT NOT NULL, expectation LONGTEXT NOT NULL, INDEX IDX_A6A3B1A5DB96F9E (proposition_id), INDEX IDX_A6A3B1A55F69A929 (specificity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specificity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_EA204E50989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, association_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649EFB9C8A5 (association_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE candidate_list ADD CONSTRAINT FK_7CE1C0BA8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE city_association ADD CONSTRAINT FK_DE313FC38BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_association ADD CONSTRAINT FK_DE313FC3EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_specificity ADD CONSTRAINT FK_C994505F8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city_specificity ADD CONSTRAINT FK_C994505F5F69A929 FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commitment ADD CONSTRAINT FK_F3E0CCBBB27FBB2A FOREIGN KEY (candidate_list_id) REFERENCES candidate_list (id)');
        $this->addSql('ALTER TABLE commitment ADD CONSTRAINT FK_F3E0CCBBDB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id)');
        $this->addSql('ALTER TABLE proposition ADD CONSTRAINT FK_C7CDC35312469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE specific_expectation ADD CONSTRAINT FK_A6A3B1A5DB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id)');
        $this->addSql('ALTER TABLE specific_expectation ADD CONSTRAINT FK_A6A3B1A55F69A929 FOREIGN KEY (specificity_id) REFERENCES specificity (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_list DROP FOREIGN KEY FK_7CE1C0BA8BAC62AF');
        $this->addSql('ALTER TABLE city_association DROP FOREIGN KEY FK_DE313FC38BAC62AF');
        $this->addSql('ALTER TABLE city_association DROP FOREIGN KEY FK_DE313FC3EFB9C8A5');
        $this->addSql('ALTER TABLE city_specificity DROP FOREIGN KEY FK_C994505F8BAC62AF');
        $this->addSql('ALTER TABLE city_specificity DROP FOREIGN KEY FK_C994505F5F69A929');
        $this->addSql('ALTER TABLE commitment DROP FOREIGN KEY FK_F3E0CCBBB27FBB2A');
        $this->addSql('ALTER TABLE commitment DROP FOREIGN KEY FK_F3E0CCBBDB96F9E');
        $this->addSql('ALTER TABLE proposition DROP FOREIGN KEY FK_C7CDC35312469DE2');
        $this->addSql('ALTER TABLE specific_expectation DROP FOREIGN KEY FK_A6A3B1A5DB96F9E');
        $this->addSql('ALTER TABLE specific_expectation DROP FOREIGN KEY FK_A6A3B1A55F69A929');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649EFB9C8A5');
        $this->addSql('DROP TABLE association');
        $this->addSql('DROP TABLE candidate_list');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE city_association');
        $this->addSql('DROP TABLE city_specificity');
        $this->addSql('DROP TABLE commitment');
        $this->addSql('DROP TABLE proposition');
        $this->addSql('DROP TABLE specific_expectation');
        $this->addSql('DROP TABLE specificity');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
