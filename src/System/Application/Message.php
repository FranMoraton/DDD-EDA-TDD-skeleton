<?php

declare(strict_types=1);

namespace App\System\Application;

use App\System\Domain\ValueObject\Uuid;

abstract class Message implements \JsonSerializable
{
    final protected function __construct(
        private readonly Uuid $messageId,
        private readonly array $payload,
    ) {
    }

    final public static function fromPayload(
        Uuid $messageId,
        array $payload,
    ): static {
        $event = new static($messageId, $payload);
        $event->rebuildPayload();

        return $event;
    }

    abstract public function rebuildPayload(): void;

    abstract public static function messageName(): string;

    public function messageId(): Uuid
    {
        return $this->messageId;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    final public function jsonSerialize(): array
    {
        return [
            'message_id' => $this->messageId,
            'name' => static::messageName(),
            'payload' => $this->payload,
        ];
    }
}
