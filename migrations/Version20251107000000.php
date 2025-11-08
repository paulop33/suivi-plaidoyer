<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les entités de suivi post-élections
 */
final class Version20251107000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les tables pour le suivi des listes élues et des avancées des engagements';
    }

    public function up(Schema $schema): void
    {
        // Table elected_list
        $this->addSql('CREATE TABLE elected_list (
            id SERIAL NOT NULL,
            candidate_list_id INT NOT NULL,
            city_id INT NOT NULL,
            election_date DATE NOT NULL,
            mandate_start_year INT NOT NULL,
            mandate_end_year INT NOT NULL,
            mayor_name VARCHAR(255) NOT NULL,
            contact_email VARCHAR(255) DEFAULT NULL,
            contact_phone VARCHAR(20) DEFAULT NULL,
            program_summary TEXT DEFAULT NULL,
            creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            update_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            is_active BOOLEAN NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_elected_list_candidate_list ON elected_list (candidate_list_id)');
        $this->addSql('CREATE INDEX IDX_elected_list_city ON elected_list (city_id)');
        $this->addSql('CREATE INDEX IDX_elected_list_active ON elected_list (is_active)');
        $this->addSql('CREATE INDEX IDX_elected_list_mandate ON elected_list (mandate_start_year, mandate_end_year)');

        // Table progress_update
        $this->addSql('CREATE TABLE progress_update (
            id SERIAL NOT NULL,
            commitment_id INT NOT NULL,
            elected_list_id INT NOT NULL,
            updated_by_id INT DEFAULT NULL,
            validated_by_id INT DEFAULT NULL,
            status VARCHAR(255) NOT NULL,
            update_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            description TEXT NOT NULL,
            evidence TEXT DEFAULT NULL,
            evidence_links TEXT DEFAULT NULL,
            progress_percentage INT DEFAULT NULL,
            expected_completion_date DATE DEFAULT NULL,
            challenges TEXT DEFAULT NULL,
            next_steps TEXT DEFAULT NULL,
            creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            budget_allocated DOUBLE PRECISION DEFAULT NULL,
            budget_spent DOUBLE PRECISION DEFAULT NULL,
            is_validated BOOLEAN NOT NULL,
            validation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX IDX_progress_update_commitment ON progress_update (commitment_id)');
        $this->addSql('CREATE INDEX IDX_progress_update_elected_list ON progress_update (elected_list_id)');
        $this->addSql('CREATE INDEX IDX_progress_update_updated_by ON progress_update (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_progress_update_validated_by ON progress_update (validated_by_id)');
        $this->addSql('CREATE INDEX IDX_progress_update_status ON progress_update (status)');
        $this->addSql('CREATE INDEX IDX_progress_update_date ON progress_update (update_date)');
        $this->addSql('CREATE INDEX IDX_progress_update_validated ON progress_update (is_validated)');

        // Contraintes de clés étrangères
        $this->addSql('ALTER TABLE elected_list ADD CONSTRAINT FK_elected_list_candidate_list
            FOREIGN KEY (candidate_list_id) REFERENCES candidate_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE elected_list ADD CONSTRAINT FK_elected_list_city
            FOREIGN KEY (city_id) REFERENCES city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE progress_update ADD CONSTRAINT FK_progress_update_commitment
            FOREIGN KEY (commitment_id) REFERENCES commitment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progress_update ADD CONSTRAINT FK_progress_update_elected_list
            FOREIGN KEY (elected_list_id) REFERENCES elected_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progress_update ADD CONSTRAINT FK_progress_update_updated_by
            FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progress_update ADD CONSTRAINT FK_progress_update_validated_by
            FOREIGN KEY (validated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Commentaires pour la documentation
        $this->addSql('COMMENT ON TABLE elected_list IS \'Table des listes elues apres les elections municipales\'');
        $this->addSql('COMMENT ON TABLE progress_update IS \'Table des mises a jour de progression des engagements\'');

        $this->addSql('COMMENT ON COLUMN elected_list.election_date IS \'Date des elections municipales\'');
        $this->addSql('COMMENT ON COLUMN elected_list.mandate_start_year IS \'Annee de debut du mandat\'');
        $this->addSql('COMMENT ON COLUMN elected_list.mandate_end_year IS \'Annee de fin du mandat\'');
        $this->addSql('COMMENT ON COLUMN elected_list.is_active IS \'Indique si cette liste est actuellement en fonction\'');

        $this->addSql('COMMENT ON COLUMN progress_update.status IS \'Statut implementation (enum ImplementationStatus)\'');
        $this->addSql('COMMENT ON COLUMN progress_update.progress_percentage IS \'Pourcentage avancement (0-100)\'');
        $this->addSql('COMMENT ON COLUMN progress_update.budget_allocated IS \'Budget total alloue en euros\'');
        $this->addSql('COMMENT ON COLUMN progress_update.budget_spent IS \'Budget deja depense en euros\'');
        $this->addSql('COMMENT ON COLUMN progress_update.is_validated IS \'Indique si cette mise a jour a ete validee\'');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les contraintes de clés étrangères
        $this->addSql('ALTER TABLE progress_update DROP CONSTRAINT FK_progress_update_commitment');
        $this->addSql('ALTER TABLE progress_update DROP CONSTRAINT FK_progress_update_elected_list');
        $this->addSql('ALTER TABLE progress_update DROP CONSTRAINT FK_progress_update_updated_by');
        $this->addSql('ALTER TABLE progress_update DROP CONSTRAINT FK_progress_update_validated_by');

        $this->addSql('ALTER TABLE elected_list DROP CONSTRAINT FK_elected_list_candidate_list');
        $this->addSql('ALTER TABLE elected_list DROP CONSTRAINT FK_elected_list_city');

        // Supprimer les tables
        $this->addSql('DROP TABLE progress_update');
        $this->addSql('DROP TABLE elected_list');
    }
}
