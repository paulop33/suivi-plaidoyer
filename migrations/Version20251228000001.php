<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter le champ password à la table candidate_list
 */
final class Version20251228000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter le champ password à la table candidate_list pour protéger l\'accès aux engagements';
    }

    public function up(Schema $schema): void
    {
        // Ajouter la colonne password à la table candidate_list
        $this->addSql('ALTER TABLE candidate_list ADD password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la colonne password de la table candidate_list
        $this->addSql('ALTER TABLE candidate_list DROP password');
    }
}
