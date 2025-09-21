<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921200429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug field to candidate_list table';
    }

    public function up(Schema $schema): void
    {
        // Add slug column as nullable first
        $this->addSql('ALTER TABLE candidate_list ADD slug VARCHAR(255) DEFAULT NULL');

        // Populate slug field from existing firstname and lastname
        $this->addSql("
            UPDATE candidate_list
            SET slug = LOWER(
                TRIM(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            CONCAT(firstname, '-', lastname),
                                                            ' ', '-'
                                                        ),
                                                        'é', 'e'
                                                    ),
                                                    'è', 'e'
                                                ),
                                                'ê', 'e'
                                            ),
                                            'à', 'a'
                                        ),
                                        'â', 'a'
                                    ),
                                    'ç', 'c'
                                ),
                                'ô', 'o'
                            ),
                            'î', 'i'
                        ),
                        'ù', 'u'
                    ),
                    '-'
                )
            )
            WHERE slug IS NULL
        ");

        // Make slug NOT NULL and add unique constraint
        $this->addSql('ALTER TABLE candidate_list ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CE1C0BA989D9B62 ON candidate_list (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_7CE1C0BA989D9B62');
        $this->addSql('ALTER TABLE candidate_list DROP slug');
    }
}
