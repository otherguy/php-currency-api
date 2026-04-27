<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Results;

use Brick\Math\BigDecimal;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConversionResultTest extends TestCase
{
    private ConversionResult $result;

    protected function setUp(): void
    {
        $this->result = new ConversionResult(Currency::USD, '2019-06-11', [
          'EUR' => 0.88,
          'THB' => 31.27,
        ]);
    }

    #[Test]
    public function construct_will_properly_set_parameters(): void
    {
        $this->assertSame('USD', $this->result->getBaseCurrency());
        $this->assertSame('2019-06-11', $this->result->getDate());

        $other = new ConversionResult(Currency::USD, '1936-07-21', ['CNY' => 1.12]);
        $this->assertSame('1936-07-21', $other->getDate());

        $third = new ConversionResult(Currency::EUR, '1990-10-05', ['LTL' => 3.45280]);
        $this->assertSame('1990-10-05', $third->getDate());
        $this->assertSame('EUR', $third->getBaseCurrency());
    }

    #[Test]
    public function returns_all_conversion_rates_including_base(): void
    {
        $rates = $this->result->all();

        $this->assertCount(3, $rates);
        $this->assertArrayHasKey('USD', $rates);
        $this->assertArrayHasKey('EUR', $rates);
        $this->assertArrayHasKey('THB', $rates);

        $this->assertTrue(BigDecimal::one()->isEqualTo($rates['USD']));
        $this->assertSame('31.27', (string) $rates['THB']);
    }

    #[Test]
    public function all_as_floats_returns_native_floats(): void
    {
        $rates = $this->result->allAsFloats();

        $this->assertSame(1.0, $rates['USD']);
        $this->assertSame(0.88, $rates['EUR']);
        $this->assertSame(31.27, $rates['THB']);
    }

    #[Test]
    public function fails_to_convert_if_target_currency_does_not_exist(): void
    {
        $this->expectException(CurrencyException::class);
        $this->result->convert(2, Currency::EUR, Currency::BTC);
    }

    #[Test]
    public function fails_to_convert_if_source_currency_does_not_exist(): void
    {
        $this->expectException(CurrencyException::class);
        $this->result->convert(2, Currency::BTC, Currency::EUR);
    }

    #[Test]
    public function can_convert_between_currencies(): void
    {
        $converted = $this->result->convert(2, Currency::EUR, Currency::THB);

        $this->assertEqualsWithDelta(71.06, $converted->toFloat(), 0.01);
    }

    #[Test]
    public function fails_to_retrieve_rate_if_currency_does_not_exist(): void
    {
        $this->expectException(CurrencyException::class);
        $this->result->rate(Currency::BTC);
    }

    #[Test]
    public function retrieves_currency_conversion_rate(): void
    {
        $this->assertSame('31.27', (string) $this->result->rate(Currency::THB));
        $this->assertSame('0.88', (string) $this->result->rate(Currency::EUR));
        $this->assertSame(31.27, $this->result->rateAsFloat(Currency::THB));
    }

    #[Test]
    public function fails_to_change_base_currency_if_currency_does_not_exist(): void
    {
        $this->expectException(CurrencyException::class);
        $this->result->setBaseCurrency(Currency::BTC);
    }

    #[Test]
    public function reset_to_original_base_currency_restores_original_rates(): void
    {
        $this->result->setBaseCurrency(Currency::EUR);
        $this->result->setBaseCurrency(Currency::USD);

        $this->assertSame('0.88', (string) $this->result->rate(Currency::EUR));
        $this->assertTrue(BigDecimal::one()->isEqualTo($this->result->rate(Currency::USD)));
    }

    #[Test]
    public function can_change_base_currency_and_convert_back_losslessly(): void
    {
        $this->result->setBaseCurrency(Currency::EUR);

        $this->assertEqualsWithDelta(1.1363, $this->result->rateAsFloat(Currency::USD), 0.001);
        $this->assertTrue(BigDecimal::one()->isEqualTo($this->result->rate(Currency::EUR)));

        $this->assertSame('EUR', $this->result->getBaseCurrency());
        $this->assertSame('USD', $this->result->originalBaseCurrency);
    }

    #[Test]
    public function convert_round_trips_via_original_base(): void
    {
        $this->assertEqualsWithDelta(
            1.0,
            $this->result->convert(0.88, Currency::EUR, Currency::USD)->toFloat(),
            0.0001,
        );

        $this->assertEqualsWithDelta(
            1.0,
            $this->result->convert(31.27, Currency::THB, Currency::USD)->toFloat(),
            0.0001,
        );
    }

    #[Test]
    public function setting_base_to_self_returns_self(): void
    {
        $this->result->setBaseCurrency(Currency::USD);
        $this->assertSame('USD', $this->result->getBaseCurrency());
        $this->assertTrue(BigDecimal::one()->isEqualTo($this->result->rate(Currency::USD)));
    }

    #[Test]
    public function constructor_accepts_string_base_currency(): void
    {
        $result = new ConversionResult('JPY', '2024-01-01', ['EUR' => '0.0061']);

        $this->assertSame('JPY', $result->getBaseCurrency());
        $this->assertSame('JPY', $result->originalBaseCurrency);
    }

    #[Test]
    public function rates_can_be_passed_as_big_decimal_already(): void
    {
        $result = new ConversionResult(Currency::USD, '2024-01-01', [
          'EUR' => BigDecimal::of('0.92'),
        ]);

        $this->assertSame('0.92', (string) $result->rate(Currency::EUR));
    }
}
