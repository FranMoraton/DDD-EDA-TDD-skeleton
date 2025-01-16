<?php
declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Guzzle;

use App\System\Infrastructure\Service\JsonSerializer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ClientMock extends Client
{
    private const URI_ATTRIBUTE_PATTERN = '/{[a-z_}]*/';
    private const ATTRIBUTE_DELIMITER_IN_URI = '/';

    private static array $responses;

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        throw new \Exception('sendAsync not allowed');
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        throw new \Exception('send not allowed');
    }

    public function requestAsync($method, $uri = '', array $options = []): PromiseInterface
    {
        throw new \Exception('requestAsync not allowed');
    }

    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        \assert(\is_string($uri));

        $body = \array_key_exists(RequestOptions::JSON, $options)
            ? JsonSerializer::encode($options[RequestOptions::JSON])
            : null;

        $uriWithQuery = null;
        if (\array_key_exists(RequestOptions::QUERY, $options)) {
            $value = \http_build_query($options[RequestOptions::QUERY], '', '&', \PHP_QUERY_RFC3986);

            $uriWithQuery = $uri . '?' . \urldecode($value);
        }

        try {
            $response = $this->getResponse($method, $uri);
        } catch (\Throwable $exception) {
            if (null === $uriWithQuery) {
                throw $exception;
            }

            $response = $this->getResponse($method, $uriWithQuery);
        }

        if (\array_key_exists('request_body', $response) && null !== $response['request_body']) {
            \assert(
                $body === $response['request_body'],
                \sprintf('The request body %s not match with %s', $body, $response['request_body']),
            );
        }

        $this->assertResponseHaveValidStatusCode($method, $uri, $response);

        return new Response($response['status'], $response['headers'], $response['body']);
    }

    public function addRequest(
        string $method,
        string $uri,
        array $requestBody,
    ): void {
        $method = \strtoupper($method);

        self::$responses[$method][$uri]['request_body'][] = JsonSerializer::encode($requestBody);
    }

    public function addResponse(
        string $method,
        string $uri,
        int $status,
        ?string $body,
        array $headers = [],
    ): void {
        $method = \strtoupper($method);

        \preg_match_all(self::URI_ATTRIBUTE_PATTERN, $uri, $attributes);

        self::$responses[$method][$uri]['attributes'] = 0 === \count($attributes) ? [] : $attributes[0];
        self::$responses[$method][$uri]['responses'][] = [
            'status' => $status,
            'headers' => $headers,
            'body' => $this->encodeBodyBasedOnHeaders($body, $headers),
        ];
    }

    public function reset(): void
    {
        self::$responses = [];
    }

    private function getResponse(string $method, string $uri): array
    {
        $method = \strtoupper($method);

        $this->assertMethodAlreadyExists($method);

        if (\array_key_exists($uri, self::$responses[$method])) {
            $responses = self::$responses[$method][$uri];

            $response = \array_shift($responses['responses']);
            $response['body'] = $this->decodeBodyBasedOnHeaders($response['body'], $response['headers']);

            return $response;
        }

        $response = $this->getResponseByUriWithAttributes($method, $uri);

        if (null === $response) {
            throw new \Exception(
                \sprintf('The uri {%s} with method {%s} not found in responses array', $uri, $method),
            );
        }

        $response['body'] = $this->decodeBodyBasedOnHeaders($response['body'], $response['headers']);

        return $response;
    }

    private function encodeBodyBasedOnHeaders(?string $body, array $headers): null|array|string
    {
        if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/xml') {
            return $body;
        }

        return $body ? JsonSerializer::encode($body) : null;
    }

    private function decodeBodyBasedOnHeaders(?string $body, array $headers): null|array|string
    {
        if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/xml') {
            return $body;
        }

        return $body ? JsonSerializer::decodeArray($body) : null;
    }

    private function assertMethodAlreadyExists(string $method): void
    {
        if (true === \array_key_exists($method, self::$responses)) {
            return;
        }

        throw new \Exception(
            \sprintf('The method {%s} not found in responses array', $method),
        );
    }

    private function getResponseByUriWithAttributes(string $method, string $uri): ?array
    {
        foreach (self::$responses[$method] as $currentUri => $value) {
            $uriReplaced = $this->replaceUriAttributes($uri, $currentUri, $value['attributes']);

            if ($uriReplaced === $currentUri) {
                return \array_shift(self::$responses[$method][$uriReplaced]['responses']);
            }
        }

        return null;
    }

    private function replaceUriAttributes(string $uri, string $currentUri, array $attributes): string
    {
        foreach ($attributes as $attribute) {
            $startPosition = \strpos($currentUri, $attribute);
            \assert(\is_int($startPosition));

            if (\strlen($uri) < $startPosition) {
                break;
            }

            $endPosition = \strpos($uri, self::ATTRIBUTE_DELIMITER_IN_URI, $startPosition);

            $toReplace = \substr($uri, $startPosition);

            if (false !== $endPosition) {
                $length = $endPosition - $startPosition;
                $toReplace = \substr($uri, $startPosition, $length);
            }

            $uri = \str_replace($toReplace, $attribute, $uri);
        }

        return $uri;
    }

    private function assertResponseHaveValidStatusCode(string $method, string $uri, array $response): void
    {
        if ($response['status'] < 400) {
            return;
        }

        $headers = $response['headers'];
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = $response['type'] === 'xml' ? 'application/xml' : 'application/json';
        }

        throw RequestException::create(
            new Request($method, $uri),
            new Response($response['status'], $headers, $response['body']),
        );
    }
}
