<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Http\Factory\Guzzle\RequestFactory;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Tests\Support\MockHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BaseCurrencyDriverTest extends TestCase
{
    private MockHttpClient $http;
    private TestableDriver $driver;

    protected function setUp(): void
    {
        $this->http   = new MockHttpClient();
        $this->driver = new TestableDriver($this->http, new RequestFactory());
    }

    #[Test]
    public function init_will_properly_set_parameters(): void
    {
        $this->driver->source(Currency::ANG)->currencies([Currency::DKK, Currency::USD]);

        $this->assertSame(['DKK', 'USD'], $this->driver->getSymbols());
        $this->assertSame('ANG', $this->driver->getBaseCurrency());
    }

    #[Test]
    public function defaults_to_https_protocol(): void
    {
        $this->assertSame('https', $this->driver->getProtocol());
        $this->assertSame('https', $this->driver->secure()->getProtocol());
    }

    #[Test]
    public function from_sets_base_currency(): void
    {
        $this->assertNotSame('BTC', $this->driver->getBaseCurrency());
        $this->assertSame('BTC', $this->driver->from(Currency::BTC)->getBaseCurrency());
    }

    #[Test]
    public function to_sets_target_currencies(): void
    {
        $this->assertSame(
            ['BTC', 'LTL'],
            $this->driver->to([Currency::BTC, Currency::LTL])->getSymbols(),
        );
    }

    #[Test]
    public function setters_are_fluent(): void
    {
        $factory = new RequestFactory();
        $base    = new MockCurrencyDriver($this->http, $factory);

        $this->assertInstanceOf(MockCurrencyDriver::class, $base->source(Currency::BTC));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->from(Currency::BTC));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->amount(12.0));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->to(Currency::LTL));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->currencies(Currency::LTL));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->secure());
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->config('test', 'value'));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->accessKey('access key'));
        $this->assertInstanceOf(MockCurrencyDriver::class, $base->date(new DateTimeImmutable('2019-06-11')));
    }

    #[Test]
    public function date_setter_formats_to_iso_date(): void
    {
        $this->driver->date(new DateTimeImmutable('@1560293762'));
        $this->assertSame('2019-06-11', $this->driver->getDate());

        $this->driver->date(new DateTimeImmutable('2019-06-11'));
        $this->assertSame('2019-06-11', $this->driver->getDate());

        $this->driver->date(null);
        $this->assertNull($this->driver->getDate());
    }

    #[Test]
    public function api_request_decodes_json_response_body(): void
    {
        $this->http->enqueue(new Response(200, [], '{"success":true,"base":"USD","date":"2019-06-11","rates":{"JPY":107.346001,"EUR":0.813399}}'));

        $response = $this->driver->callApi('latest');

        $this->assertSame('USD', $response['base']);
        $this->assertSame('2019-06-11', $response['date']);
        $this->assertSame(107.346001, $response['rates']['JPY']);
    }

    #[Test]
    public function api_request_wraps_invalid_json_in_api_exception(): void
    {
        $this->http->enqueue(new Response(200, [], 'Cannot reach upstream currency data server!'));

        $this->expectException(ApiException::class);
        $this->driver->callApi('nojson');
    }

    #[Test]
    public function api_request_includes_http_params_in_query_string(): void
    {
        $this->http->enqueue(new Response(200, [], '{"ok":true}'));

        $this->driver->config('access_key', 'secret');
        $this->driver->callApi('latest', ['base' => 'USD']);

        $uri = (string) $this->http->lastRequest()?->getUri();
        $this->assertStringContainsString('access_key=secret', $uri);
        $this->assertStringContainsString('base=USD', $uri);
        $this->assertStringStartsWith('https://example.test/latest?', $uri);
    }

    #[Test]
    public function api_request_omits_query_string_when_no_params(): void
    {
        $this->http->enqueue(new Response(200, [], '{"ok":true}'));

        $this->driver->callApi('ping');

        $this->assertSame('https://example.test/ping', (string) $this->http->lastRequest()?->getUri());
    }
}
