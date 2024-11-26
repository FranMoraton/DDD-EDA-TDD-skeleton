<?php

declare(strict_types=1);

namespace App\System\Domain\ValueObject;

use JsonSerializable;
use Stringable;

class DateTimeValueObject extends \DateTimeImmutable implements JsonSerializable, Stringable
{
    private const string TIME_ZONE = 'UTC';
    private const string TIME_FORMAT = 'Y-m-d\TH:i:s.uP';
    private const string DATE_FORMAT = 'Y-m-d';

    final private function __construct(string $time, \DateTimeZone $timezone)
    {
        parent::__construct($time, $timezone);
    }

    final public static function from(string $str): static
    {
        return new static($str, new \DateTimeZone(self::TIME_ZONE));
    }

    final public static function now(): static
    {
        return static::from('now');
    }

    final public static function fromFormat(string $format, string $str): static
    {
        $dateTime = \DateTimeImmutable::createFromFormat($format, $str, new \DateTimeZone(self::TIME_ZONE));

        \assert($dateTime instanceof \DateTimeImmutable);

        return static::from($dateTime->format(self::TIME_FORMAT));
    }

    final public static function fromTimestamp(int $timestamp): static
    {
        return self::fromFormat('U', (string) $timestamp);
    }

    final public function jsonSerialize(): string
    {
        return $this->value();
    }

    final public function value(): string
    {
        return $this->format(\DATE_ATOM);
    }

    public function __toString(): string
    {
        return $this->format(\DATE_ATOM);
    }

    final public function equalTo(DateTimeValueObject $other): bool
    {
        return static::class === \get_class($other) && $this->value() === $other->value();
    }

    public function toDate(): string
    {
        return $this->format(self::DATE_FORMAT);
    }
}
