<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\ValueObject;

enum Role: string implements \JsonSerializable
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
