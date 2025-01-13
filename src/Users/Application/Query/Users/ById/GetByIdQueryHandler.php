<?php

declare(strict_types=1);

namespace App\Users\Application\Query\Users\ById;

use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Id;

final readonly class GetByIdQueryHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(GetByIdQuery $query): ?User
    {
        return $this->userRepository->byId(Id::from($query->id()));
    }
}
