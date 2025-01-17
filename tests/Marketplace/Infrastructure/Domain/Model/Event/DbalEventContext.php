<?php

declare(strict_types=1);

namespace App\Tests\Marketplace\Infrastructure\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\EventRepository;
use App\Marketplace\Infrastructure\Domain\Model\Event\DbalArrayEventMapper;
use App\System\Infrastructure\Service\JsonSerializer;
use App\Tests\System\Infrastructure\Behat\BehatContext;
use Behat\Gherkin\Node\PyStringNode;

final readonly class DbalEventContext extends BehatContext
{
    public function __construct(private EventRepository $eventRepository)
    {
    }

    /** @Given these Events exist */
    public function theseEventsExist(PyStringNode $payload): void
    {
        foreach ($this->stringNodeToArray($payload) as $event) {
            $event['zones'] = JsonSerializer::encode($event['zones']);
            $this->eventRepository->add(DbalArrayEventMapper::map($event));
        }
    }
}
