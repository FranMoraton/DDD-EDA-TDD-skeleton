<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;
use App\Marketplace\Domain\Model\Event\Event\EventWasUpdated;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\Marketplace\Domain\Model\Event\ValueObject\OrganizerCompanyId;
use App\Marketplace\Domain\Model\Event\ValueObject\SellMode;
use App\Marketplace\Domain\Model\Event\ValueObject\Title;
use App\Marketplace\Domain\Model\Event\ValueObject\Zones;
use App\System\Domain\Model\Aggregate;
use App\System\Domain\Service\JsonSerializer;
use App\System\Domain\ValueObject\DateTimeValueObject;

class Event extends Aggregate
{
    private const string NAME = 'event';

    private function __construct(
        private readonly Id $id,
        private readonly int $baseEventId,
        private readonly SellMode $sellMode,
        private readonly Title $title,
        private readonly DateTimeValueObject $eventStartDate,
        private readonly DateTimeValueObject $eventEndDate,
        private readonly int $eventId,
        private readonly DateTimeValueObject $sellFrom,
        private readonly DateTimeValueObject $sellTo,
        private readonly bool $soldOut,
        private readonly Zones $zones,
        private readonly DateTimeValueObject $requestTime,
        private readonly ?OrganizerCompanyId $organizerCompanyId,
        private readonly float $minPrice,
        private readonly float $maxPrice,
    ) {
        parent::__construct();
    }

    public static function from(
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
        string $requestTime,
        ?string $organizerCompanyId,
        float $minPrice,
        float $maxPrice,
    ): self {
        return new self(
            Id::from($id),
            $baseEventId,
            SellMode::from($sellMode),
            Title::from($title),
            DateTimeValueObject::from($eventStartDate),
            DateTimeValueObject::from($eventEndDate),
            $eventId,
            DateTimeValueObject::from($sellFrom),
            DateTimeValueObject::from($sellTo),
            $soldOut,
            Zones::fromArray($zones),
            DateTimeValueObject::from($requestTime),
            null !== $organizerCompanyId ? OrganizerCompanyId::from($organizerCompanyId) : null,
            $minPrice,
            $maxPrice,
        );
    }

    public static function create(
        string $id,
        int $baseEventId,
        string $sellMode,
        string $title,
        DateTimeValueObject $eventStartDate,
        DateTimeValueObject $eventEndDate,
        int $eventId,
        DateTimeValueObject $sellFrom,
        DateTimeValueObject $sellTo,
        bool $soldOut,
        array $zones,
        DateTimeValueObject $requestTime,
        ?string $organizerCompanyId
    ): self {
        $transformedZones = Zones::fromArray($zones);
        [$minPrice, $maxPrice] = self::calculateMinMaxPrices($transformedZones);

        $event = new self(
            $idVo = Id::from($id),
            $baseEventId,
            $sellModeVo = SellMode::from($sellMode),
            $titleVo = Title::from($title),
            $eventStartDate,
            $eventEndDate,
            $eventId,
            $sellFrom,
            $sellTo,
            $soldOut,
            $transformedZones,
            $requestTime,
            $organizerCompanyIdVo = $organizerCompanyId ? OrganizerCompanyId::from($organizerCompanyId) : null,
            $minPrice,
            $maxPrice,
        );

        $event->recordThat(
            EventWasCreated::from(
                $idVo,
                $baseEventId,
                $sellModeVo,
                $titleVo,
                $eventStartDate,
                $eventEndDate,
                $eventId,
                $sellFrom,
                $sellTo,
                $soldOut,
                $transformedZones,
                $requestTime,
                $organizerCompanyIdVo,
                $minPrice,
                $maxPrice,
            )
        );

        return $event;
    }

    public function update(
        string $sellMode,
        string $title,
        DateTimeValueObject $eventStartDate,
        DateTimeValueObject $eventEndDate,
        int $eventId,
        DateTimeValueObject $sellFrom,
        DateTimeValueObject $sellTo,
        bool $soldOut,
        array $zones,
        DateTimeValueObject $requestTime,
        ?string $organizerCompanyId
    ): self {
        $currentRequestTime = $this->requestTime;

        if ($currentRequestTime > $requestTime) {
            return $this;
        }

        $sellModeVo = SellMode::from($sellMode);
        $titleVo = Title::from($title);

        $transformedZones = Zones::fromArray($zones);
        [$minPrice, $maxPrice] = self::calculateMinMaxPrices($transformedZones);

        $organizerCompanyIdVo = $organizerCompanyId ? OrganizerCompanyId::from($organizerCompanyId) : null;

        if (
            $this->eventId === $eventId &&
            $this->soldOut === $soldOut &&
            $this->sellMode->equalTo($sellModeVo) &&
            $this->title->equalTo($titleVo) &&
            $this->eventStartDate->equalTo($eventStartDate) &&
            $this->eventEndDate->equalTo($eventEndDate) &&
            $this->sellFrom->equalTo($sellFrom) &&
            $this->sellTo->equalTo($sellTo) &&
            JsonSerializer::encode($this->zones) === JsonSerializer::encode($transformedZones) &&
            $organizerCompanyIdVo?->value() === $this->organizerCompanyId?->value()
        ) {
            return $this;
        }

        $event = new self(
            $this->id,
            $this->baseEventId,
            $sellModeVo,
            $titleVo,
            $eventStartDate,
            $eventEndDate,
            $eventId,
            $sellFrom,
            $sellTo,
            $soldOut,
            $transformedZones,
            $requestTime,
            $organizerCompanyIdVo,
            $minPrice,
            $maxPrice,
        );

        $event->recordThat(
            EventWasUpdated::from(
                $this->id,
                $this->baseEventId,
                $sellModeVo,
                $titleVo,
                $eventStartDate,
                $eventEndDate,
                $eventId,
                $sellFrom,
                $sellTo,
                $soldOut,
                $transformedZones,
                $requestTime,
                $organizerCompanyIdVo,
                $minPrice,
                $maxPrice,
            )
        );

        return $event;
    }

    private static function calculateMinMaxPrices(Zones $zones): array
    {
        $minPrice = null;
        $maxPrice = null;

        foreach ($zones as $zone) {
            $price = $zone->price();

            if ($minPrice === null || $price < $minPrice) {
                $minPrice = $price;
            }

            if ($maxPrice === null || $price > $maxPrice) {
                $maxPrice = $price;
            }
        }

        return [$minPrice ?? 0.0, $maxPrice ?? 0.0];
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'base_event_id' => $this->baseEventId,
            'sell_mode' => $this->sellMode,
            'title' => $this->title,
            'event_start_date' => $this->eventStartDate,
            'event_end_date' => $this->eventEndDate,
            'event_id' => $this->eventId,
            'sell_from' => $this->sellFrom,
            'sell_to' => $this->sellTo,
            'sold_out' => $this->soldOut,
            'request_time' => $this->requestTime,
            'organizer_company_id' => $this->organizerCompanyId,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'zones' => $this->zones,
        ];
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function baseEventId(): int
    {
        return $this->baseEventId;
    }

    public function sellMode(): SellMode
    {
        return $this->sellMode;
    }

    public function title(): Title
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

    public function zones(): Zones
    {
        return $this->zones;
    }

    public function requestTime(): DateTimeValueObject
    {
        return $this->requestTime;
    }

    public function organizerCompanyId(): ?OrganizerCompanyId
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
