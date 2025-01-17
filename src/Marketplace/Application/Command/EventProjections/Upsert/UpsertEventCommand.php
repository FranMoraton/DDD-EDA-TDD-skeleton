<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Command\EventProjections\Upsert;

use App\System\Application\Command;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class UpsertEventCommand extends Command
{
    private const string NAME = 'company.marketplace.1.command.event_projection.upsert';

    private string $id;
    private int $baseEventId;
    private string $sellMode;
    private string $title;
    private DateTimeValueObject $eventStartDate;
    private DateTimeValueObject $eventEndDate;
    private int $eventId;
    private DateTimeValueObject $sellFrom;
    private DateTimeValueObject $sellTo;
    private bool $soldOut;
    private array $zones;
    private DateTimeValueObject $lastEventDate;
    private ?string $organizerCompanyId;

    public static function create(
        string $id,
        int $baseEventId,
        string $sellMode,
        string $title,
        string $eventStartDate,
        string $eventEndDate,
        int $eventId,
        string $sellFrom,
        string $sellTo,
        bool $soldOut,
        array $zones,
        string $lastEventDate,
        ?string $organizerCompanyId
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            [
                'id' => $id,
                'base_event_id' => $baseEventId,
                'sell_mode' => $sellMode,
                'title' => $title,
                'event_start_date' => $eventStartDate,
                'event_end_date' => $eventEndDate,
                'event_id' => $eventId,
                'sell_from' => $sellFrom,
                'sell_to' => $sellTo,
                'sold_out' => $soldOut,
                'zones' => $zones,
                'last_event_date' => $lastEventDate,
                'organizer_company_id' => $organizerCompanyId,
            ],
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function baseEventId(): int
    {
        return $this->baseEventId;
    }

    public function sellMode(): string
    {
        return $this->sellMode;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function eventStartDate(): DateTimeValueObject
    {
        return $this->eventStartDate;
    }

    public function eventEndDate(): DateTimeValueObject
    {
        return $this->eventEndDate;
    }

    public function eventId(): int
    {
        return $this->eventId;
    }

    public function sellFrom(): DateTimeValueObject
    {
        return $this->sellFrom;
    }

    public function sellTo(): DateTimeValueObject
    {
        return $this->sellTo;
    }

    public function soldOut(): bool
    {
        return $this->soldOut;
    }

    public function zones(): array
    {
        return $this->zones;
    }

    public function lastEventDate(): DateTimeValueObject
    {
        return $this->lastEventDate;
    }

    public function organizerCompanyId(): ?string
    {
        return $this->organizerCompanyId;
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
            ->keyExists('base_event_id')
            ->keyExists('sell_mode')
            ->keyExists('title')
            ->keyExists('event_start_date')
            ->keyExists('event_end_date')
            ->keyExists('event_id')
            ->keyExists('sell_from')
            ->keyExists('sell_to')
            ->keyExists('sold_out')
            ->keyExists('zones')
            ->keyExists('last_event_date')
            ->verifyNow();

        foreach ($payload['zones'] as $zone) {
            Assert::lazy()->tryAll()
                ->that($zone, 'zone')->isArray()
                ->keyExists('zone_id')
                ->keyExists('capacity')
                ->keyExists('price')
                ->keyExists('name')
                ->keyExists('numbered')
                ->verifyNow();
        }

        $this->id = $payload['id'];
        $this->baseEventId = (int) $payload['base_event_id'];
        $this->sellMode = $payload['sell_mode'];
        $this->title = $payload['title'];
        $this->eventStartDate = DateTimeValueObject::from($payload['event_start_date']);
        $this->eventEndDate = DateTimeValueObject::from($payload['event_end_date']);
        $this->eventId = (int) $payload['event_id'];
        $this->sellFrom = DateTimeValueObject::from($payload['sell_from']);
        $this->sellTo = DateTimeValueObject::from($payload['sell_to']);
        $this->soldOut = (bool) $payload['sold_out'];
        $this->zones = $payload['zones'];
        $this->lastEventDate = DateTimeValueObject::from($payload['last_event_date']);
        $this->organizerCompanyId = $payload['organizer_company_id'] ?? null;
    }
}
