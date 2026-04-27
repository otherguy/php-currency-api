<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\OpenExchangeRates;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OpenExchangeRatesTest extends TestCase
{
    private DriverHarness $harness;
    private OpenExchangeRates $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('openexchangerates');
        $this->assertInstanceOf(OpenExchangeRates::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_sent_as_app_id_query_param(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"base":"USD","timestamp":1700000000,"rates":{"EUR":0.9}}'));

        $this->driver->accessKey('app-id-token')->from(Currency::USD)->get([Currency::EUR]);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('app_id=app-id-token', $uri);
        $this->assertStringNotContainsString('access_key=', $uri);
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"timestamp":1449877801,"base":"USD","rates":{"AED":3.672538,"AFN":66.809999,"ALL":125.716501,"AMD":484.902502,"ANG":1.788575}}'));

        $result = $this->driver->from(Currency::USD)->get([Currency::AED, Currency::AFN, Currency::ALL, Currency::AMD, Currency::ANG]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2015-12-11', $result->getDate());
        $this->assertSame('3.672538', (string) $result->rate(Currency::AED));
        $this->assertSame('66.809999', (string) $result->rate(Currency::AFN));
        $this->assertSame('125.716501', (string) $result->rate(Currency::ALL));
        $this->assertSame('484.902502', (string) $result->rate(Currency::AMD));
        $this->assertSame('1.788575', (string) $result->rate(Currency::ANG));
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"timestamp":982342800,"base":"USD","rates":{"AED":3.67246,"ALL":144.529793,"ANG":1.79}}'));

        $result = $this->driver->from(Currency::USD)->historical(
            new DateTimeImmutable('2001-02-16'),
            [Currency::AED, Currency::ALL, Currency::ANG],
        );

        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2001-02-16', $result->getDate());
        $this->assertSame('3.67246', (string) $result->rate(Currency::AED));
        $this->assertSame('144.529793', (string) $result->rate(Currency::ALL));
        $this->assertSame('1.79', (string) $result->rate(Currency::ANG));
    }

    #[Test]
    public function fails_to_get_historical_rates_if_date_not_set(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->historical();
    }

    #[Test]
    public function can_convert_currency_amounts(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"request":{"query":"/convert/19999.95/GBP/EUR","amount":19999.95,"from":"GBP","to":"EUR"},"meta":{"timestamp":1449885661,"rate":1.383702},"response":27673.975864}'));

        $result = $this->driver->convert(19999.95, Currency::GBP, Currency::EUR);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertEqualsWithDelta(1.383702, $result->rateAsFloat(Currency::EUR), 0.000001);
    }

    #[Test]
    public function can_handle_response_failures(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"error":true,"status":401,"message":"invalid_app_id","description":"Invalid App ID provided"}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->get();
    }
}
