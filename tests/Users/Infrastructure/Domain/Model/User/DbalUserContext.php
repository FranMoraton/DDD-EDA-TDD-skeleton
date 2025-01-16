<?php

declare(strict_types=1);

namespace App\Tests\Users\Infrastructure\Domain\Model\User;

use App\Tests\System\Infrastructure\Behat\BehatContext;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Infrastructure\Domain\Model\User\DbalArrayUserMapper;
use Behat\Gherkin\Node\PyStringNode;

final readonly class DbalUserContext extends BehatContext
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /** @Given these Users exist */
    public function theseUsersExist(PyStringNode $payload): void
    {
        foreach ($this->stringNodeToArray($payload) as $user) {
            $this->userRepository->add(DbalArrayUserMapper::map($user));
        }
    }
}
