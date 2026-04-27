<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\Frankfurter;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FrankfurterTest extends TestCase
{
    private DriverHarness $harness;
    private Frankfurter $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('frankfurter');
        $this->assertInstanceOf(Frankfurter::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_rejected(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Frankfurter does not require an API key.');
        $this->driver->accessKey('any');
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"amount":1.0,"base":"EUR","date":"2024-01-02","rates":{"USD":1.1,"GBP":0.86}}'));

        $result = $this->driver->get([Currency::USD, Currency::GBP]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('EUR', $result->getBaseCurrency());
        $this->assertSame('2024-01-02', $result->getDate());
        $this->assertSame('1.1', (string) $result->rate(Currency::USD));
        $this->assertSame('0.86', (string) $result->rate(Currency::GBP));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.frankfurter.dev/v1/latest', $uri);
        $this->assertStringContainsString('base=EUR', $uri);
        $this->assertStringContainsString('symbols=USD%2CGBP', $uri);
    }

    #[Test]
    public function can_get_latest_rates_without_symbols(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"amount":1.0,"base":"EUR","date":"2024-01-02","rates":{"USD":1.1}}'));

        $this->driver->get();

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('base=EUR', $uri);
        $this->assertStringNotContainsString('symbols=', $uri);
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"amount":1.0,"base":"EUR","date":"2020-04-01","rates":{"USD":1.0934}}'));

        $result = $this->driver->historical(new DateTimeImmutable('2020-04-01'), [Currency::USD]);

        $this->assertSame('2020-04-01', $result->getDate());
        $this->assertSame('1.0934', (string) $result->rate(Currency::USD));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.frankfurter.dev/v1/2020-04-01', $uri);
    }

    #[Test]
    public function fails_to_get_historical_rates_if_date_not_set(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->to(Currency::USD)->historical();
    }

    #[Test]
    public function convert_uses_latest_rates_when_no_date_given(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"amount":1.0,"base":"EUR","date":"2024-01-02","rates":{"USD":1.1}}'));

        $result = $this->driver->convert(10.0, Currency::EUR, Currency::USD);

        $this->assertSame('1.1', (string) $result->rate(Currency::USD));
    }

    #[Test]
    public function convert_uses_historical_rates_when_date_given(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"amount":1.0,"base":"EUR","date":"2020-04-01","rates":{"USD":1.0934}}'));

        $result = $this->driver->convert(10.0, Currency::EUR, Currency::USD, new DateTimeImmutable('2020-04-01'));

        $this->assertSame('2020-04-01', $result->getDate());
        $this->assertSame('1.0934', (string) $result->rate(Currency::USD));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('/2020-04-01', $uri);
    }

    #[Test]
    public function convert_requires_target_currency(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->amount(10.0)->convert();
    }

    #[Test]
    public function convert_requires_amount(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->to(Currency::USD)->convert();
    }
}
