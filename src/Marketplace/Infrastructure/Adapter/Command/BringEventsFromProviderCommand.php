<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Adapter\Command;

use App\Marketplace\Application\Command\Events\BringFromProvider\BringFromProviderCommand;
use App\System\Domain\ValueObject\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class BringEventsFromProviderCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $commandBus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:events:from-provider')
            ->setDescription('dispatch bring events from provider');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->commandBus->dispatch(BringFromProviderCommand::create(Uuid::v4()->value()));
        } catch (\Throwable $exception) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
