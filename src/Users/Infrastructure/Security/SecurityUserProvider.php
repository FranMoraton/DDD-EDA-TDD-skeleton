<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Security;

use App\System\Domain\Criteria\Criteria;
use App\Users\Domain\Model\User\Criteria\ByEmailCriteria;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Email;
use App\Users\Domain\Model\User\ValueObject\Id;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class SecurityUserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function refreshUser(UserInterface $securityUser): SecurityUser
    {
        if (!$securityUser instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($securityUser)));
        }

        $user = $this->findUser($securityUser->getUserIdentifier());

        return SecurityUser::from(
            $user->id()->value(),
            $user->email()->value(),
            $user->password()->value(),
            [$user->role()->value],
        );
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): SecurityUser
    {
        $user = $this->findUser($identifier);

        return SecurityUser::from(
            $user->id()->value(),
            $user->email()->value(),
            $user->password()->value(),
            [$user->role()->value],
        );
    }

    private function findUser(string $identifier): User
    {
        $users = $this->userRepository->search(
            ByEmailCriteria::create(Email::from($identifier))
        );


        if (\count($users) === 0) {
            throw new UserNotFoundException();
        }

        return $users[array_key_first($users)];
    }
}
