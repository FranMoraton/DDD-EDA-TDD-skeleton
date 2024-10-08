<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Factions\Create;

use App\System\Application\Command;

final readonly class CreateFactionCommand implements Command
{
    public function __construct(private string $id, private string $name, private string $description)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public static function messageName(): string
    {
        return 'company.lotr.1.command.faction.create';
    }
}
