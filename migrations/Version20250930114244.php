<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930114244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE specific_expectation (id SERIAL NOT NULL, proposition_id INT NOT NULL, specificity_id INT NOT NULL, expectation TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A6A3B1A5DB96F9E ON specific_expectation (proposition_id)');
        $this->addSql('CREATE INDEX IDX_A6A3B1A55F69A929 ON specific_expectation (specificity_id)');
        $this->addSql('ALTER TABLE specific_expectation ADD CONSTRAINT FK_A6A3B1A5DB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE specific_expectation ADD CONSTRAINT FK_A6A3B1A55F69A929 FOREIGN KEY (specificity_id) REFERENCES specificity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition_specificity DROP CONSTRAINT fk_96d4d5b35f69a929');
        $this->addSql('ALTER TABLE proposition_specificity DROP CONSTRAINT fk_96d4d5b3db96f9e');
        $this->addSql('DROP TABLE proposition_specificity');
        $this->addSql('ALTER TABLE proposition ADD common_expectation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposition DROP is_common_expectation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE proposition_specificity (proposition_id INT NOT NULL, specificity_id INT NOT NULL, PRIMARY KEY(proposition_id, specificity_id))');
        $this->addSql('CREATE INDEX idx_96d4d5b35f69a929 ON proposition_specificity (specificity_id)');
        $this->addSql('CREATE INDEX idx_96d4d5b3db96f9e ON proposition_specificity (proposition_id)');
        $this->addSql('ALTER TABLE proposition_specificity ADD CONSTRAINT fk_96d4d5b35f69a929 FOREIGN KEY (specificity_id) REFERENCES specificity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition_specificity ADD CONSTRAINT fk_96d4d5b3db96f9e FOREIGN KEY (proposition_id) REFERENCES proposition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE specific_expectation DROP CONSTRAINT FK_A6A3B1A5DB96F9E');
        $this->addSql('ALTER TABLE specific_expectation DROP CONSTRAINT FK_A6A3B1A55F69A929');
        $this->addSql('DROP TABLE specific_expectation');
        $this->addSql('ALTER TABLE proposition ADD is_common_expectation BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE proposition DROP common_expectation');
    }
}
