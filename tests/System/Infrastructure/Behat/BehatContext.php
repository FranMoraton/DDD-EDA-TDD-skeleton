<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

abstract readonly class BehatContext implements Context
{
    public function stringNodeToArray(PyStringNode $payload): array
    {
        $result = \json_decode(
            json: $payload->getRaw(),
            associative: true,
            flags: \JSON_THROW_ON_ERROR,
        );
        \assert(\is_array($result), 'Invalid JSON');

        return $result;
    }
}
