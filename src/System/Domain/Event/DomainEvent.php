<?php

declare(strict_types=1);

namespace App\System\Domain\Event;

use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;
use JsonSerializable;

abstract class DomainEvent implements JsonSerializable
{
    final protected function __construct(
        private readonly Uuid $messageId,
        private readonly Uuid $aggregateId,
        private readonly int $aggregateVersion,
        private readonly DateTimeValueObject $occurredOn,
        private readonly array $payload,
    ) {
    }

    final public static function fromPayload(
        Uuid $messageId,
        Uuid $aggregateId,
        DateTimeValueObject $occurredOn,
        array $payload,
        int $aggregateVersion = 0,
    ): static {
        $event = new static($messageId, $aggregateId, $aggregateVersion, $occurredOn, $payload);
        $event->rebuildPayload();

        return $event;
    }

    abstract public function rebuildPayload(): void;

    abstract public static function messageName(): string;

    final public function jsonSerialize(): array
    {
        return [
                'message_id' => $this->messageId,
                'name' => static::messageName(),
                'aggregate_id' => $this->aggregateId,
                'aggregate_version' => $this->aggregateVersion,
                'occurred_on' => $this->occurredOn,
                'payload' => $this->payload,
        ];
    }

    public function messageId(): Uuid
    {
        return $this->messageId;
    }

    public function aggregateId(): Uuid
    {
        return $this->aggregateId;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    public function occurredOn(): DateTimeValueObject
    {
        return $this->occurredOn;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
