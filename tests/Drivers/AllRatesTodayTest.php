<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeImmutable;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\AllRatesToday;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use Otherguy\Currency\Tests\Support\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AllRatesTodayTest extends TestCase
{
    private const ECB_BODY = '{"result":"success","provider":"https://allratestoday.com","source":"European Central Bank reference rates, served by AllRatesToday","base_code":"EUR","date":"2026-10-28","rates":{"EUR":1,"USD":1.1133,"GBP":0.8713,"JPY":171.52}}';

    // Foreign-per-home table: the quote column is the home currency (SAR).
    private const SAMA_BODY = '{"bank":"sama","rate_date":"2026-10-28","age_days":1,"stale":false,"rates":[{"base":"USD","quote":"SAR","type":"reference","value":3.75},{"base":"EUR","quote":"SAR","type":"reference","value":4.5},{"base":"GBP","quote":"SAR","type":"reference","value":5.0}]}';

    // Home-per-foreign table: the base column is the home currency (NZD).
    private const RBNZ_BODY = '{"bank":"rbnz","rate_date":"2026-10-29","age_days":0,"stale":false,"rates":[{"base":"NZD","quote":"USD","type":"reference","value":0.5},{"base":"NZD","quote":"AUD","type":"reference","value":0.8}]}';

    private DriverHarness $harness;
    private AllRatesToday $driver;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
        $driver        = $this->harness->make('allratestoday');
        $this->assertInstanceOf(AllRatesToday::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function access_key_is_rejected(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('AllRatesToday open endpoints do not take an API key.');
        $this->driver->accessKey('any');
    }

    #[Test]
    public function can_get_latest_ecb_rates(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::ECB_BODY));

        $result = $this->driver->get([Currency::USD, Currency::GBP]);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('EUR', $result->getBaseCurrency());
        $this->assertSame('2026-10-28', $result->getDate());
        $this->assertSame('1.1133', (string) $result->rate(Currency::USD));
        $this->assertSame('0.8713', (string) $result->rate(Currency::GBP));
        $this->assertArrayNotHasKey('JPY', $result->all());

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertSame('https://allratestoday.com/api/open/latest/EUR', $uri);
    }

    #[Test]
    public function base_currency_goes_into_the_path_and_symbols_are_picked_client_side(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(str_replace('"base_code":"EUR"', '"base_code":"USD"', self::ECB_BODY)));

        $result = $this->driver->from(Currency::USD)->get();

        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertCount(4, $result->all());

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertSame('https://allratestoday.com/api/open/latest/USD', $uri);
        $this->assertStringNotContainsString('symbols=', $uri);
    }

    #[Test]
    public function a_symbol_missing_from_the_table_is_an_error(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::ECB_BODY));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('SAR is not in the ECB table published by AllRatesToday.');
        $this->driver->get(Currency::SAR);
    }

    #[Test]
    public function an_error_envelope_is_wrapped(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok('{"result":"error","error":"Unsupported base currency"}'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unsupported base currency');
        $this->driver->get();
    }

    #[Test]
    public function bank_table_quoted_in_home_currency_is_rebased_on_the_source(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::SAMA_BODY));

        $result = $this->driver->bank('sama')->from(Currency::SAR)->to([Currency::USD, Currency::EUR])->get();

        $this->assertSame('sama', $this->driver->getBank());
        $this->assertSame('SAR', $result->getBaseCurrency());
        $this->assertSame('2026-10-28', $result->getDate());
        $this->assertSame('0.26666667', (string) $result->rate(Currency::USD));
        $this->assertSame('0.22222222', (string) $result->rate(Currency::EUR));

        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertSame('https://allratestoday.com/api/open/central-bank/sama', $uri);
    }

    #[Test]
    public function bank_table_cross_rates_use_the_source_currency(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::SAMA_BODY));

        $result = $this->driver->config('bank', 'SAMA')->from(Currency::USD)->to(Currency::GBP)->get();

        $this->assertSame('sama', $this->driver->getBank());
        $this->assertSame('0.75', (string) $result->rate(Currency::GBP));
    }

    #[Test]
    public function bank_table_quoted_per_home_unit_is_inverted(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::RBNZ_BODY));

        $result = $this->driver->bank('rbnz')->from(Currency::NZD)->get();

        $this->assertSame('1', (string) $result->rate(Currency::NZD));
        $this->assertSame('0.5', (string) $result->rate(Currency::USD));
        $this->assertSame('0.8', (string) $result->rate(Currency::AUD));

        $usd = $this->harness->make('allratestoday');
        $this->assertInstanceOf(AllRatesToday::class, $usd);
        $this->harness->http->enqueue(JsonResponse::ok(self::RBNZ_BODY));
        $this->assertSame('2', (string) $usd->bank('rbnz')->from(Currency::USD)->to(Currency::NZD)->get()->rate(Currency::NZD));
    }

    #[Test]
    public function bank_without_the_source_currency_is_an_error(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::SAMA_BODY));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Central bank sama does not publish a JPY rate.');
        $this->driver->bank('sama')->from(Currency::JPY)->get();
    }

    #[Test]
    public function empty_bank_table_is_an_error(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok('{"bank":"sama","rate_date":"2026-10-28","rates":[]}'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('AllRatesToday returned no rates for central bank sama.');
        $this->driver->bank('sama')->get();
    }

    #[Test]
    public function clearing_the_bank_returns_to_the_ecb_table(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::ECB_BODY));

        $this->driver->bank('sama')->bank(null)->get();

        $this->assertNull($this->driver->getBank());
        $uri = (string) $this->harness->http->lastRequest()?->getUri();
        $this->assertSame('https://allratestoday.com/api/open/latest/EUR', $uri);
    }

    #[Test]
    public function historical_rates_need_an_api_key(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('dated rates need an API key');
        $this->driver->historical(new DateTimeImmutable('2020-04-01'), [Currency::USD]);
    }

    #[Test]
    public function convert_uses_latest_rates(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(self::ECB_BODY));

        $result = $this->driver->convert(10.0, Currency::EUR, Currency::USD);

        $this->assertSame('1.1133', (string) $result->rate(Currency::USD));
        $this->assertSame('11.133', (string) $result->convert(10, Currency::EUR, Currency::USD));
    }

    #[Test]
    public function convert_with_a_date_needs_an_api_key(): void
    {
        $this->expectException(ApiException::class);
        $this->driver->convert(10.0, Currency::EUR, Currency::USD, new DateTimeImmutable('2020-04-01'));
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
