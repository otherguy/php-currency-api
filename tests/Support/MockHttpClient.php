<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Support;

use Otherguy\Currency\Exceptions\MockHttpClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface|ClientExceptionInterface> */
    private array $queue = [];

    /** @var list<RequestInterface> */
    private array $sentRequests = [];

    public function enqueue(ResponseInterface|ClientExceptionInterface $item): self
    {
        $this->queue[] = $item;

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->sentRequests[] = $request;

        if ($this->queue === []) {
            throw new MockHttpClientException('MockHttpClient queue is empty.');
        }

        $next = array_shift($this->queue);

        if ($next instanceof ClientExceptionInterface) {
            throw $next;
        }

        return $next;
    }

    public function lastRequest(): ?RequestInterface
    {
        return $this->sentRequests[count($this->sentRequests) - 1] ?? null;
    }

    /**
     * @return list<RequestInterface>
     */
    public function sentRequests(): array
    {
        return $this->sentRequests;
    }
}
