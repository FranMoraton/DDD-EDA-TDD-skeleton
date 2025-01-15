<?php

declare(strict_types=1);

namespace App\Users\Application\Command\Users\Remove;

use App\System\Application\Command;
use App\System\Domain\ValueObject\Uuid;

final class RemoveUserCommand extends Command
{
    private const string NAME = 'company.users.1.command.user.remove';

    private string $id;

    public static function create(string $id): self
    {
        return self::fromPayload(
            Uuid::v4(),
            [
                'id' => $id,
            ],
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();

        $this->id = $payload['id'];
    }
}
