<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Tests\Support\DriverHarness;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MockCurrencyDriverTest extends TestCase
{
    private MockCurrencyDriver $driver;

    protected function setUp(): void
    {
        $driver = (new DriverHarness())->make('mock');
        $this->assertInstanceOf(MockCurrencyDriver::class, $driver);
        $this->driver = $driver;
    }

    #[Test]
    public function returns_conversion_result_for_get(): void
    {
        $this->assertInstanceOf(ConversionResult::class, $this->driver->get());
    }

    #[Test]
    public function returns_conversion_result_for_historical(): void
    {
        $result = $this->driver->historical(new DateTimeImmutable('2015-01-01'));

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('2015-01-01', $result->getDate());
    }

    #[Test]
    public function convert_returns_conversion_result_with_target_rate(): void
    {
        $result = $this->driver->convert(1.0, Currency::USD, Currency::EUR);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertSame('USD', $result->getBaseCurrency());
        $this->assertTrue(BigDecimal::of('12.34')->isEqualTo($result->rate(Currency::EUR)));
    }

    #[Test]
    public function with_rates_seeds_get_response(): void
    {
        $this->driver->withRates(['EUR' => '0.92', 'GBP' => '0.79']);

        $result = $this->driver->get();

        $this->assertSame('0.92', (string) $result->rate(Currency::EUR));
        $this->assertSame('0.79', (string) $result->rate(Currency::GBP));
    }
}
