<?php

declare(strict_types=1);

namespace App\Tests\Marketplace\Infrastructure\Domain\Model\EventProjection;

use App\Marketplace\Domain\Model\EventProjection\EventProjectionRepository;
use App\Marketplace\Infrastructure\Domain\Model\EventProjection\DbalArrayEventProjectionMapper;
use App\Tests\System\Infrastructure\Behat\BehatContext;
use Behat\Gherkin\Node\PyStringNode;

final readonly class DbalEventProjectionContext extends BehatContext
{
    public function __construct(private EventProjectionRepository $eventProjectionRepository)
    {
    }

    /** @Given these EventProjections exist */
    public function theseEventProjectionsExist(PyStringNode $payload): void
    {
        foreach ($this->stringNodeToArray($payload) as $event) {
            $this->eventProjectionRepository->upsertByEventDate(DbalArrayEventProjectionMapper::map($event));
        }
    }
}
