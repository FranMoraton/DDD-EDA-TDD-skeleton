<?php

declare(strict_types=1);

namespace App\Marketplace\Application\Command\Events\BringFromProvider;

use App\System\Application\Command;
use App\System\Domain\ValueObject\Uuid;
use Assert\Assert;

final class BringFromProviderCommand extends Command
{
    private const string NAME = 'company.marketplace.1.command.event.bring_from_provider';

    private string $providerId;

    public static function create(
        string $providerId,
    ): self {
        return self::fromPayload(
            Uuid::v4(),
            [
                'provider_id' => $providerId,
            ],
        );
    }

    public function providerId(): string
    {
        return $this->providerId;
    }

    public static function messageName(): string
    {
        return self::NAME;
    }

    public function rebuildPayload(): void
    {
        $payload = $this->payload();

        Assert::lazy()->tryAll()
            ->that($payload, 'payload')->isArray()
            ->keyExists('provider_id')
            ->verifyNow();

        $this->providerId = $payload['provider_id'];
    }
}
