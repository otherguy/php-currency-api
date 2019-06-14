<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\OpenExchangeRates;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * OpenExchangeRatesTest
 */
class OpenExchangeRatesTest extends TestCase
{
  /** @var OpenExchangeRates */
  private $openExchangeRates;

  private $mockHandler;

  protected function setUp()
  {
    $this->mockHandler       = new MockHandler();
    $this->openExchangeRates = DriverFactory::make('openexchangerates', new Client(['handler' => $this->mockHandler]));
  }

  /** @test */
  public function can_set_app_id()
  {
    $this->assertInstanceOf(OpenExchangeRates::class, $this->openExchangeRates->accessKey('7b23e3e4706c074e2665caf25e823e88'));
  }

  /** @test */
  public function can_get_latest_rates()
  {
    // Response from https://docs.openexchangerates.org
    $this->mockHandler->append(new Response(200, [], '{"disclaimer":"https://openexchangerates.org/terms/","license":"https://openexchangerates.org/license/","timestamp":1449877801,"base":"USD","rates":{"AED":3.672538,"AFN":66.809999,"ALL":125.716501,"AMD":484.902502,"ANG":1.788575}}'));

    $result = $this->openExchangeRates->from(Symbol::USD)->get([Symbol::AED, Symbol::AFN, Symbol::ALL, Symbol::AMD, Symbol::ANG]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::USD, $result->getBaseCurrency());
    $this->assertEquals('2015-12-11', $result->getDate());
    $this->assertEquals(3.672538, $result->rate(Symbol::AED));
    $this->assertEquals(66.809999, $result->rate(Symbol::AFN));
    $this->assertEquals(125.716501, $result->rate(Symbol::ALL));
    $this->assertEquals(484.902502, $result->rate(Symbol::AMD));
    $this->assertEquals(1.788575, $result->rate(Symbol::ANG));
  }


  /** @test */
  public function can_get_historical_rates()
  {
    // Response from https://docs.openexchangerates.org
    $this->mockHandler->append(new Response(200, [], '{"disclaimer":"https://openexchangerates.org/terms/","license":"https://openexchangerates.org/license/","timestamp":982342800,"base":"USD","rates":{"AED":3.67246,"ALL":144.529793,"ANG":1.79}}'));

    $result = $this->openExchangeRates->from(Symbol::USD)->historical('2001-02-16', [Symbol::AED, Symbol::AED, Symbol::ANG]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::USD, $result->getBaseCurrency());
    $this->assertEquals('2001-02-16', $result->getDate());

    $this->assertEquals(3.67246, $result->rate(Symbol::AED));
    $this->assertEquals(144.529793, $result->rate(Symbol::ALL));
    $this->assertEquals(1.79, $result->rate(Symbol::ANG));
  }

  /** @test */
  public function fails_to_get_historical_rates_if_date_not_set()
  {
    $this->expectException(ApiException::class);
    $this->openExchangeRates->from(Symbol::USD)->to(Symbol::EUR)->historical();
  }

  /** @test */
  public function can_convert_currency_amounts()
  {
    // Response from https://docs.openexchangerates.org
    $this->mockHandler->append(new Response(200, [], '{"disclaimer":"https://openexchangerates.org/terms/","license":"https://openexchangerates.org/license/","request":{"query":"/convert/19999.95/GBP/EUR","amount":19999.95,"from":"GBP","to":"EUR"},"meta":{"timestamp":1449885661,"rate":1.383702},"response":27673.975864}'));

    $result = $this->openExchangeRates->convert(19999.95, Symbol::GBP, Symbol::EUR, '2015-12-12');
    $this->assertEquals(27673.975864, $result);
  }

  /** @test */
  public function can_handle_response_failures()
  {
    // Response from https://docs.openexchangerates.org
    $this->mockHandler->append(new Response(200, [], '{"error":true,"status":401,"message":"invalid_app_id","description":"Invalid App ID provided - please sign up at https://openexchangerates.org/signup, or contact support@openexchangerates.org."}'));

    $this->expectException(ApiException::class);
    $this->openExchangeRates->from(Symbol::USD)->to(Symbol::LTL)->get();
  }
}
