<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008090627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Event Storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS event_store (
                message_id UUID NOT NULL PRIMARY KEY,
                aggregate_id UUID NOT NULL,
                message_name VARCHAR(255) NOT NULL,
                payload JSONB not null,
                occurred_on DECIMAL(18, 6) NOT NULL,
                aggregate_version VARCHAR(60) NOT NULL
            );
        ');

        $this->addSql('CREATE INDEX event_store_aggregate_id_idx ON event_store(aggregate_id);');
        $this->addSql('CREATE INDEX event_store_message_name_idx ON event_store(message_name);');
        $this->addSql('CREATE INDEX event_store_occurred_on_idx ON event_store(occurred_on);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop table event_store;');
    }
}
