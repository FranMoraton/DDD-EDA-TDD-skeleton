<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Behat;

use App\System\Domain\Service\JsonSerializer;
use App\Tests\System\Infrastructure\Faker\FakerFactory;
use App\Users\Infrastructure\Security\SecurityUser;
use Assert\Assert;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RestContext implements Context
{
    private const DEFAULT_USER = 'test@test.com';
    private const DEFAULT_PASSWORD = '\$argon2id\$v=19\$m=65536,t=4,p=1\$Ly5RdWl6Lk1OWEw4YzFpQg\$mJRVLsPIzJv3MndN5U0ju7zPgRAluNq/wyL/vn9xcTQ';

    private ?string $token = null;

    public function __construct(
        private JWTTokenManagerInterface $JWTManager,
        protected ResponseManager $responseManager,
    ) {
    }

    /**
     * @Given user is authenticated
     */
    public function userIsAuthenticated(
        string $email = self::DEFAULT_USER,
        ?PyStringNode $roles = null,
    ): void {
        $arrayRoles = null !== $roles
            ? (array) \json_decode($roles->getRaw(), true)
            : [];

        Assert::that($arrayRoles)->isArray()->all()->string();

        $faker = FakerFactory::create();

        $token = $this->JWTManager->create(
            $securityUser = SecurityUser::from(
                $faker->uuid(),
                $email,
                self::DEFAULT_PASSWORD,
                $arrayRoles
            ),
        );

        $this->token = 'Bearer ' . $token;
    }

    /**
     * @Given user is authenticated with roles:
     */
    public function userIsAuthenticatedWithRoles(PyStringNode $roles): void
    {
        $this->userIsAuthenticated(roles: $roles);
    }

    /**
     * @Given user is not authenticated
     */
    public function userIsNotAuthenticated(): void
    {
        $this->token = null;
    }

    /**
     * @When I send a :method request to :path
     * @throws \Exception
     */
    public function whenISendARequestTo(string $method, string $path): void
    {
        $this->responseManager->sendRequest(
            Request::create(
                $path,
                \strtoupper($method),
                server: [
                    'HTTP_AUTHORIZATION' => $this->token,
                    'CONTENT_TYPE' => 'application/json',
                ],
            ),
        );
    }

    /**
     * @When I send a :method request to :path with body:
     * @throws \Exception
     */
    public function whenISendARequestToWithBody(string $method, string $path, PyStringNode $body): void
    {
        $request = Request::create(
            uri: $path,
            method: \strtoupper($method),
            server: [
                'HTTP_AUTHORIZATION' => $this->token,
                'CONTENT_TYPE' => 'application/json',
            ],
            content: $body->getRaw(),
        );

        $this->responseManager->sendRequest($request);
    }

    /**
     * @When I send a :method request to :path status should be :status
     * @throws \Exception
     */
    public function whenISendARequestToStatusShouldBe(string $method, string $path, int $status): void
    {
        $this->responseManager->sendRequest(
            Request::create(
                $path,
                \strtoupper($method),
                server: [
                    'HTTP_AUTHORIZATION' => $this->token,
                    'CONTENT_TYPE' => 'application/json',
                ],
            ),
        );
        $this->theResponseStatusShouldBe($status);
    }

    /**
     * @Then the response status should be :status
     */
    public function theResponseStatusShouldBe(int $status): void
    {
        $response = $this->responseManager->getResponse();

        if (null === $response) {
            throw new \RuntimeException('No response received');
        }

        if ($status !== $response->getStatusCode()) {
            throw new \RuntimeException(\sprintf(
                'Expected status code <%d>, but %d in response. Contents <%s>',
                $status,
                $this->responseManager->getResponse()->getStatusCode(),
                $response->getContent(),
            ));
        }
    }

    /**
     * @Then the response content should be equal to:
     * @throws \JsonException
     */
    public function theResponseContentsShouldBeEqualTo(PyStringNode $body): void
    {
        if (null === $this->responseManager->getResponse()) {
            throw new \RuntimeException('No response received');
        }

        $expectedContents = \json_decode(\trim(\implode($body->getStrings())), true, 512, \JSON_THROW_ON_ERROR);
        $expectedContents = \json_encode($expectedContents);

        $responseContents = \json_decode(
            $this->responseManager->getResponse()->getContent(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );
        $responseContents = \json_encode($responseContents);


        if ($expectedContents !== $responseContents) {
            throw new \RuntimeException(\sprintf(
                'Expected body <%s>, but <%s> in response',
                $expectedContents,
                $responseContents,
            ));
        }
    }

    /**
     * @Then the response content should be empty
     */
    public function theResponseContentsShouldBeEmpty(): void
    {
        if (null === $this->responseManager->getResponse()) {
            throw new \RuntimeException('No response received');
        }

        $responseContents = $this->responseManager->getResponse()->getContent();

        if ('' !== $responseContents && '[]' !== $responseContents && '{}' !== $responseContents) {
            throw new \RuntimeException(\sprintf(
                'Expected empty response, but <%s> in response',
                $responseContents,
            ));
        }
    }

    /**
     * @Then the JSON response should be:
     */
    public function assertJsonResponse(PyStringNode $expected): void
    {
        $response = $this->responseManager->getResponse()->getContent();

        $message = 'The JSON response is not the expected';

        $arrayResponse = JsonSerializer::decodeArray($response);
        \ksort($arrayResponse);
        $sortedSerializedResponse = JsonSerializer::encode($arrayResponse);

        $arrayExpected = JsonSerializer::decodeArray($expected->getRaw());
        \ksort($arrayExpected);
        $sortedSerializedExpected = JsonSerializer::encode($arrayExpected);

        Assert::that(
            $sortedSerializedResponse,
            $message
        )->eq($sortedSerializedExpected);
    }

    /**
     * @Then /^print last response$/
     */
    public function printLastResponse()
    {
        echo $this->responseManager->getResponse()->getContent();
    }

}
