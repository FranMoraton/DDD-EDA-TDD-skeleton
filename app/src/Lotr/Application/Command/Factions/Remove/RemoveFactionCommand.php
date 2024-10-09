<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Factions\Remove;

use App\System\Application\Command;

final readonly class RemoveFactionCommand implements Command
{
    public function __construct(private string $id)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public static function messageName(): string
    {
        return 'company.lotr.1.command.faction.remove';
    }
}
