<?php

namespace App\Marketplace\Domain\Model\Event\Event;

use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\Marketplace\Domain\Model\Event\ValueObject\OrganizerCompanyId;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
use App\Marketplace\Domain\Model\Event\ValueObject\Title;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;

class EventWasUpdated extends DomainEvent
{
    private const string NAME = 'company.marketplace.1.domain_event.event.was_updated';

    private int $baseEventId;
    private string $sellMode;
    private string $title;
    private string $eventStartDate;
    private string $eventEndDate;
    private int $eventId;
    private string $sellFrom;
    private string $sellTo;
    private bool $soldOut;
    private array $zones;
    private string $requestTime;
    private ?string $organizerCompanyId;
    private float $minPrice;
    private float $maxPrice;

    public static function from(
        Id $aggregateId,
        int $baseEventId,
        SellMode $sellMode,
        Title $title,
        DateTimeValueObject $eventStartDate,
        DateTimeValueObject $eventEndDate,
        int $eventId,
        DateTimeValueObject $sellFrom,
        DateTimeValueObject $sellTo,
        bool $soldOut,
        array $zones,
        DateTimeValueObject $requestTime,
        ?OrganizerCompanyId $organizerCompanyId,
        float $minPrice,
        float $maxPrice,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            [
                'base_event_id' => $baseEventId,
                'sell_mode' => $sellMode->value(),
                'title' => $title->value(),
                'event_start_date' => $eventStartDate->value(),
                'event_end_date' => $eventEndDate->value(),
                'event_id' => $eventId,
                'sell_from' => $sellFrom->value(),
                'sell_to' => $sellTo->value(),
                'sold_out' => $soldOut,
                'zones' => $zones,
                'request_time' => $requestTime->value(),
                'organizer_company_id' => $organizerCompanyId,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();

        $this->baseEventId = $payload['base_event_id'];
        $this->sellMode = $payload['sell_mode'];
        $this->title = $payload['title'];
        $this->eventStartDate = $payload['event_start_date'];
        $this->eventEndDate = $payload['event_end_date'];
        $this->eventId = $payload['event_id'];
        $this->sellFrom = $payload['sell_from'];
        $this->sellTo = $payload['sell_to'];
        $this->soldOut = $payload['sold_out'];
        $this->zones = $payload['zones'];
        $this->requestTime = $payload['request_time'];
        $this->organizerCompanyId = $payload['organizer_company_id'];
        $this->minPrice = $payload['min_price'];
        $this->maxPrice = $payload['max_price'];
    }

    public static function messageName(): string
    {
        return self::NAME;
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

    public function eventStartDate(): string
    {
        return $this->eventStartDate;
    }

    public function eventEndDate(): string
    {
        return $this->eventEndDate;
    }

    public function eventId(): int
    {
        return $this->eventId;
    }

    public function sellFrom(): string
    {
        return $this->sellFrom;
    }

    public function sellTo(): string
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

    public function requestTime(): string
    {
        return $this->requestTime;
    }

    public function organizerCompanyId(): ?string
    {
        return $this->organizerCompanyId;
    }

    public function minPrice(): float
    {
        return $this->minPrice;
    }

    public function maxPrice(): float
    {
        return $this->maxPrice;
    }
}
