<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\CurrencyApi;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CurrencyApiTest extends TestCase
{
    private DriverHarness $harness;
    private CurrencyApi $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('currencyapi');
        $this->assertInstanceOf(CurrencyApi::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_sent_as_apikey_header(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"meta":{"last_updated_at":"2025-10-11T10:15:59Z"},"data":{"EUR":{"code":"EUR","value":0.8601}}}'));

        $this->driver->accessKey('currencyapi-token')->from(Currency::USD)->get([Currency::EUR]);

        $request = $this->harness->http->lastRequest();
        $this->assertNotNull($request);
        $this->assertSame(['currencyapi-token'], $request->getHeader('apikey'));
        $this->assertStringNotContainsString('apikey=', (string) $request->getUri());
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"meta":{"last_updated_at":"2025-10-11T10:15:59Z"},"data":{"EUR":{"code":"EUR","value":0.8601},"GBP":{"code":"GBP","value":0.7462}}}'));

        $result = $this->driver->from(Currency::USD)->get([Currency::EUR, Currency::GBP]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2025-10-11', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));
        $this->assertSame('0.7462', (string) $result->rate(Currency::GBP));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.currencyapi.com/v3/latest', $uri);
        $this->assertStringContainsString('base_currency=USD', $uri);
        $this->assertStringContainsString('currencies=EUR%2CGBP', $uri);
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"meta":{"last_updated_at":"2025-10-10T23:59:59Z"},"data":{"EUR":{"code":"EUR","value":0.8601}}}'));

        $result = $this->driver->from(Currency::USD)->historical(new DateTimeImmutable('2025-10-10'), [Currency::EUR]);

        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2025-10-10', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.currencyapi.com/v3/historical', $uri);
        $this->assertStringContainsString('date=2025-10-10', $uri);
    }

    #[Test]
    public function can_convert_currency_amounts(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"meta":{"last_updated_at":"2025-10-11T10:15:59Z"},"data":{"value":86.01}}'));

        $result = $this->driver->convert(100.0, Currency::USD, Currency::EUR);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2025-10-11', $result->getDate());
        $this->assertEqualsWithDelta(0.8601, $result->rateAsFloat(Currency::EUR), 0.000001);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.currencyapi.com/v3/convert', $uri);
        $this->assertStringContainsString('value=100', $uri);
        $this->assertStringContainsString('base_currency=USD', $uri);
        $this->assertStringContainsString('currencies=EUR', $uri);
    }

    #[Test]
    public function convert_uses_historical_rates_when_date_given(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"meta":{"last_updated_at":"2025-10-10T23:59:59Z"},"data":{"EUR":{"code":"EUR","value":0.8601}}}'));

        $result = $this->driver->convert(100.0, Currency::USD, Currency::EUR, new DateTimeImmutable('2025-10-10'));

        $this->assertSame('2025-10-10', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('/v3/historical', $uri);
    }

    #[Test]
    public function fails_to_get_historical_rates_if_date_not_set(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->historical();
    }

    #[Test]
    public function can_handle_response_failures(): void
    {
        $this->harness->http->enqueue(new Response(422, [], '{"message":"Validation error","errors":{"currencies":["The selected currencies is invalid."]}}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to('ZZZ')->get();
    }
}
