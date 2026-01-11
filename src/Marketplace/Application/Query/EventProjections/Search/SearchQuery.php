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

    private ?int $itemsPerPage;
    private ?int $page;
    private array $filters;

    public static function create(array $queryParams): self
    {
        return self::fromPayload(Uuid::v4(), $queryParams);
    }

    public function itemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    public function page(): ?int
    {
        return $this->page;
    }

    public function filters(): array
    {
        return $this->filters;
    }

    public function expirationTime(): int
    {
        return self::EXPIRATION_TIME;
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
            ->verifyNow();

        $itemsPerPage = $payload['items_per_page'] ?? null;
        $page = $payload['page'] ?? null;

        Assert::lazy()
            ->that($itemsPerPage, 'items_per_page')->nullOr()->integerish()->greaterThan(0)
            ->that($page, 'page')->nullOr()->integerish()->greaterThan(0)
            ->verifyNow();

        $this->itemsPerPage = null !== $itemsPerPage ? (int) $itemsPerPage : null;
        $this->page = null !== $page ? (int) $page : null;

        unset($payload['items_per_page'], $payload['page']);
        $this->filters = $payload;
    }
}
