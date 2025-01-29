<?php

declare(strict_types=1);

namespace App\System\Domain\Service;

final class JsonSerializer
{
    public static function encode(mixed $value): string
    {
        $result = \json_encode(
            value: $value,
            flags: \JSON_THROW_ON_ERROR,
        );
        \assert(\is_string($result), 'Invalid Value');

        return $result;
    }

    public static function decodeArray(string $json): array
    {
        $result = \json_decode(
            json: $json,
            associative: true,
            flags: \JSON_THROW_ON_ERROR,
        );
        \assert(\is_array($result), 'Invalid JSON');

        return $result;
    }
}
