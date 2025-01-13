<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Domain\Model\User;

use App\System\Infrastructure\Dbal\DbalRepository;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Id;

class DbalUserRepository extends DbalRepository implements UserRepository
{
    private const string TABLE_NAME = 'users';

    public function byId(Id $id): ?User
    {
        $result = $this->findOnyByIdentification(
            self::TABLE_NAME,
            $id->value(),
            'id'
        );

        return null !== $result
            ? $this->map($result)
            : null;
    }

    public function add(User $user): void
    {
        $this->executeInsert(DbalArrayUserMapper::toArray($user));
    }

    public function update(User $user): void
    {
        $this->executeUpdate(
            $user,
            DbalArrayUserMapper::toArray($user),
            [
                'id' => $user->id(),
            ],
        );
    }

    public function remove(User $user): void
    {
        $this->executeDelete(['id' => $user->id()]);
    }

    protected static function tableName(): string
    {
        return self::TABLE_NAME;
    }

    protected function map(array $item): User
    {
        $model = $this->addControlFields($item, DbalArrayUserMapper::map($item));
        \assert($model instanceof User);

        return $model;
    }
}
