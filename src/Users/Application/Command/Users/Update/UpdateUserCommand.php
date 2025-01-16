<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Update;

use App\System\Application\Command;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class UpdateUserCommand extends Command
{
    private const string NAME = 'company.users.1.command.user.update';

    private string $id;
    private string $email;
    private string $role;

    public static function create(string $id, string $email, string $role): self
    {
        return self::fromPayload(
            Uuid::v4(),
            [
                'id' => $id,
                'email' => $email,
                'role' => $role,
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
            ->verifyNow();

        $this->id = $payload['id'];
        $this->email = $payload['email'];
        $this->role = $payload['role'];
    }
}
