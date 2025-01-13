<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241223142410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Users migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS users (
                id UUID NOT NULL,
                email VARCHAR(128) NOT NULL,
                password TEXT NOT NULL,
                role VARCHAR(128) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                version INT NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            );
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS users;');
    }
}
