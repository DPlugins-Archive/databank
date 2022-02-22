<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220219000504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO plan (id, slug, name, price, duration, unit, is_enabled) VALUES (nextval(\'plan_id_seq\'), \'monthly\', \'Monthly\', 2, 1, \'month\', true)');
        $this->addSql('INSERT INTO plan (id, slug, name, price, duration, unit, is_enabled) VALUES (nextval(\'plan_id_seq\'), \'annual\', \'Annual\', 18, 12, \'month\', true)');
        $this->addSql('INSERT INTO plan (id, slug, name, price, duration, unit, is_enabled) VALUES (nextval(\'plan_id_seq\'), \'launch-promo\', \'Lauch Promo\', 18, 24, \'month\', true)');
        $this->addSql('INSERT INTO plan (id, slug, name, price, duration, unit, is_enabled) VALUES (nextval(\'plan_id_seq\'), \'scorg-promo\', \'Exclusive - Script Organizer user\', 18, 18, \'month\', true)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM plan WHERE slug = \'monthly\'');
        $this->addSql('DELETE FROM plan WHERE slug = \'annual\'');
        $this->addSql('DELETE FROM plan WHERE slug = \'launch-promo\'');
        $this->addSql('DELETE FROM plan WHERE slug = \'scorg-promo\'');
    }
}
