<?php

namespace App\Users\Domain\Model\User;

use App\System\Domain\Criteria\Criteria;
use App\Users\Domain\Model\User\ValueObject\Id;

interface UserRepository
{
    public function byId(Id $id): ?User;
    public function add(User $user): void;
    public function update(User $user): void;
    public function remove(User $user): void;
    /**
     * @param Criteria $criteria
     * @return array<User>
    */
    public function search(Criteria $criteria): array;
    public function count(Criteria $criteria): int;
}
