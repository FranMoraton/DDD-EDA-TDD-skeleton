<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Create;

use App\System\Application\Command;

final readonly class CreateUserCommand implements Command
{
    private const string NAME = 'company.users.1.command.user.create';

    public function __construct(
        private string $id,
        private string $email,
        private string $role,
        #[\SensitiveParameter] private string $password,
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

    public function password(): string
    {
        return $this->password;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }
}
