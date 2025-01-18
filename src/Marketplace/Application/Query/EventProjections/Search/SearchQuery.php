<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Query\EventProjections\Search;

use App\System\Application\CacheQuery;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class SearchQuery extends CacheQuery
{
    private const string NAME = 'company.marketplace.1.query.event_projection.search';
    private const int EXPIRATION_TIME = 30;

    private DateTimeValueObject $startsAt;
    private DateTimeValueObject $endsAt;

    public static function create(mixed $startsAt, mixed $endsAt): self
    {
        return self::fromPayload(
            Uuid::v4(),
            [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
        );
    }

    public function startsAt(): DateTimeValueObject
    {
        return $this->startsAt;
    }

    public function endsAt(): DateTimeValueObject
    {
        return $this->endsAt;
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
            ->keyExists('starts_at')
            ->keyExists('ends_at')
            ->verifyNow();

        Assert::lazy()
            ->that($payload['starts_at'], 'starts_at')->date('Y-m-d\TH:i:s\Z')
            ->that($payload['ends_at'], 'ends_at')->date('Y-m-d\TH:i:s\Z')
            ->verifyNow();

        $this->startsAt = DateTimeValueObject::from($payload['starts_at']);
        $this->endsAt = DateTimeValueObject::from($payload['ends_at']);
    }

    public function expirationTime(): int
    {
        return self::EXPIRATION_TIME;
    }
}
