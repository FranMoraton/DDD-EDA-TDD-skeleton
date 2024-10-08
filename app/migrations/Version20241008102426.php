<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241008102426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fixture to be moved';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO factions (id, faction_name, description)
            VALUES (\'c3ee1a06-9941-4568-b0f4-0f7f1508a359\', \'MORDOR\', \'Mordor es un país situado al sureste de la Tierra Media, que tuvo gran importancia durante la Guerra del Anillo por ser el lugar donde Sauron, el Señor Oscuro, decidió edificar su fortaleza de Barad-dûr para intentar atacar y dominar a todos los pueblos de la Tierra Media.\');
        ');

        $this->addSql('
            INSERT INTO equipments (id, name, type, made_by)
            VALUES (\'e7de6fd0-80d4-4d81-a7f8-5f31cb8ce6dc\', \'Maza de Sauron\', \'arma\', \'desconocido\');
        ');

        $this->addSql('
            INSERT INTO characters (id, name, birth_date, kingdom, equipment_id, faction_id)
            VALUES (\'ad8b57e6-dc1f-4b8a-94d8-6f531a1a65dc\', \'SAURON\', \'3019-03-25\', \'AINUR\', \'e7de6fd0-80d4-4d81-a7f8-5f31cb8ce6dc\', \'c3ee1a06-9941-4568-b0f4-0f7f1508a359\');
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('delete from factions where id = \'c3ee1a06-9941-4568-b0f4-0f7f1508a359\';');
        $this->addSql('delete from equipments where id = \'e7de6fd0-80d4-4d81-a7f8-5f31cb8ce6dc\';');
        $this->addSql('delete from characters where id = \'ad8b57e6-dc1f-4b8a-94d8-6f531a1a65dc\';');
    }
}
