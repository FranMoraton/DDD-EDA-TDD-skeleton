<?php

namespace App\Users\Application\Command\Users\Create;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Users\Domain\Model\User\Criteria\ByEmailCriteria;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Email;
use App\Users\Domain\Model\User\ValueObject\Id;

final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private DomainEventPublisher $domainEventPublisher,
    ) {
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $this->assertThatUserDoesNotExist($command);

        $this->emailAlreadyInUseChecker($command->email());

        $user = User::create(
            $command->id(),
            $command->email(),
            $command->password(),
            $command->role(),
        );

        $this->userRepository->add($user);

        $this->domainEventPublisher->execute($user);
    }

    public function assertThatUserDoesNotExist(CreateUserCommand $command): void
    {
        $character = $this->userRepository->byId(Id::from($command->id()));

        if (null !== $character) {
            throw new AlreadyExistException(User::modelName(), User::modelName(), ['id' => $command->id()]);
        }
    }

    public function emailAlreadyInUseChecker(string $email): void
    {
        $users = $this->userRepository->search(ByEmailCriteria::create(Email::from($email)));

        if (\count($users) === 0) {
            return;
        }

        throw new AlreadyExistException(User::modelName(), User::modelName(), []);
    }
}
