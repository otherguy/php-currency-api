<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\ExchangeRatesApi;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * RatesApiTest
 */
class ExchangeRatesApiTest extends TestCase
{
  /** @var ExchangeRatesApi */
  private $exchangeRatesApi;

  private $mockHandler;

  protected function setUp(): void
  {
    $this->mockHandler      = new MockHandler();
    $this->exchangeRatesApi = DriverFactory::make('exchangeratesapi', new Client(['handler' => $this->mockHandler]));
  }

  /** @test */
  public function fails_to_set_api_key()
  {
    $this->expectException(ApiException::class);
    $this->expectExceptionMessage('No Access Key is required for this driver!');
    $this->expectExceptionCode(400);
    $this->exchangeRatesApi->accessKey('test-access-key');
  }

  /** @test */
  public function can_get_latest_rates()
  {
    // Response from https://exchangeratesapi.io
    $this->mockHandler->append(new Response(200, [], '{"base":"EUR","rates":{"NOK":9.772,"USD":1.1289,"JPY":122.44},"date":"2019-06-13"}'));

    $result = $this->exchangeRatesApi->from(Symbol::EUR)->get([Symbol::NOK, Symbol::JPY, Symbol::USD]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::EUR, $result->getBaseCurrency());
    $this->assertEquals('2019-06-13', $result->getDate());
    $this->assertEquals(9.772, $result->rate(Symbol::NOK));
    $this->assertEquals(1.1289, $result->rate(Symbol::USD));
    $this->assertEquals(122.44, $result->rate(Symbol::JPY));
  }


  /** @test */
  public function can_get_historical_rates()
  {
    // Response from https://exchangeratesapi.io
    $this->mockHandler->append(new Response(200, [], '{"base":"GBP","rates":{"NOK":10.088752796,"CAD":1.7366601677,"USD":1.636783369,"JPY":170.6398095762,"EUR":1.1961293255},"date":"2013-12-24"}'));

    $result = $this->exchangeRatesApi->from(Symbol::GBP)->historical('2013-12-24', [Symbol::NOK, Symbol::CAD, Symbol::USD, Symbol::JPY, Symbol::EUR]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::GBP, $result->getBaseCurrency());
    $this->assertEquals('2013-12-24', $result->getDate());

    $this->assertEquals(1.636783369, $result->rate(Symbol::USD));
    $this->assertEquals(1.1961293255, $result->rate(Symbol::EUR));
    $this->assertEquals(1.7366601677, $result->rate(Symbol::CAD));
    $this->assertEquals(10.088752796, $result->rate(Symbol::NOK));
    $this->assertEquals(170.6398095762, $result->rate(Symbol::JPY));
  }

  /** @test */
  public function fails_to_get_historical_rates_if_date_not_set()
  {
    $this->expectException(ApiException::class);
    $this->exchangeRatesApi->from(Symbol::USD)->to(Symbol::EUR)->historical();
  }

  /** @test */
  public function fails_to_convert_currency_amounts()
  {
    $this->expectException(ApiException::class);
    $this->expectExceptionMessage("Endpoint 'convert' is not supported for this driver!");
    $this->expectExceptionCode(404);

    $result = $this->exchangeRatesApi->convert(25, Symbol::GBP, Symbol::JPY, '2018-02-22');
  }

  /** @test */
  public function can_handle_response_failures()
  {
    // Response from https://exchangeratesapi.io
    $this->mockHandler->append(new Response(200, [], '{"error":"Symbols \'USD,CAD,EUR,JPY,NOK,CDP\' are invalid for date 2019-06-14."}'));

    $this->expectException(ApiException::class);
    $this->expectExceptionCode(500);
    $this->exchangeRatesApi->from(Symbol::USD)->to(Symbol::LTL)->get();
  }
}
