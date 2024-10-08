<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Factions\Create;

use App\System\Application\Command;

final class CreateFactionCommand implements Command
{
    public static function messageName(): string
    {
        return 'company.lotr.1.command.faction.create';
    }
}
