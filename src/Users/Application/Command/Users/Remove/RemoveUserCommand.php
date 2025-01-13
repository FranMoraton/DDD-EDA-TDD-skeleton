<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Remove;

use App\System\Application\Command;

final readonly class RemoveUserCommand implements Command
{
    private const string NAME = 'company.users.1.command.user.remove';

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
