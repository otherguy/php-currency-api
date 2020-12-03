<?php

use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;
use PHPUnit\Framework\TestCase;
use Otherguy\Currency\Symbol;

/**
 * ConversionResultTest
 */
class ConversionResultTest extends TestCase
{
  /** @var ConversionResult */
  private $classUnderTest;

  /**
   *
   */
  protected function setUp(): void
  {
    $this->classUnderTest = new ConversionResult(Symbol::USD, 1560293762, [
      'EUR' => 0.88,
      'THB' => 31.27,
    ]);
  }

  /** @test */
  public function construct_will_properly_set_parameters()
  {
    $this->assertEquals(Symbol::USD, $this->classUnderTest->getBaseCurrency());
    $this->assertEquals('2019-06-11', $this->classUnderTest->getDate());

    $result = new ConversionResult(Symbol::USD, '1936-07-21', [
      'CNY' => 1.12,
    ]);

    $this->assertEquals('1936-07-21', $result->getDate());

    $result = new ConversionResult(Symbol::EUR, DateTime::createFromFormat('d.m.Y', '5.10.1990'), [
      'LTL' => 3.45280,
    ]);

    $this->assertEquals('1990-10-05', $result->getDate());
    $this->assertEquals(Symbol::EUR, $result->getBaseCurrency());
  }

  /** @test */
  public function returns_all_conversion_rates()
  {
    $this->assertCount(3, $this->classUnderTest->all());
    $this->assertArrayHasKey(Symbol::USD, $this->classUnderTest->all());
    $this->assertArrayHasKey(Symbol::EUR, $this->classUnderTest->all());
    $this->assertArrayHasKey(Symbol::THB, $this->classUnderTest->all());
    $this->assertEquals(1, $this->classUnderTest->all()[Symbol::USD]);
    $this->assertEquals(31.27, $this->classUnderTest->all()[Symbol::THB]);
  }

  /** @test */
  public function fails_to_convert_if_target_currency_does_not_exist()
  {
    $this->expectException(CurrencyException::class);
    $this->classUnderTest->convert(2, Symbol::EUR, Symbol::BTC);
  }

  /** @test */
  public function fails_to_convert_if_source_currency_does_not_exist()
  {
    $this->expectException(CurrencyException::class);
    $this->classUnderTest->convert(2, Symbol::BTC, Symbol::EUR);
  }

  /** @test */
  public function can_convert_between_currencies()
  {
    $result = $this->classUnderTest->convert(2, Symbol::EUR, Symbol::THB);
    $this->assertEqualsWithDelta(71.06, $result, 0.1);
  }

  /** @test */
  public function fails_to_retrieve_rate_if_currency_does_not_exist()
  {
    $this->expectException(CurrencyException::class);
    $this->classUnderTest->rate(Symbol::BTC);
  }

  /** @test */
  public function retrieves_currency_conversion_rate()
  {
    $this->assertEquals(31.27, $this->classUnderTest->rate(Symbol::THB));
    $this->assertEquals(0.88, $this->classUnderTest->rate(Symbol::EUR));
  }

  /** @test */
  public function fails_to_change_base_currency_if_currency_does_not_exist()
  {
    $this->expectException(CurrencyException::class);
    $this->classUnderTest->setBaseCurrency(Symbol::BTC);
  }

  /** @test */
  public function can_reset_base_currency()
  {
    $this->classUnderTest->setBaseCurrency(Symbol::USD);
    $this->assertEquals(0.88, $this->classUnderTest->rate(Symbol::EUR));
    $this->assertEquals(1, $this->classUnderTest->rate(Symbol::USD));
  }

  /** @test */
  public function can_change_base_currency()
  {
    $this->classUnderTest->setBaseCurrency(Symbol::EUR);
    $this->assertEqualsWithDelta(1.14, $this->classUnderTest->rate(Symbol::USD), 0.1);
    $this->assertEquals(1, $this->classUnderTest->rate(Symbol::EUR));

    $this->assertEquals(1, $this->classUnderTest->convert(0.88, Symbol::EUR, Symbol::USD));
    $this->assertEquals(1, $this->classUnderTest->convert(31.27, Symbol::THB, Symbol::USD));
  }
}
