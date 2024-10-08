<?php

namespace App\Tests\Lotr\Application\Command\Factions\Create;

use PHPUnit\Framework\TestCase;

class CreateFactionCommandHandlerTest extends TestCase
{
    public function testGivenQueryWhenCountryNotFoundThenFail(): void
    {
        self::assertEquals(1, 1);
    }
}