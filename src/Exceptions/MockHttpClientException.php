<?php

declare(strict_types=1);

namespace Otherguy\Currency\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class MockHttpClientException extends RuntimeException implements ClientExceptionInterface
{
}
