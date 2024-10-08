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
            CREATE TABLE IF NOT EXISTS `factions` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `faction_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `equipments` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                `type` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                `made_by` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS `characters` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                `birth_date` DATE NOT NULL,
                `kingdom` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                `equipment_id` INT NOT NULL,
                `faction_id` INT NOT NULL,
                PRIMARY KEY (`id`),
                KEY `equipment_id` (`equipment_id`),
                KEY `faction_id` (`faction_id`),
                CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipments` (`id`),
                CONSTRAINT `characters_ibfk_2` FOREIGN KEY (`faction_id`) REFERENCES `factions` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS `factions`;');
        $this->addSql('DROP TABLE IF EXISTS `equipments`;');
        $this->addSql('DROP TABLE IF EXISTS `characters`;');
    }
}
