<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\FastForex;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FastForexTest extends TestCase
{
    private DriverHarness $harness;
    private FastForex $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('fastforex');
        $this->assertInstanceOf(FastForex::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_added_to_request_query_string(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","result":{"EUR":0.8601},"updated":"2025-10-10 23:59:07","ms":13}'));

        $this->driver->accessKey('fastforex-token')->from(Currency::USD)->get(Currency::EUR);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('api_key=fastforex-token', $uri);
    }

    #[Test]
    public function can_get_latest_rate_for_one_currency(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","result":{"EUR":0.8601},"updated":"2025-10-10 23:59:07","ms":13}'));

        $result = $this->driver->from(Currency::USD)->get(Currency::EUR);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2025-10-10', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.fastforex.io/fetch-one', $uri);
        $this->assertStringContainsString('from=USD', $uri);
        $this->assertStringContainsString('to=EUR', $uri);
    }

    #[Test]
    public function can_get_latest_rates_for_multiple_currencies(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","results":{"EUR":0.8601,"GBP":0.7462},"updated":"2025-10-10 23:59:07","ms":13}'));

        $result = $this->driver->from(Currency::USD)->get([Currency::EUR, Currency::GBP]);

        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));
        $this->assertSame('0.7462', (string) $result->rate(Currency::GBP));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.fastforex.io/fetch-multi', $uri);
        $this->assertStringContainsString('to=EUR%2CGBP', $uri);
    }

    #[Test]
    public function can_get_latest_rates_without_symbols(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","results":{"EUR":0.8601},"updated":"2025-10-10 23:59:07","ms":13}'));

        $result = $this->driver->from(Currency::USD)->get();

        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.fastforex.io/fetch-all', $uri);
        $this->assertStringContainsString('from=USD', $uri);
        $this->assertStringNotContainsString('to=', $uri);
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"date":"2025-10-10","base":"USD","results":{"EUR":0.8601},"ms":2}'));

        $result = $this->driver->from(Currency::USD)->historical(new DateTimeImmutable('2025-10-10'), Currency::EUR);

        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2025-10-10', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.fastforex.io/historical', $uri);
        $this->assertStringContainsString('date=2025-10-10', $uri);
    }

    #[Test]
    public function can_convert_currency_amounts(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","amount":100,"result":{"EUR":86.01},"ms":13}'));

        $result = $this->driver->convert(100.0, Currency::USD, Currency::EUR);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertEqualsWithDelta(0.8601, $result->rateAsFloat(Currency::EUR), 0.000001);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('https://api.fastforex.io/convert', $uri);
        $this->assertStringContainsString('from=USD', $uri);
        $this->assertStringContainsString('to=EUR', $uri);
        $this->assertStringContainsString('amount=100', $uri);
    }

    #[Test]
    public function convert_uses_historical_rates_when_date_given(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"date":"2025-10-10","base":"USD","results":{"EUR":0.8601},"ms":2}'));

        $result = $this->driver->convert(100.0, Currency::USD, Currency::EUR, new DateTimeImmutable('2025-10-10'));

        $this->assertSame('2025-10-10', $result->getDate());
        $this->assertSame('0.8601', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('/historical', $uri);
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
        $this->harness->http->enqueue(new Response(401, [], '{"error":"Invalid API key"}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->get();
    }
}
