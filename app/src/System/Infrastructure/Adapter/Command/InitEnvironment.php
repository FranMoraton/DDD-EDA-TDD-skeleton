<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Adapter\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InitEnvironment extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:environment:init')
            ->setDescription('create transports, db & execute migrations')
            ->setHelp('This command allows you to execute all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();
        \assert($app instanceof Application);

        $app->setAutoExit(false);

        $app->run($this->messengerMigrationCommand(), $output);
        $output->writeln('<info>Migrations <comment>messenger</comment> executed</info>');

        $app->run($this->createDatabaseCommand(), $output);
        $app->run($this->dbMigrationCommand(), $output);
        $output->writeln('<info>Migrations <comment>postgresDb</comment> executed</info>');

        return 0;
    }

    private function messengerMigrationCommand(): ArrayInput
    {
        return new ArrayInput(
            [
                'command' => 'messenger:setup-transports',
            ],
        );
    }

    private function createDatabaseCommand(): ArrayInput
    {
        return new ArrayInput(
            [
                'command' => 'doctrine:database:create',
                '--no-interaction',
            ],
        );
    }

    private function dbMigrationCommand(): ArrayInput
    {
        return new ArrayInput(
            [
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction',
            ],
        );
    }
}
