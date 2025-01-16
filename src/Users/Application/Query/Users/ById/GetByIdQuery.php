<?php

declare(strict_types=1);

namespace App\Users\Application\Query\Users\ById;

use App\System\Application\Query;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class GetByIdQuery extends Query
{
    private const string NAME = 'company.users.1.query.user.by_id';

    private string $id;

    public static function create(string $id): self
    {
        return self::fromPayload(
            Uuid::v4(),
            ['id' => $id],
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

        Assert::lazy()->tryAll()
            ->that($payload, 'payload')->isArray()
            ->keyExists('id')
            ->verifyNow();

        $this->id = $payload['id'];
    }
}
