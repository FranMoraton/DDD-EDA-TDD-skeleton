<?php

namespace App\Tests\Users\Application\Command\Users\Create;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\AlreadyExistException;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use App\Tests\Users\Domain\Model\User\RandomUserGenerator;
use App\Users\Application\Command\Users\Create\CreateUserCommandHandler;
use App\Users\Domain\Model\User\Event\UserWasCreated;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use PHPUnit\Framework\TestCase;

class CreateUserCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private UserRepository $userRepository;
    private DomainEventPublisher $domainEventPublisher;

    public function testGivenCreateWhenUserExistsThenFail(): void
    {
        $command = RandomCreateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomUserGenerator::execute(),
            );

        $this->userRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new CreateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        self::expectException(AlreadyExistException::class);
        $handler($command);
    }

    public function testGivenCreateWhenEmailInUseThenSuccess(): void
    {
        $command = RandomCreateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('search')
            ->willReturn([RandomUserGenerator::execute()]);

        $this->userRepository
            ->expects(self::never())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');

        $handler = new CreateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        self::expectException(AlreadyExistException::class);
        $handler($command);
    }

    public function testGivenCreateCommandWhenUserDoesNotExistThenSuccess(): void
    {
        $command = RandomCreateUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->userRepository
            ->expects(self::once())
            ->method('add');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($user),
            );

        $handler = new CreateUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );

        $handler($command);

        self::assertEquals($user::modelName(), User::modelName());
        self::assertEquals($user->id(), $command->id());
        self::assertEquals($user->role()->value, $command->role());
        self::assertEquals($user->email(), $command->email());
        $firstEvent = $user->events()[array_key_first($user->events())];
        self::assertEquals($firstEvent::messageName(), UserWasCreated::messageName());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);
    }
}
