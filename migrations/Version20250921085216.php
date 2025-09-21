<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921085216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug field to city table and populate it from city names';
    }

    public function up(Schema $schema): void
    {
        // Add slug column as nullable first
        $this->addSql('ALTER TABLE city ADD slug VARCHAR(255) DEFAULT NULL');

        // Populate slug field from existing city names
        $this->addSql("
            UPDATE city
            SET slug = LOWER(
                TRIM(
                    REGEXP_REPLACE(
                        REGEXP_REPLACE(
                            name,
                            '[^a-zA-Z0-9\\s-]', '', 'g'
                        ),
                        '[\\s-]+', '-', 'g'
                    ),
                    '-'
                )
            )
        ");

        // Make slug NOT NULL and add unique constraint
        $this->addSql('ALTER TABLE city ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D5B0234989D9B62 ON city (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_2D5B0234989D9B62');
        $this->addSql('ALTER TABLE city DROP slug');
    }
}
