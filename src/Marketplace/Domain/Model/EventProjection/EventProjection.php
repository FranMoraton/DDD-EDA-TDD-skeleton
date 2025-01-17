<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\EventProjection;

use App\System\Domain\Model\Aggregate;

final class EventProjection extends Aggregate
{
    private const string NAME = 'event_projection';

    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly string $startDate,
        private readonly string $startTime,
        private readonly string $endDate,
        private readonly string $endTime,
        private readonly float $minPrice,
        private readonly float $maxPrice,
        private readonly string $startsAt,
        private readonly string $endsAt,
        private readonly string $lastEventDate
    ) {
        parent::__construct();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function startDate(): string
    {
        return $this->startDate;
    }

    public function startTime(): string
    {
        return $this->startTime;
    }

    public function endDate(): string
    {
        return $this->endDate;
    }

    public function endTime(): string
    {
        return $this->endTime;
    }

    public function minPrice(): float
    {
        return $this->minPrice;
    }

    public function maxPrice(): float
    {
        return $this->maxPrice;
    }

    public function startsAt(): string
    {
        return $this->startsAt;
    }

    public function endsAt(): string
    {
        return $this->endsAt;
    }

    public function lastEventDate(): string
    {
        return $this->lastEventDate;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => $this->startDate,
            'start_time' => $this->startTime,
            'end_date' => $this->endDate,
            'end_time' => $this->endTime,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
        ];
    }

    public static function modelName(): string
    {
        return self::NAME;
    }
}
