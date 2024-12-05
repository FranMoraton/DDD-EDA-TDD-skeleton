<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008175819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add transactional fields to lock models';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE factions
            ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            ADD version INT NOT NULL DEFAULT 1;
        ');

        $this->addSql('
            ALTER TABLE equipments 
            ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            ADD version INT NOT NULL DEFAULT 1;
        ');

        $this->addSql('
            ALTER TABLE characters 
            ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            ADD version INT NOT NULL DEFAULT 1;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE factions DROP COLUMN updated_at, DROP COLUMN version;');
        $this->addSql('ALTER TABLE equipments DROP COLUMN updated_at, DROP COLUMN version;');
        $this->addSql('ALTER TABLE characters DROP COLUMN updated_at, DROP COLUMN version;');
    }
}
