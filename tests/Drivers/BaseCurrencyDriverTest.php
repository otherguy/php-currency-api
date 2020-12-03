<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\BaseCurrencyDriver;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * BaseCurrencyDriverTest
 */
class BaseCurrencyDriverTest extends TestCase
{
  /** @var BaseCurrencyDriver */
  private $baseCurrencyDriver;

  private $valid_json_response = '{ "success": true, "base": "USD", "date": "2019-06-11", "rates": { "JPY": 107.346001, "EUR": 0.813399 } }';

  protected function setUp(): void
  {
    $mock = new MockHandler([
      new Response(200, [], $this->valid_json_response),
      new Response(404, [], '404 - Not Found'),
      new Response(200, [], 'Cannot reach upstream currency data server!'),
    ]);

    $this->baseCurrencyDriver = DriverFactory::make('mock', new Client(['handler' => HandlerStack::create($mock)]));
  }

  /** @test */
  public function init_will_properly_set_parameters()
  {
    $this->baseCurrencyDriver->source(Symbol::ANG)->currencies([Symbol::DKK, Symbol::USD]);
    $this->assertEquals([Symbol::DKK, Symbol::USD], $this->baseCurrencyDriver->getSymbols());
    $this->assertEquals(Symbol::ANG, $this->baseCurrencyDriver->getBaseCurrency());
  }

  /** @test */
  public function will_properly_switch_to_https()
  {
    $this->assertEquals('http', $this->baseCurrencyDriver->getProtocol());
    $this->assertEquals('https', $this->baseCurrencyDriver->secure()->getProtocol());
  }

  /** @test */
  public function from_sets_base_currency()
  {
    $this->assertNotEquals(Symbol::BTC, $this->baseCurrencyDriver->getBaseCurrency());
    $this->assertEquals(Symbol::BTC, $this->baseCurrencyDriver->from(Symbol::BTC)->getBaseCurrency());
  }

  /** @test */
  public function to_sets_target_currency()
  {
    $this->assertIsArray($this->baseCurrencyDriver->getSymbols());
    $this->assertCount(2, $this->baseCurrencyDriver->to([Symbol::BTC, Symbol::LTL])->getSymbols());
    $this->assertEquals([Symbol::BTC, Symbol::LTL], $this->baseCurrencyDriver->to([Symbol::BTC, Symbol::LTL])->getSymbols());
  }

  /** @test */
  public function setters_are_fluent()
  {
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->source(Symbol::BTC));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->from(Symbol::BTC));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->amount(12));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->to(Symbol::LTL));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->currencies(Symbol::LTL));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->secure());
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->config('test', 'value'));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->accessKey('access key'));
    $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver->date(time()));
  }

  /** @test */
  public function can_set_and_retrieve_date()
  {
    $this->assertEquals('2019-06-11', $this->baseCurrencyDriver->date(1560293762)->getDate());
    $this->assertEquals('2019-06-11', $this->baseCurrencyDriver->date('2019-06-11')->getDate());
    $this->assertEquals('2019-06-11', $this->baseCurrencyDriver->date(DateTime::createFromFormat('d.m.Y', '11.6.2019'))->getDate());
  }

  /** @test */
  public function can_perform_api_requests()
  {
    $response = $this->baseCurrencyDriver->apiRequest('test');

    $this->assertIsArray($response);
    $this->assertIsArray($response['rates']);
    $this->assertEquals('2019-06-11', $response['date']);
    $this->assertEquals(Symbol::USD, $response['base']);

    try {
      $this->baseCurrencyDriver->apiRequest('fail');
    } catch (ApiException $exception) {
      $this->assertInstanceOf(ApiException::class, $exception);
      $this->assertEquals(404, $exception->getCode());
      $this->assertInstanceOf(MockCurrencyDriver::class, $this->baseCurrencyDriver);
    }

    try {
      $this->baseCurrencyDriver->apiRequest('nojson');
    } catch (ApiException $exception) {
      $this->assertInstanceOf(ApiException::class, $exception);
      $this->assertEquals(JSON_ERROR_SYNTAX, $exception->getCode());
      $this->assertEquals('Syntax error', $exception->getMessage());
    }
  }
}
