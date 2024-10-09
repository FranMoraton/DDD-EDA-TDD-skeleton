<?php

declare(strict_types=1);

namespace App\Lotr\Application\Command\Characters\Remove;

use App\System\Application\Command;

final readonly class RemoveCharacterCommand implements Command
{
    private const string NAME = 'company.lotr.1.command.character.remove';

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
