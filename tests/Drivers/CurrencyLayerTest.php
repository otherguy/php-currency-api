<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\CurrencyLayer;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * CurrencyLayerTest
 */
class CurrencyLayerTest extends TestCase
{
  /** @var CurrencyLayer */
  private $currencyLayer;

  private $mockHandler;

  protected function setUp()
  {
    $this->mockHandler   = new MockHandler();
    $this->currencyLayer = DriverFactory::make('currencylayer', new Client(['handler' => $this->mockHandler]));
  }

  /** @test */
  public function can_get_latest_rates()
  {
    // Response from https://currencylayer.com/documentation
    $this->mockHandler->append(new Response(200, [], '{"success":true,"terms":"https://currencylayer.com/terms","privacy":"https://currencylayer.com/privacy","timestamp":1432400348,"source":"USD","quotes":{"USDAUD":1.278342,"USDEUR":1.278342,"USDGBP":0.908019,"USDPLN":3.731504}}'));

    $result = $this->currencyLayer->from(Symbol::USD)->get([Symbol::AUD, Symbol::EUR, Symbol::GBP, Symbol::PLN]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::USD, $result->getBaseCurrency());
    $this->assertEquals('2015-05-23', $result->getDate());

    $this->assertEquals(1.278342, $result->rate(Symbol::AUD));
    $this->assertEquals(1.278342, $result->rate(Symbol::EUR));
    $this->assertEquals(0.908019, $result->rate(Symbol::GBP));
    $this->assertEquals(3.731504, $result->rate(Symbol::PLN));
  }


  /** @test */
  public function can_get_historical_rates()
  {
    // Response from https://currencylayer.com/documentation
    $this->mockHandler->append(new Response(200, [], '{"success":true,"terms":"https://currencylayer.com/terms","privacy":"https://currencylayer.com/privacy","historical":true,"date":"2005-02-01","timestamp":1107302399,"source":"USD","quotes":{"USDAED":3.67266,"USDALL":96.848753,"USDAMD":475.798297,"USDANG":1.790403,"USDARS":2.918969,"USDAUD":1.293878}}'));

    $result = $this->currencyLayer->from(Symbol::USD)->historical('2005-02-01', [Symbol::AED, Symbol::ALL, Symbol::AMD, Symbol::ANG, Symbol::ARS, Symbol::AUD]);

    $this->assertInstanceOf(ConversionResult::class, $result);

    $this->assertEquals(Symbol::USD, $result->getBaseCurrency());
    $this->assertEquals('2005-02-01', $result->getDate());

    $this->assertEquals(3.67266, $result->rate(Symbol::AED));
    $this->assertEquals(96.848753, $result->rate(Symbol::ALL));
    $this->assertEquals(475.798297, $result->rate(Symbol::AMD));
    $this->assertEquals(1.790403, $result->rate(Symbol::ANG));
    $this->assertEquals(2.918969, $result->rate(Symbol::ARS));
    $this->assertEquals(1.293878, $result->rate(Symbol::AUD));
  }

  /** @test */
  public function fails_to_get_historical_rates_if_date_not_set()
  {
    $this->expectException(ApiException::class);
    $this->currencyLayer->from(Symbol::USD)->to(Symbol::EUR)->historical();
  }

  /** @test */
  public function can_convert_currency_amounts()
  {
    // Response from https://currencylayer.com/documentation
    $this->mockHandler->append(new Response(200, [], '{"success":true,"terms":"https://currencylayer.com/terms","privacy":"https://currencylayer.com/privacy","query":{"from":"USD","to":"GBP","amount":10},"info":{"timestamp":1430068515,"quote":0.658443},"result":6.58443}'));

    $result = $this->currencyLayer->convert(10, Symbol::USD, Symbol::GBP, 1430068515);
    $this->assertEquals(6.58443, $result);
  }

  /** @test */
  public function can_handle_response_failures()
  {
    // Response from https://currencylayer.com/documentation
    $this->mockHandler->append(new Response(200, [], '{"success":false,"error":{"code":104,"info":"Your monthly usage limit has been reached. Please upgrade your subscription plan."}}'));

    $this->expectException(ApiException::class);
    $this->currencyLayer->from(Symbol::USD)->to(Symbol::LTL)->get();
  }
}
