<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\ValueObject;

enum Role: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
}
