<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * FixerIoTest
 */
class FixerIoTest extends TestCase
{
  /** @var FixerIo */
  private $fixerIo;

  private $mockHandler;

  protected function setUp()
  {
    $this->mockHandler = new MockHandler();
    $this->fixerIo     = DriverFactory::make('fixerio', new Client(['handler' => $this->mockHandler]));
  }

  /** @test */
  public function can_get_latest_rates()
  {
    // Response from https://fixer.io/documentation
    $this->mockHandler->append(new Response(200, [], '{ "success": true, "timestamp": 1519296206, "base": "USD", "date": "2018-02-22", "rates": { "GBP": 0.72007, "JPY": 107.346001, "EUR": 0.813399 } }'));

    $result = $this->fixerIo->from(Symbol::USD)->get([Symbol::GBP, Symbol::JPY, Symbol::EUR]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::USD, $result->getBaseCurrency());
    $this->assertEquals('2018-02-22', $result->getDate());
    $this->assertEquals(0.72007, $result->rate(Symbol::GBP));
    $this->assertEquals(107.346001, $result->rate(Symbol::JPY));
    $this->assertEquals(0.813399, $result->rate(Symbol::EUR));
  }


  /** @test */
  public function can_get_historical_rates()
  {
    // Response from https://fixer.io/documentation
    $this->mockHandler->append(new Response(200, [], '{ "success": true, "historical": true, "date": "2013-12-24", "timestamp": 1387929599, "base": "GBP", "rates": { "USD": 1.636492, "EUR": 1.196476, "CAD": 1.739516 } }'));

    $result = $this->fixerIo->from(Symbol::GBP)->historical('2013-12-24', [Symbol::USD, Symbol::EUR, Symbol::CAD]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::GBP, $result->getBaseCurrency());
    $this->assertEquals('2013-12-24', $result->getDate());

    $this->assertEquals(1.636492, $result->rate(Symbol::USD));
    $this->assertEquals(1.196476, $result->rate(Symbol::EUR));
    $this->assertEquals(1.739516, $result->rate(Symbol::CAD));
  }

  /** @test */
  public function fails_to_get_historical_rates_if_date_not_set()
  {
    $this->expectException(ApiException::class);
    $this->fixerIo->from(Symbol::USD)->to(Symbol::EUR)->historical();
  }

  /** @test */
  public function can_convert_currency_amounts()
  {
    // Response from https://fixer.io/documentation
    $this->mockHandler->append(new Response(200, [], '{ "success": true, "query": { "from": "GBP", "to": "JPY", "amount": 25 }, "info": { "timestamp": 1519328414, "rate": 148.972231 }, "historical": "true", "date": "2018-02-22",  "result": 3724.305775 }'));

    $result = $this->fixerIo->from(Symbol::USD)->date('2018-02-22')->convert(25, Symbol::GBP, Symbol::JPY);
    $this->assertEquals(3724.305775, $result);
  }

  /** @test */
  public function can_handle_response_failures()
  {
    // Response from https://fixer.io/documentation
    $this->mockHandler->append(new Response(200, [], '{ "success": false, "error": { "code": 104, "info": "Your monthly API request volume has been reached. Please upgrade your plan." } }'));

    $this->expectException(ApiException::class);
    $this->fixerIo->from(Symbol::USD)->to(Symbol::LTL)->get();
  }
}
