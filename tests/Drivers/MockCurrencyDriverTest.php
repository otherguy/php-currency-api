<?php

use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\BaseCurrencyDriver;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * MockCurrencyDriverTest
 */
class MockCurrencyDriverTest extends TestCase
{

  /** @var BaseCurrencyDriver */
  private $mockCurrencyDriver;

  protected function setUp(): void
  {
    $this->mockCurrencyDriver = DriverFactory::make('mock');
  }

  /** @test */
  public function can_get_latest_rates()
  {
    $this->assertInstanceOf(ConversionResult::class, $this->mockCurrencyDriver->get());
  }

  /** @test */
  public function can_get_historical_rates()
  {
    $this->assertInstanceOf(ConversionResult::class, $this->mockCurrencyDriver->historical('2015-01-01'));
  }

  /** @test */
  public function can_convert_currencies()
  {
    $this->assertEquals(12.34, $this->mockCurrencyDriver->convert(1, Symbol::USD, Symbol::EUR));
  }
}
