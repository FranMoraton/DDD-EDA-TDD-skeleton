<?php

namespace App\Users\Infrastructure\Domain\Model\User;

use App\Users\Domain\Model\User\User;

class DbalArrayUserMapper
{
    public static function map(array $item): User
    {
        return User::from(
            $item['id'],
            $item['email'],
            $item['password'],
            $item['role'],
        );
    }

    public static function toArray(User $user): array
    {
        return [
            'id' => $user->id(),
            'email' => $user->email(),
            'password' => $user->password(),
            'role' => $user->role(),
        ];
    }
}
