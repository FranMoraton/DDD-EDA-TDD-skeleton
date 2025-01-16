<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Create;

use App\System\Application\Command;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class CreateUserCommand extends Command
{
    private const string NAME = 'company.users.1.command.user.create';

    private string $id;
    private string $email;
    private string $role;
    private string $password;

    public static function create(
        string $id,
        string $email,
        string $role,
        #[\SensitiveParameter] string $password,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            [
                'id' => $id,
                'email' => $email,
                'role' => $role,
                'password' => $password,
            ],
        );
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

    public function rebuildPayload(): void
    {
        $payload = $this->payload();

        Assert::lazy()->tryAll()
            ->that($payload, 'payload')->isArray()
            ->keyExists('id')
            ->keyExists('email')
            ->keyExists('role')
            ->keyExists('password')
            ->verifyNow();

        $this->id = $payload['id'];
        $this->email = $payload['email'];
        $this->role = $payload['role'];
        $this->password = $payload['password'];
    }
}
