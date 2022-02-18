<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220218034033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE billing_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE billing_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE blob_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reset_password_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE revision_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE snippet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE billing (id INT NOT NULL, plan_id INT DEFAULT NULL, credit DOUBLE PRECISION DEFAULT \'0\' NOT NULL, is_active BOOLEAN DEFAULT false NOT NULL, is_auto_renewal BOOLEAN DEFAULT false NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EC224CAAE899029B ON billing (plan_id)');
        $this->addSql('COMMENT ON COLUMN billing.expired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE billing_history (id INT NOT NULL, billing_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, description TEXT DEFAULT NULL, type VARCHAR(180) NOT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4C94FB9C3B025C87 ON billing_history (billing_id)');
        $this->addSql('COMMENT ON COLUMN billing_history.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE blob (id INT NOT NULL, snippet_id INT NOT NULL, uuid UUID NOT NULL, hash TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, size INT DEFAULT 0 NOT NULL, meta JSON DEFAULT NULL, content TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B07FA5CCD17F50A6 ON blob (uuid)');
        $this->addSql('CREATE INDEX IDX_B07FA5CC6E34B975 ON blob (snippet_id)');
        $this->addSql('COMMENT ON COLUMN blob.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blob.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN blob.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, billing_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_verified BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E7927C74 ON person (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176F85E0677 ON person (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD1763B025C87 ON person (billing_id)');
        $this->addSql('COMMENT ON COLUMN person.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE plan (id INT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, duration INT NOT NULL, unit VARCHAR(180) NOT NULL, is_enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DD5A5B7D989D9B62 ON plan (slug)');
        $this->addSql('CREATE TABLE reset_password_request (id INT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE revision (id INT NOT NULL, blob_id INT NOT NULL, uuid UUID NOT NULL, hash TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, size INT DEFAULT 0 NOT NULL, content TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D6315CCD17F50A6 ON revision (uuid)');
        $this->addSql('CREATE INDEX IDX_6D6315CCED3E8EA5 ON revision (blob_id)');
        $this->addSql('COMMENT ON COLUMN revision.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN revision.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE snippet (id INT NOT NULL, person_id INT NOT NULL, uuid UUID NOT NULL, namespace VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, is_public BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, meta JSON DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_961C8CD5D17F50A6 ON snippet (uuid)');
        $this->addSql('CREATE INDEX IDX_961C8CD5217BBB47 ON snippet (person_id)');
        $this->addSql('COMMENT ON COLUMN snippet.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN snippet.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, person_id INT NOT NULL, uuid UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B783D17F50A6 ON tag (uuid)');
        $this->addSql('CREATE INDEX IDX_389B783217BBB47 ON tag (person_id)');
        $this->addSql('COMMENT ON COLUMN tag.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tag_snippet (tag_id INT NOT NULL, snippet_id INT NOT NULL, PRIMARY KEY(tag_id, snippet_id))');
        $this->addSql('CREATE INDEX IDX_A42DA17FBAD26311 ON tag_snippet (tag_id)');
        $this->addSql('CREATE INDEX IDX_A42DA17F6E34B975 ON tag_snippet (snippet_id)');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_EC224CAAE899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing_history ADD CONSTRAINT FK_4C94FB9C3B025C87 FOREIGN KEY (billing_id) REFERENCES billing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blob ADD CONSTRAINT FK_B07FA5CC6E34B975 FOREIGN KEY (snippet_id) REFERENCES snippet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1763B025C87 FOREIGN KEY (billing_id) REFERENCES billing (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT FK_6D6315CCED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blob (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE snippet ADD CONSTRAINT FK_961C8CD5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag_snippet ADD CONSTRAINT FK_A42DA17FBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag_snippet ADD CONSTRAINT FK_A42DA17F6E34B975 FOREIGN KEY (snippet_id) REFERENCES snippet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX name_idx ON snippet (LOWER(name))');
        $this->addSql('CREATE INDEX description_idx ON snippet (LOWER(description))');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE billing_history DROP CONSTRAINT FK_4C94FB9C3B025C87');
        $this->addSql('ALTER TABLE person DROP CONSTRAINT FK_34DCD1763B025C87');
        $this->addSql('ALTER TABLE revision DROP CONSTRAINT FK_6D6315CCED3E8EA5');
        $this->addSql('ALTER TABLE reset_password_request DROP CONSTRAINT FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE snippet DROP CONSTRAINT FK_961C8CD5217BBB47');
        $this->addSql('ALTER TABLE tag DROP CONSTRAINT FK_389B783217BBB47');
        $this->addSql('ALTER TABLE billing DROP CONSTRAINT FK_EC224CAAE899029B');
        $this->addSql('ALTER TABLE blob DROP CONSTRAINT FK_B07FA5CC6E34B975');
        $this->addSql('ALTER TABLE tag_snippet DROP CONSTRAINT FK_A42DA17F6E34B975');
        $this->addSql('ALTER TABLE tag_snippet DROP CONSTRAINT FK_A42DA17FBAD26311');
        $this->addSql('DROP SEQUENCE billing_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE billing_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE blob_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reset_password_request_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE revision_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE snippet_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('DROP TABLE billing');
        $this->addSql('DROP TABLE billing_history');
        $this->addSql('DROP TABLE blob');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE revision');
        $this->addSql('DROP TABLE snippet');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_snippet');
    }
}
