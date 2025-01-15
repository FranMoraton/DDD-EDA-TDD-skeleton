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

        $this->addSql("CREATE INDEX users_id_version_idx ON users(id,version);");
        $this->addSql("CREATE INDEX users_external_reference_idx ON users(email);");
        $this->addSql("CREATE INDEX users_role ON users(role)");

        $this->addSql("
            insert into users (id, email, password, role, created_at, updated_at, version)
            values  (
                     '01ceb46e-96d8-41b6-972a-9b2403d36bd7',
                     'test@test.com',
                     '\$argon2id\$v=19\$m=65536,t=4,p=1\$Ly5RdWl6Lk1OWEw4YzFpQg\$mJRVLsPIzJv3MndN5U0ju7zPgRAluNq/wyL/vn9xcTQ',
                     'ROLE_ADMIN',
                     '2025-01-15 16:16:32.194025',
                     '2025-01-15 16:16:32.194025',
                     1
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS users;');
    }
}
