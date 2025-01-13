<?php

namespace App\Tests\Users\Application\Command\Users\Remove;

use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use App\Tests\Users\Domain\Model\User\RandomUserGenerator;
use App\Users\Application\Command\Users\Remove\RemoveUserCommandHandler;
use App\Users\Domain\Model\User\Event\UserWasRemoved;
use App\Users\Domain\Model\User\User;
use App\Users\Domain\Model\User\UserRepository;
use PHPUnit\Framework\TestCase;

class RemoveUserCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private UserRepository $userRepository;
    private DomainEventPublisher $domainEventPublisher;

    private RemoveUserCommandHandler $handler;

    public function testGivenRemoveWhenCharacterDoesNotExistThenSuccess(): void
    {
        $command = RandomRemoveUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomUserGenerator::execute($command->id()),
            );

        $this->userRepository
            ->expects(self::once())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($user),
            );

        ($this->handler)($command);

        self::assertEquals($user::modelName(), User::modelName());
        self::assertEquals($user->id(), $command->id());
        $firstEvent = $user->events()[array_key_first($user->events())];
        self::assertEquals($firstEvent::messageName(), UserWasRemoved::messageName());
    }

    public function testGivenRemoveCommandWhenCharacterDoesNotExistThenFail(): void
    {
        $command = RandomRemoveUserCommand::execute();

        $this->userRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->userRepository
            ->expects(self::never())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::never())
            ->method('execute');


        self::expectException(NotFoundException::class);
        ($this->handler)($command);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new RemoveUserCommandHandler(
            $this->userRepository,
            $this->domainEventPublisher,
        );
    }
}
