<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117124421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Users migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS events (
                id UUID NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                version INT NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            );
        ');

        $this->addSql("CREATE INDEX events_id_version_idx ON events(id,version);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS events;');
    }
}
