<?php

use Otherguy\Currency\API;
use Otherguy\Currency\Drivers\DriverInterface;
use Otherguy\Currency\Exceptions\DriverNotFoundException;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\TestCase;

/**
 * DriverTest
 */
class DriverTest extends TestCase
{
  /** @test */
  public function can_init_driver()
  {
    $this->assertInstanceOf(DriverInterface::class, API::make('mock'));
  }

  /** @test */
  public function init_will_properly_set_parameters()
  {
    $api = API::make('mock')->source(Symbol::ANG)->currencies([Symbol::DKK, Symbol::USD]);
    $this->assertEquals([Symbol::DKK, Symbol::USD], $api->getSymbols());
    $this->assertEquals(Symbol::ANG, $api->getBaseCurrency());
  }

  /** @test */
  public function will_throw_exception_if_using_invalid_driver()
  {
    $this->expectException(DriverNotFoundException::class);

    API::make('nonexistent-currency-api-driver');
  }

  /** @test */
  public function will_properly_switch_to_https()
  {
    $api = API::make('mock');
    $this->assertEquals($api->getProtocol(), 'http');
    $api->secure();
    $this->assertEquals($api->getProtocol(), 'https');
  }
}
