<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117210524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Events projection migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE events_projection (
                id UUID,
                title VARCHAR(255) NOT NULL,
                start_date DATE NOT NULL,
                start_time TIME NOT NULL,
                end_date DATE NOT NULL,
                end_time TIME NOT NULL,
                min_price DECIMAL(10, 2) NOT NULL,
                max_price DECIMAL(10, 2) NOT NULL,
                starts_at TIMESTAMP NOT NULL,
                ends_at TIMESTAMP NOT NULL,
                last_event_date TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            );
        ");

        $this->addSql("CREATE INDEX events_projection_starts_at_idx ON events_projection (starts_at);");
        $this->addSql("CREATE INDEX events_projection_ends_at_time_idx ON events_projection (ends_at);");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS events_projection;');
    }
}
