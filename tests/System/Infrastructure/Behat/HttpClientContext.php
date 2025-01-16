<?php
declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Behat;

use App\System\Infrastructure\Service\JsonSerializer;
use App\Tests\System\Infrastructure\Guzzle\ClientMock;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

final class HttpClientContext implements Context
{
    private const HTTP_NO_CONTENT = 204;

    private ClientMock $clientMock;

    public function __construct(ClientMock $clientMock)
    {
        $this->clientMock = $clientMock;
    }

    /**
     * @BeforeScenario
     */
    public function bootstrapEnvironment(): void
    {
        $this->clientMock->reset();
    }

    /**
     * @Given the :method request to :uri will come with body
     */
    public function theRequestWillHaveBody(
        string $method,
        string $uri,
        PyStringNode $requestBody,
    ): void {
        $this->clientMock->addRequest(
            $method,
            $uri,
            JsonSerializer::decodeArray($requestBody->getRaw()),
        );
    }

    /**
     * @Given the :method request to :uri will have status :status and response
     */
    public function theRequestWillHaveStatusAndResponse(
        string $method,
        string $uri,
        int $status,
        PyStringNode $response,
    ): void {
        $this->clientMock->addResponse(
            $method,
            $uri,
            $status,
            $response->getRaw(),
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Given the :method request to :uri will have status :status
     */
    public function theRequestWillHaveStatus(
        string $method,
        string $uri,
        int $status,
    ): void {
        $this->clientMock->addResponse(
            $method,
            $uri,
            $status,
            null,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Given the :method request to :uri will have empty response
     */
    public function theRequestWillHaveEmptyResponse(string $method, string $uri): void
    {
        $this->clientMock->addResponse(
            $method,
            $uri,
            self::HTTP_NO_CONTENT,
            null,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Given the :method request to :uri will have status :status and XML response
     */
    public function theRequestWillHaveStatusAndXmlResponse(
        string $method,
        string $uri,
        int $status,
        PyStringNode $response,
    ): void {
        $this->clientMock->addResponse(
            $method,
            $uri,
            $status,
            $response->getRaw(),
            ['Content-Type' => 'application/xml'],
        );
    }
}
