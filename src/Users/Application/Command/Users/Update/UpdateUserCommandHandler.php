<?php

namespace App\Users\Application\Command\Users\Update;

use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Equipment\Equipment;
use App\Lotr\Domain\Model\Equipment\EquipmentRepository;
use App\Lotr\Domain\Model\Equipment\ValueObject\Id as EquipmentId;
use App\Lotr\Domain\Model\Faction\FactionRepository;
use App\Lotr\Domain\Model\Faction\ValueObject\Id as FactionId;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Id;

final readonly class UpdateUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(UpdateUserCommand $command): void
    {
        $user = $this->userFinder($command);

        $user = $user->update(
            $command->email(),
            $command->role(),
        );

        $this->userRepository->update($user);

        $this->domainEventPublisher->execute($user);
    }

    public function userFinder(UpdateUserCommand $command): User
    {
        $user = $this->userRepository->byId(Id::from($command->id()));

        if (null === $user) {
            throw new NotFoundException(User::modelName(), User::modelName(), ['id' => $command->id()]);
        }

        return $user;
    }
}
