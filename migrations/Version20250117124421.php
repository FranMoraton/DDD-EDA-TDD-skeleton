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
        return 'Events migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS events (
                id UUID NOT NULL,
                base_event_id INT NOT NULL,
                sell_mode VARCHAR(50) NOT NULL,
                organizer_company_id VARCHAR(50) NULL,
                title VARCHAR(255) NOT NULL,
                event_start_date TIMESTAMP NOT NULL,
                event_end_date TIMESTAMP NOT NULL,
                event_id INT NOT NULL,
                sell_from TIMESTAMP NOT NULL,
                sell_to TIMESTAMP NOT NULL,
                sold_out BOOLEAN NOT NULL DEFAULT FALSE,
                zones JSONB NOT NULL,
                request_time TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                version INT NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            );
        ');

        $this->addSql("CREATE INDEX events_id_version_idx ON events(id,version);");
        $this->addSql("CREATE INDEX events_base_event_id_version_idx ON events(base_event_id);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS events;');
    }
}
