<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Equipments\Remove;

use App\System\Application\Command;

final readonly class RemoveEquipmentCommand implements Command
{
    private const string NAME = 'company.lotr.1.command.equipment.remove';

    public function __construct(private string $id)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
