<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\PhpUnit;

use PHPUnit\Framework\MockObject\Stub\ReturnCallback;

trait SpyTestHelper
{
    public static function extractArguments(mixed &...$references): ReturnCallback
    {
        $calledCounter = 0;
        return new ReturnCallback(
            static function (...$items) use (&$references, &$calledCounter) {
                if (1 === \count($items)) {
                    $references[$calledCounter] = $items[0];
                    $calledCounter++;
                    return;
                }

                foreach ($items as $item) {
                    $references[$calledCounter][] = $item;
                }

                $calledCounter++;
            },
        );
    }

    public static function returnAndExtractArguments(mixed $return, mixed &...$references): ReturnCallback
    {
        $calledCounter = 0;
        return new ReturnCallback(
            static function (...$items) use ($return, &$references, &$calledCounter) {
                if (1 === \count($items)) {
                    $references[$calledCounter] = $items[0];
                    $calledCounter++;

                    return $return;
                }

                foreach ($items as $item) {
                    $references[$calledCounter][] = $item;
                }

                $calledCounter++;

                return $return;
            },
        );
    }
}
