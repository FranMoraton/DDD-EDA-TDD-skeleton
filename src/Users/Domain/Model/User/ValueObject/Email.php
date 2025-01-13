<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\ValueObject;

use App\System\Domain\Exception\ValidationException;
use App\System\Domain\ValueObject\StringValueObject;
use App\Users\Domain\Model\User\User;

final class Email extends StringValueObject
{
    public static function from(string $value): static
    {
        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(
                User::modelName(),
                'Email',
                ['validation' => 'Invalid email address.'],
            );
        }

        return parent::from($value);
    }
}
