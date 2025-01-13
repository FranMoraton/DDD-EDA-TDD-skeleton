<?php

namespace App\Users\Domain\Model\User\Event;

use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;
use App\Users\Domain\Model\User\ValueObject\Email;
use App\Users\Domain\Model\User\ValueObject\Id;
use App\Users\Domain\Model\User\ValueObject\Role;

class UserWasCreated extends DomainEvent
{
    private const string NAME = 'company.app.1.domain_event.user.was_created';

    private string $email;
    private string $role;

    public static function from(
        Id $aggregateId,
        Email $email,
        Role $role
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            $aggregateId,
            DateTimeValueObject::now(),
            self::buildPayload(
                $email,
                $role
            ),
        );
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();
        $this->email = $payload['email'];
        $this->role = $payload['role'];
    }

    private static function buildPayload(
        Email $email,
        Role $role
    ): array {
        return [
            'email' => $email->value(),
            'role' => $role->value,
        ];
    }

    public static function messageName(): string
    {
        return self::NAME;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function role(): string
    {
        return $this->role;
    }
}
