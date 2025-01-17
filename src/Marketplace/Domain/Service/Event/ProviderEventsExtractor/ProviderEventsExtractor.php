<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Service\Event\ProviderEventsExtractor;

interface ProviderEventsExtractor
{
    public function execute(string $providerId): array;
}
