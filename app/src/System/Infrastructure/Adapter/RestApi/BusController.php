<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Adapter\RestApi;

use App\System\Application\AsyncCommandPublisher;
use App\System\Application\Command;
use App\System\Application\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

abstract class BusController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus,
        private readonly AsyncCommandPublisher $asyncCommandPublisher,
    ) {
    }

    public function getRequestBody(Request $request): ParameterBag
    {
        $validation = \json_validate($request->getContent());
        if (false === $validation) {
            throw new \Exception('Invalid request body');
        }


        return new ParameterBag(json_decode($request->getContent(), true));
    }

    protected function publishSyncCommand(Command $command): mixed
    {
        $envelope =  $this->commandBus->dispatch($command);
        $handledStamps = $envelope->all(HandledStamp::class);

        return $handledStamps[0]->getResult();
    }

    protected function publishQuery(Query $query): mixed
    {
        $envelope =  $this->queryBus->dispatch($query);
        $handledStamps = $envelope->all(HandledStamp::class);

        return $handledStamps[0]->getResult();
    }

    protected function publishAsync(Command ...$commands): void
    {
        $this->asyncCommandPublisher->execute(...$commands);
    }

}
