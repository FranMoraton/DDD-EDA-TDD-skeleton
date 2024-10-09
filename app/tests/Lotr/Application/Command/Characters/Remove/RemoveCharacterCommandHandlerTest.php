<?php

namespace App\Tests\Lotr\Application\Command\Characters\Remove;

use App\Lotr\Application\Command\Characters\Remove\RemoveCharacterCommandHandler;
use App\Lotr\Domain\Model\Character\Character;
use App\Lotr\Domain\Model\Character\CharacterRepository;
use App\Lotr\Domain\Model\Character\Event\CharacterWasRemoved;
use App\System\Application\DomainEventPublisher;
use App\System\Domain\Exception\NotFoundException;
use App\Tests\Lotr\Domain\Model\Character\RandomCharacterGenerator;
use App\Tests\System\Infrastructure\PhpUnit\SpyTestHelper;
use PHPUnit\Framework\TestCase;

class RemoveCharacterCommandHandlerTest extends TestCase
{
    use SpyTestHelper;

    private CharacterRepository $characterRepository;
    private DomainEventPublisher $domainEventPublisher;

    private RemoveCharacterCommandHandler $handler;

    public function testGivenRemoveWhenCharacterDoesNotExistThenSuccess(): void
    {
        $command = RandomRemoveCharacterCommand::execute();

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(
                RandomCharacterGenerator::execute($command->id()),
            );

        $this->characterRepository
            ->expects(self::once())
            ->method('remove');

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('execute')
            ->will(
                self::extractArguments($character),
            );

        ($this->handler)($command);

        self::assertEquals($character::modelName(), Character::modelName());
        self::assertEquals($character->id(), $command->id());
        $firstEvent = $character->events()[array_key_first($character->events())];
        self::assertEquals($firstEvent::messageName(), CharacterWasRemoved::messageName());
    }

    public function testGivenRemoveCommandWhenCharacterDoesNotExistThenFail(): void
    {
        $command = RandomRemoveCharacterCommand::execute();

        $this->characterRepository
            ->expects(self::once())
            ->method('byId')
            ->with(
                $command->id(),
            )->willReturn(null);

        $this->characterRepository
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

        $this->characterRepository = $this->createMock(CharacterRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new RemoveCharacterCommandHandler(
            $this->characterRepository,
            $this->domainEventPublisher,
        );
    }
}
