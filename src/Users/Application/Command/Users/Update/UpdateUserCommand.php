<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Update;

use App\System\Application\Command;

final readonly class UpdateUserCommand implements Command
{
    private const string NAME = 'company.users.1.command.user.update';

    public function __construct(
        private string $id,
        private string $email,
        private string $role,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function role(): string
    {
        return $this->role;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
