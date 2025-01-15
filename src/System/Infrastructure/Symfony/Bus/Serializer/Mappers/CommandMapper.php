<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Serializer\Mappers;

use App\System\Application\Command;

final class CommandMapper
{
    protected array $commands = [];

    public function add(string $name, string $className): void
    {
        if (false === \is_a($className, Command::class, true)) {
            return;
        }

        if (true === \array_key_exists($name, $this->commands)) {
            throw new \RuntimeException(\sprintf(
                'Command <%s> has already been registered. Maybe you forgot to update the use case name',
                $name,
            ));
        }

        $this->commands[$name] = $className;
    }

    public function get(string $name): ?string
    {
        return $this->commands[$name] ?? null;
    }

    public function list(): array
    {
        return $this->commands;
    }
}
