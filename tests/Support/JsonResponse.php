<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class JsonResponse
{
    public static function ok(string $body): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }

    /**
     * @param array<string, string> $headers
     */
    public static function with(int $status, string $body, array $headers = []): ResponseInterface
    {
        return new Response($status, $headers + ['Content-Type' => 'application/json'], $body);
    }
}
