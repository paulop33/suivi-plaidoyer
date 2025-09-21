<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921065641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE association (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(7) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE candidate_list (id SERIAL NOT NULL, city_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, name_list VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7CE1C0BA8BAC62AF ON candidate_list (city_id)');
        $this->addSql('CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, bareme INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE city (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE city_association (city_id INT NOT NULL, association_id INT NOT NULL, PRIMARY KEY(city_id, association_id))');
        $this->addSql('CREATE INDEX IDX_DE313FC38BAC62AF ON city_association (city_id)');
        $this->addSql('CREATE INDEX IDX_DE313FC3EFB9C8A5 ON city_association (association_id)');
        $this->addSql('CREATE TABLE commitment (id SERIAL NOT NULL, candidate_list_id INT NOT NULL, proposition_id INT NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, update_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, comment_candidate_list TEXT DEFAULT NULL, comment_association TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F3E0CCBBB27FBB2A ON commitment (candidate_list_id)');
        $this->addSql('CREATE INDEX IDX_F3E0CCBBDB96F9E ON commitment (proposition_id)');
        $this->addSql('CREATE TABLE proposition (id SERIAL NOT NULL, category_id INT NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, bareme INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7CDC35312469DE2 ON proposition (category_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE candidate_list ADD CONSTRAINT FK_7CE1C0BA8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE city_association ADD CONSTRAINT FK_DE313FC38BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE city_association ADD CONSTRAINT FK_DE313FC3EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commitment ADD CONSTRAINT FK_F3E0CCBBB27FBB2A FOREIGN KEY (candidate_list_id) REFERENCES candidate_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commitment ADD CONSTRAINT FK_F3E0CCBBDB96F9E FOREIGN KEY (proposition_id) REFERENCES proposition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition ADD CONSTRAINT FK_C7CDC35312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE candidate_list DROP CONSTRAINT FK_7CE1C0BA8BAC62AF');
        $this->addSql('ALTER TABLE city_association DROP CONSTRAINT FK_DE313FC38BAC62AF');
        $this->addSql('ALTER TABLE city_association DROP CONSTRAINT FK_DE313FC3EFB9C8A5');
        $this->addSql('ALTER TABLE commitment DROP CONSTRAINT FK_F3E0CCBBB27FBB2A');
        $this->addSql('ALTER TABLE commitment DROP CONSTRAINT FK_F3E0CCBBDB96F9E');
        $this->addSql('ALTER TABLE proposition DROP CONSTRAINT FK_C7CDC35312469DE2');
        $this->addSql('DROP TABLE association');
        $this->addSql('DROP TABLE candidate_list');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE city_association');
        $this->addSql('DROP TABLE commitment');
        $this->addSql('DROP TABLE proposition');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
