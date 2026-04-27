<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\ExchangeRatesApi;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExchangeRatesApiTest extends TestCase
{
    private DriverHarness $harness;
    private ExchangeRatesApi $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('exchangeratesapi');
        $this->assertInstanceOf(ExchangeRatesApi::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_sent_as_apikey_query_param(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"base":"EUR","date":"2024-01-01","rates":{"USD":1.1}}'));

        $this->driver->accessKey('apilayer-token')->get([Currency::USD]);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('apikey=apilayer-token', $uri);
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"base":"EUR","rates":{"NOK":9.772,"USD":1.1289,"JPY":122.44},"date":"2019-06-13"}'));

        $result = $this->driver->from(Currency::EUR)->get([Currency::NOK, Currency::JPY, Currency::USD]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('EUR', $result->getBaseCurrency());
        $this->assertSame('2019-06-13', $result->getDate());
        $this->assertSame('9.772', (string) $result->rate(Currency::NOK));
        $this->assertSame('1.1289', (string) $result->rate(Currency::USD));
        $this->assertSame('122.44', (string) $result->rate(Currency::JPY));
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"base":"GBP","rates":{"NOK":10.088752796,"CAD":1.7366601677,"USD":1.636783369,"JPY":170.6398095762,"EUR":1.1961293255},"date":"2013-12-24"}'));

        $result = $this->driver->from(Currency::GBP)->historical(
            new DateTimeImmutable('2013-12-24'),
            [Currency::USD, Currency::EUR, Currency::CAD, Currency::JPY, Currency::NOK],
        );

        $this->assertSame('GBP', $result->getBaseCurrency());
        $this->assertSame('2013-12-24', $result->getDate());
        $this->assertSame('1.636783369', (string) $result->rate(Currency::USD));
        $this->assertSame('1.1961293255', (string) $result->rate(Currency::EUR));
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
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"query":{"from":"GBP","to":"JPY","amount":25},"info":{"timestamp":1519328414,"rate":148.972231},"date":"2018-02-22","result":3724.305775}'));

        $result = $this->driver->convert(25.0, Currency::GBP, Currency::JPY, new DateTimeImmutable('2018-02-22'));

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertEqualsWithDelta(148.972231, $result->rateAsFloat(Currency::JPY), 0.000001);
    }

    #[Test]
    public function can_handle_response_failures(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":false,"error":{"code":101,"info":"Invalid API key"}}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->get();
    }
}
