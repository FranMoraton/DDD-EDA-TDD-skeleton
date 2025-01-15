<?php

namespace App\Users\Application\Command\Users\Update;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\System\Domain\Exception\NotFoundException;
use App\Users\Domain\Model\User\Criteria\ByEmailCriteria;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Email;
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

        $this->emailAlreadyInUseChecker($user, $command->email());

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

    public function emailAlreadyInUseChecker(User $user, string $email): void
    {
        if ($user->email()->equalTo($userEmail = Email::from($email))) {
            return;
        }

        $users = $this->userRepository->search(ByEmailCriteria::create($userEmail));

        if (\count($users) === 0) {
            return;
        }

        throw new AlreadyExistException(User::modelName(), User::modelName(), []);
    }
}
