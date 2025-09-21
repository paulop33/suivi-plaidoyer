<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921095123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug to category and image to proposition';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Add slug column as nullable first
        $this->addSql('ALTER TABLE category ADD slug VARCHAR(255) DEFAULT NULL');

        // Generate slugs for existing categories using a simpler approach
        $this->addSql("
            UPDATE category
            SET slug = LOWER(
                TRIM(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(name, ' ', '-'),
                                    'é', 'e'
                                ),
                                'è', 'e'
                            ),
                            'à', 'a'
                        ),
                        'ç', 'c'
                    ),
                    '-'
                )
            )
            WHERE slug IS NULL
        ");

        // Make slug NOT NULL and add unique constraint
        $this->addSql('ALTER TABLE category ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C1989D9B62 ON category (slug)');

        // Add image column to proposition
        $this->addSql('ALTER TABLE proposition ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_64C19C1989D9B62');
        $this->addSql('ALTER TABLE category DROP slug');
        $this->addSql('ALTER TABLE proposition DROP image');
    }
}
