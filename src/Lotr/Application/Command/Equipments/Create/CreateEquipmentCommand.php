<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Equipments\Create;

use App\System\Application\Command;

final readonly class CreateEquipmentCommand implements Command
{
    private const string NAME = 'company.lotr.1.command.equipment.create';

    public function __construct(
        private string $id,
        private string $name,
        private string $type,
        private string $madeBy,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function madeBy(): string
    {
        return $this->madeBy;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
