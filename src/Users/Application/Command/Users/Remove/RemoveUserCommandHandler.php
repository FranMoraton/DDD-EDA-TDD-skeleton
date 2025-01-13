<?php

namespace App\Users\Application\Command\Users\Remove;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Id;

final readonly class RemoveUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(RemoveUserCommand $command): void
    {
        $user = $this->userRepository->byId(Id::from($command->id()));

        if (null === $user) {
            throw new NotFoundException(User::modelName(), User::modelName(), ['id' => $user]);
        }

        $user->remove();

        $this->userRepository->remove($user);

        $this->domainEventPublisher->execute($user);
    }
}
