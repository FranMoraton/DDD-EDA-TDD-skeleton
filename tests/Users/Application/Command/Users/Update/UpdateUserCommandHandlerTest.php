<?php

namespace App\Tests\Users\Application\Command\Users\Update;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use App\Tests\Users\Domain\Model\User\RandomUserGenerator;
use App\Users\Application\Command\Users\Update\UpdateUserCommandHandler;
use App\Users\Domain\Model\User\Event\UserWasUpdated;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use App\Users\Domain\Model\User\ValueObject\Role;
use PHPUnit\Framework\TestCase;

class UpdateUserCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private UserRepository $userRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenUpdateWhenUserDoesNotExistThenFail(): void
    {
        $command = RandomUpdateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->userRepository
            ->expects(self::never())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new UpdateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        self::expectException(NotFoundException::class);
        $handler($command);
    }

    public function testGivenUpdateCommandWhenUserAlreadyUpdatedThenDoNotUpdate(): void
    {
        $command = RandomUpdateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldUser = RandomUserGenerator::execute(
                    id: $command->id(),
                    email: $command->email(),
                    role: $command->role(),
                ),
            );

        $this->userRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($user),
            );

        $handler = new UpdateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($user::modelName(), User::modelName());
        self::assertEquals($user->id(), $command->id());
        self::assertEquals($user->email(), $command->email());
        self::assertEquals($user->role()->value, $command->role());

        self::assertCount(0, $user->events());
    }

    public function testGivenUpdateCommandWhenUserEmailIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldUser = RandomUserGenerator::execute(
                    id: $command->id(),
                    role: $command->role(),
                ),
            );

        $this->userRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($user),
            );

        $handler = new UpdateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($user::modelName(), User::modelName());
        self::assertEquals($user->id(), $command->id());
        self::assertEquals($user->role()->value, $command->role());
        self::assertEquals($user->email(), $command->email());
        self::assertNotEquals($user->email(), $oldUser->email());

        $firstEvent = $user->events()[array_key_first($user->events())];
        self::assertEquals($firstEvent::messageName(), UserWasUpdated::messageName());
    }

    public function testGivenUpdateCommandWhenUserRoleIsDifferentThenUpdate(): void
    {
        $command = RandomUpdateUserCommand::execute(
            role: Role::USER->value,
        );

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                $oldUser = RandomUserGenerator::execute(
                    id: $command->id(),
                    email: $command->email(),
                    role: Role::ADMIN->value
                ),
            );

        $this->userRepository
            ->expects(self::once())
            ->method('update');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($user),
            );

        $handler = new UpdateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($user::modelName(), User::modelName());
        self::assertEquals($user->id(), $command->id());
        self::assertEquals($user->email(), $command->email());
        self::assertEquals($user->role()->value, $command->role());
        self::assertNotEquals($user->role()->value, $oldUser->role()->value);

        $firstEvent = $user->events()[array_key_first($user->events())];
        self::assertEquals($firstEvent::messageName(), UserWasUpdated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
