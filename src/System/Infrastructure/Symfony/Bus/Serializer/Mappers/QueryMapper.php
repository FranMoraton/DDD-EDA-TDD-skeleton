<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Serializer\Mappers;

use App\System\Application\Query;

final class QueryMapper
{
    protected array $queries = [];

    public function add(string $name, string $className): void
    {
        if (false === \is_a($className, Query::class, true)) {
            return;
        }

        if (true === \array_key_exists($name, $this->queries)) {
            throw new \RuntimeException(\sprintf(
                'Command <%s> has already been registered. Maybe you forgot to update the use case name',
                $name,
            ));
        }

        $this->queries[$name] = $className;
    }

    public function get(string $name): ?string
    {
        return $this->queries[$name] ?? null;
    }

    public function list(): array
    {
        return $this->queries;
    }
}
