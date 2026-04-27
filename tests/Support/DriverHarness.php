<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Support;

use Http\Factory\Guzzle\RequestFactory;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\CurrencyDriverContract;

class DriverHarness
{
    public readonly MockHttpClient $http;

    public function __construct()
    {
        $this->http = new MockHttpClient();
    }

    public function make(string $name): CurrencyDriverContract
    {
        return (new DriverFactory())->build($name, $this->http, new RequestFactory());
    }
}
