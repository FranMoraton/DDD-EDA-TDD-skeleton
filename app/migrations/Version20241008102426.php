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
            VALUES (1, \'MORDOR\', \'Mordor es un país situado al sureste de la Tierra Media, que tuvo gran importancia durante la Guerra del Anillo por ser el lugar donde Sauron, el Señor Oscuro, decidió edificar su fortaleza de Barad-dûr para intentar atacar y dominar a todos los pueblos de la Tierra Media.\');
        ');

        $this->addSql('
            INSERT INTO equipments (id, name, type, made_by)
            VALUES (1, \'Maza de Sauron\', \'arma\', \'desconocido\');
        ');

        $this->addSql('
            INSERT INTO characters (id, name, birth_date, kingdom, equipment_id, faction_id)
            VALUES (1, \'SAURON\', \'3019-03-25\', \'AINUR\', 1, 1);
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('delete from factions where id = 1;');
        $this->addSql('delete from equipments where id = 1;');
        $this->addSql('delete from characters where id = 1;');
    }
}
