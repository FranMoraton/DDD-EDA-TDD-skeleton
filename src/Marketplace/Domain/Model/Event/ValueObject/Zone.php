<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event\ValueObject;

final class Zone implements \JsonSerializable
{
    private function __construct(
        public int $zoneId,
        public int $capacity,
        public float $price,
        public string $name,
        public bool $numbered,
    ) {
    }

    public static function from(
        int $zoneId,
        int $capacity,
        float $price,
        string $name,
        bool $numbered,
    ): self {
        return new self(
            $zoneId,
            $capacity,
            $price,
            $name,
            $numbered,
        );
    }

    public static function fromArray(array $data): self
    {
        \assert(array_key_exists('zone_id', $data));
        \assert(array_key_exists('capacity', $data));
        \assert(array_key_exists('price', $data));
        \assert(array_key_exists('name', $data));
        \assert(array_key_exists('numbered', $data));

        return new self(
            (int) $data['zone_id'],
            (int) $data['capacity'],
            (float) $data['price'],
            $data['name'],
            filter_var($data['numbered'], FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function zoneId(): int
    {
        return $this->zoneId;
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function numbered(): bool
    {
        return $this->numbered;
    }

    public function jsonSerialize(): array
    {
        return [
            'zone_id' => $this->zoneId,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'name' => $this->name,
            'numbered' => $this->numbered,
        ];
    }
}
