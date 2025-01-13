<?php

namespace App\Users\Domain\Model\User;

use App\Users\Domain\Model\User\ValueObject\Id;

interface UserRepository
{
    public function byId(Id $id): ?User;
    public function add(User $user): void;
    public function update(User $user): void;
    public function remove(User $user): void;
}
