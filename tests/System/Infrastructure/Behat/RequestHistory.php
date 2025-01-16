<?php

declare(strict_types=1);

namespace App\Tests\System\Infrastructure\Behat;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestHistory
{
    private array $requests;
    private array $responses;

    public function __construct()
    {
        $this->requests = [];
        $this->responses = [];
    }

    public function add(Request $request, Response $response): void
    {
        $this->requests[] = $request;
        $this->responses[] = $response;
    }

    public function getLastRequest(): Request
    {
        return \end($this->requests);
    }

    public function getLastResponse(): Response
    {
        return \end($this->responses);
    }

    public function reset(): void
    {
        $this->requests = [];
        $this->responses = [];
    }
}
