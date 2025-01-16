<?php

namespace App\Tests\System\Infrastructure\Behat;

use App\System\Application\Message;
use App\System\Domain\Event\DomainEvent;
use App\System\Infrastructure\Symfony\Bus\Serializer\CustomSerializer;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

final readonly class MessageBusContext implements Context
{
    public function __construct(
        private ResponseManager $responseManager,
        private CustomSerializer $customSerializer,
    ) {
    }

    /**
     * @Given the following command:
     * @throws \Exception
     */
    public function givenTheFollowingCommand(PyStringNode $body): void
    {
        $bodyContents = \trim(\implode($body->getStrings()));
        $bodyContents = \json_decode($bodyContents, true, 512, \JSON_THROW_ON_ERROR);

        $contents = [
            'body' => \json_encode($bodyContents),
            'headers' => [],
        ];

        $envelope = $this->customSerializer->decode($contents);
        $message = $envelope->getMessage();
        \assert($message instanceof Message);

        $this->responseManager->sendMessage($message);
    }

    /**
     * @Given the following event:
     * @throws \Exception
     */
    public function givenTheFollowingEvent(PyStringNode $body): void
    {
        $bodyContents = \trim(\implode($body->getStrings()));
        $bodyContents = \json_decode($bodyContents, true, 512, \JSON_THROW_ON_ERROR);

        $contents = [
            'body' => \json_encode($bodyContents),
            'headers' => [],
        ];

        $envelope = $this->customSerializer->decode($contents);
        $message = $envelope->getMessage();
        \assert($message instanceof DomainEvent);

        $this->responseManager->sendEvent($message);
    }

    /**
     * @Then the :eventName event should be dispatched
     * @throws \Exception
     */
    public function theEventShouldBeDispatched(string $eventName): void
    {
        $this->responseManager->getLatestSpyMiddleware()->hasEvent($eventName);
    }

    /**
     * @Then the :eventName event should be dispatched :nTimes times
     * @throws \Exception
     */
    public function theEventShouldBeDispatchedNTimes(string $eventName, int $nTimes): void
    {
        $this->responseManager->getLatestSpyMiddleware()->hasEvent($eventName, $nTimes);
    }

    /**
     * @Then the :eventName event should not be dispatched
     * @throws \Exception
     */
    public function theEventShouldNotBeDispatched(string $eventName): void
    {
        $this->responseManager->getLatestSpyMiddleware()->hasEvent($eventName, 0);
    }

    /**
     * @Then the :requestName command should be dispatched
     * @throws \Exception
     */
    public function theCommandShouldBeDispatched(string $requestName): void
    {
        $this->responseManager->getLatestSpyMiddleware()->hasCommand($requestName);
    }

    /**
     * @Given the buses are clean
     */
    public function theBusesAreClean(): void
    {
        $this->responseManager->getLatestSpyMiddleware()?->clean();
    }
}
