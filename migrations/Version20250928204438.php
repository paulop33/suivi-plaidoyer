<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928204438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE refusal_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE engagement_id_seq CASCADE');
        $this->addSql('ALTER TABLE refusal DROP CONSTRAINT fk_9a64f34ab27fbb2a');
        $this->addSql('ALTER TABLE refusal DROP CONSTRAINT fk_9a64f34adb96f9e');
        $this->addSql('ALTER TABLE engagement DROP CONSTRAINT fk_d86f0141b27fbb2a');
        $this->addSql('ALTER TABLE engagement DROP CONSTRAINT fk_d86f0141db96f9e');
        $this->addSql('DROP TABLE refusal');
        $this->addSql('DROP TABLE engagement');
        $this->addSql('ALTER TABLE commitment ADD status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE refusal_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE engagement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE refusal (id SERIAL NOT NULL, candidate_list_id INT NOT NULL, proposition_id INT NOT NULL, reason TEXT NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, update_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_9a64f34ab27fbb2a ON refusal (candidate_list_id)');
        $this->addSql('CREATE INDEX idx_9a64f34adb96f9e ON refusal (proposition_id)');
        $this->addSql('CREATE TABLE engagement (id SERIAL NOT NULL, candidate_list_id INT NOT NULL, proposition_id INT NOT NULL, status VARCHAR(20) DEFAULT NULL, reason TEXT DEFAULT NULL, comment_candidate_list TEXT DEFAULT NULL, comment_association TEXT DEFAULT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, update_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d86f0141b27fbb2a ON engagement (candidate_list_id)');
        $this->addSql('CREATE INDEX idx_d86f0141db96f9e ON engagement (proposition_id)');
        $this->addSql('ALTER TABLE refusal ADD CONSTRAINT fk_9a64f34ab27fbb2a FOREIGN KEY (candidate_list_id) REFERENCES candidate_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE refusal ADD CONSTRAINT fk_9a64f34adb96f9e FOREIGN KEY (proposition_id) REFERENCES proposition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE engagement ADD CONSTRAINT fk_d86f0141b27fbb2a FOREIGN KEY (candidate_list_id) REFERENCES candidate_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE engagement ADD CONSTRAINT fk_d86f0141db96f9e FOREIGN KEY (proposition_id) REFERENCES proposition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commitment DROP status');
    }
}
