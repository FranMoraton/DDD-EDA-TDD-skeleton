<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008094742 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS factions (
                id UUID NOT NULL,
                faction_name VARCHAR(128) NOT NULL,
                description TEXT NOT NULL,
                PRIMARY KEY (id)
            );
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS equipments (
                id UUID NOT NULL,
                name VARCHAR(128) NOT NULL,
                type VARCHAR(128) NOT NULL,
                made_by VARCHAR(128) NOT NULL,
                PRIMARY KEY (id)
            );
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS characters (
                id UUID NOT NULL,
                name VARCHAR(128) NOT NULL,
                birth_date DATE NOT NULL,
                kingdom VARCHAR(128) NOT NULL,
                equipment_id UUID NOT NULL,
                faction_id UUID NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT fk_characters_equipment FOREIGN KEY (equipment_id) REFERENCES equipments (id),
                CONSTRAINT fk_characters_faction FOREIGN KEY (faction_id) REFERENCES factions (id)
            );
        ');

        $this->addSql('CREATE INDEX characters_equipment_id_idx ON characters (equipment_id);');
        $this->addSql('CREATE INDEX characters_faction_id_idx ON characters (faction_id);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS characters;');
        $this->addSql('DROP TABLE IF EXISTS equipments;');
        $this->addSql('DROP TABLE IF EXISTS factions;');
    }
}
