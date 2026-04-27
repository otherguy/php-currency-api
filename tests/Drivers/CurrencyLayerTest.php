<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\CurrencyLayer;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CurrencyLayerTest extends TestCase
{
    private DriverHarness $harness;
    private CurrencyLayer $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('currencylayer');
        $this->assertInstanceOf(CurrencyLayer::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function can_get_latest_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"timestamp":1432400348,"source":"USD","quotes":{"USDAUD":1.278342,"USDEUR":1.278342,"USDGBP":0.908019,"USDPLN":3.731504}}'));

        $result = $this->driver->from(Currency::USD)->get([Currency::AUD, Currency::EUR, Currency::GBP, Currency::PLN]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2015-05-23', $result->getDate());

        $this->assertSame('1.278342', (string) $result->rate(Currency::AUD));
        $this->assertSame('1.278342', (string) $result->rate(Currency::EUR));
        $this->assertSame('0.908019', (string) $result->rate(Currency::GBP));
        $this->assertSame('3.731504', (string) $result->rate(Currency::PLN));
    }

    #[Test]
    public function can_get_historical_rates(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"historical":true,"date":"2005-02-01","timestamp":1107302399,"source":"USD","quotes":{"USDAED":3.67266,"USDAUD":1.293878}}'));

        $result = $this->driver->from(Currency::USD)->historical(
            new DateTimeImmutable('2005-02-01'),
            [Currency::AED, Currency::AUD],
        );

        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertSame('2005-02-01', $result->getDate());
        $this->assertSame('3.67266', (string) $result->rate(Currency::AED));
        $this->assertSame('1.293878', (string) $result->rate(Currency::AUD));
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
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"query":{"from":"USD","to":"GBP","amount":10},"info":{"timestamp":1430068515,"quote":0.658443},"result":6.58443}'));

        $result = $this->driver->convert(10.0, Currency::USD, Currency::GBP);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertEqualsWithDelta(0.658443, $result->rateAsFloat(Currency::GBP), 0.000001);
    }

    #[Test]
    public function can_handle_response_failures(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":false,"error":{"code":104,"info":"Your monthly usage limit has been reached. Please upgrade your subscription plan."}}'));

        $this->expectException(ApiException::class);
        $this->driver->from(Currency::USD)->to(Currency::EUR)->get();
    }

    #[Test]
    public function access_key_is_added_to_request_query_string(): void
    {
        $this->harness->http->enqueue(new Response(200, [], '{"success":true,"timestamp":1700000000,"source":"USD","quotes":{"USDEUR":0.9}}'));

        $this->driver->accessKey('cl-key')->from(Currency::USD)->get([Currency::EUR]);

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertStringContainsString('access_key=cl-key', $uri);
        $this->assertStringContainsString('source=USD', $uri);
        $this->assertStringContainsString('currencies=EUR', $uri);
    }
}
