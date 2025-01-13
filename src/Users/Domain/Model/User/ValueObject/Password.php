<?php

declare(strict_types=1);

namespace App\Users\Domain\Model\User\ValueObject;

use App\System\Domain\Exception\ValidationException;
use App\System\Domain\ValueObject\StringValueObject;
use App\Users\Domain\Model\User\User;

final class Password extends StringValueObject
{
    private const string REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/';

    public static function from(#[\SensitiveParameter] string $value): static
    {
        if (false === (bool) preg_match(self::REGEX, $value)) {
            throw new ValidationException(
                User::modelName(),
                'password',
                [
                    'validation' => 'Password must be at least 8 characters long,'
                     . 'include at least one uppercase letter, one lowercase letter,'
                     . 'one number, and one special character.',
                ],
            );
        }

        return parent::from(password_hash($value, PASSWORD_ARGON2ID));
    }

    public function verify(#[\SensitiveParameter] string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->value());
    }
}
