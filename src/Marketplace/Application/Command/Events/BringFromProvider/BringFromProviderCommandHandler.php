<?php

namespace App\Marketplace\Application\Command\Events\BringFromProvider;

use App\Marketplace\Application\Command\Events\Register\RegisterEventCommand;
use App\Marketplace\Domain\Service\Event\ProviderEventsExtractor\ProviderEventsExtractor;
use App\System\Application\AsyncCommandPublisher;
use App\System\Domain\ValueObject\Uuid;

final readonly class BringFromProviderCommandHandler
{
    public function __construct(
        private ProviderEventsExtractor $providerEventsExtractor,
        private AsyncCommandPublisher $asyncCommandPublisher,
    ) {
    }

    public function __invoke(BringFromProviderCommand $command): void
    {
        $commands = [];

        $events = $this->providerEventsExtractor->execute($command->providerId());

        foreach ($events as $event) {
            $commands[] = RegisterEventCommand::fromPayload(
                $id = Uuid::v4(),
                array_merge($event, ['id' => $id->value()]),
            );
        }

        $this->asyncCommandPublisher->execute(...$commands);
    }
}
