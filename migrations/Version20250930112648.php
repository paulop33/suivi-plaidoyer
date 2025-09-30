<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930112648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city_specificity (city_id INT NOT NULL, specificity_id INT NOT NULL, PRIMARY KEY(city_id, specificity_id))');
        $this->addSql('CREATE INDEX IDX_C994505F8BAC62AF ON city_specificity (city_id)');
        $this->addSql('CREATE INDEX IDX_C994505F5F69A929 ON city_specificity (specificity_id)');
        $this->addSql('CREATE TABLE proposition_specificity (proposition_id INT NOT NULL, specificity_id INT NOT NULL, PRIMARY KEY(proposition_id, specificity_id))');
        $this->addSql('CREATE INDEX IDX_96D4D5B3DB96F9E ON proposition_specificity (proposition_id)');
        $this->addSql('CREATE INDEX IDX_96D4D5B35F69A929 ON proposition_specificity (specificity_id)');
        $this->addSql('CREATE TABLE specificity (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EA204E50989D9B62 ON specificity (slug)');
        $this->addSql('ALTER TABLE city_specificity ADD CONSTRAINT FK_C994505F8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE city_specificity ADD CONSTRAINT FK_C994505F5F69A929 FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition_specificity ADD CONSTRAINT FK_96D4D5B3DB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition_specificity ADD CONSTRAINT FK_96D4D5B35F69A929 FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition ADD is_common_expectation BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE city_specificity DROP CONSTRAINT FK_C994505F8BAC62AF');
        $this->addSql('ALTER TABLE city_specificity DROP CONSTRAINT FK_C994505F5F69A929');
        $this->addSql('ALTER TABLE proposition_specificity DROP CONSTRAINT FK_96D4D5B3DB96F9E');
        $this->addSql('ALTER TABLE proposition_specificity DROP CONSTRAINT FK_96D4D5B35F69A929');
        $this->addSql('DROP TABLE city_specificity');
        $this->addSql('DROP TABLE proposition_specificity');
        $this->addSql('DROP TABLE specificity');
        $this->addSql('ALTER TABLE proposition DROP is_common_expectation');
    }
}
