<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FixerIoTest extends TestCase
{
    private DriverHarness $harness;
    private FixerIo $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('fixerio');
        $this->assertInstanceOf(FixerIo::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"timestamp":1519296206,"base":"USD","date":"2018-02-22","rates":{"GBP":0.72007,"JPY":107.346001,"EUR":0.813399}}'));

        $result = $this->driver->from(Currency::USD)->get([Currency::GBP, Currency::JPY, Currency::EUR]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2018-02-22', $result->getDate());
        $this->assertSame('0.72007', (string) $result->rate(Currency::GBP));
        $this->assertSame('107.346001', (string) $result->rate(Currency::JPY));
        $this->assertSame('0.813399', (string) $result->rate(Currency::EUR));
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"historical":true,"date":"2013-12-24","timestamp":1387929599,"base":"GBP","rates":{"USD":1.636492,"EUR":1.196476,"CAD":1.739516}}'));

        $result = $this->driver->from(Currency::GBP)->historical(
            new DateTimeImmutable('2013-12-24'),
            [Currency::USD, Currency::EUR, Currency::CAD],
        );

        $this->assertSame('GBP', $result->getBaseCurrency());
        $this->assertSame('2013-12-24', $result->getDate());
        $this->assertSame('1.636492', (string) $result->rate(Currency::USD));
        $this->assertSame('1.196476', (string) $result->rate(Currency::EUR));
        $this->assertSame('1.739516', (string) $result->rate(Currency::CAD));
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
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"query":{"from":"GBP","to":"JPY","amount":25},"info":{"timestamp":1519328414,"rate":148.972231},"historical":true,"date":"2018-02-22","result":3724.305775}'));

        $result = $this->driver->convert(25.0, Currency::GBP, Currency::JPY, new DateTimeImmutable('2018-02-22'));

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('GBP', $result->getBaseCurrency());
        $this->assertSame('2018-02-22', $result->getDate());
        $this->assertEqualsWithDelta(148.972231, $result->rateAsFloat(Currency::JPY), 0.000001);
    }

    #[Test]
    public function convert_requires_target_currency(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->amount(10.0)->from(Currency::USD)->convert();
    }

    #[Test]
    public function convert_requires_amount(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->convert();
    }

    #[Test]
    public function can_handle_response_failures(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":false,"error":{"code":104,"type":"api_volume_reached","info":"Your monthly API request volume has been reached. Please upgrade your plan."}}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->get();
    }

    #[Test]
    public function access_key_is_added_to_request_query_string(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"base":"USD","date":"2024-01-01","rates":{"EUR":0.9}}'));

        $this->driver->accessKey('my-fixerio-key')->from(Currency::USD)->get([Currency::EUR]);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('access_key=my-fixerio-key', $uri);
        $this->assertStringContainsString('base=USD', $uri);
        $this->assertStringContainsString('symbols=EUR', $uri);
    }
}
