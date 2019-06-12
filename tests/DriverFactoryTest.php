<?php

use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\CurrencyDriverContract;
use Otherguy\Currency\Exceptions\DriverNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * DriverFactoryTest
 */
class DriverFactoryTest extends TestCase
{
  /** @test */
  public function can_init_driver()
  {
    $this->assertInstanceOf(CurrencyDriverContract::class, DriverFactory::make('mock'));
  }

  /** @test */
  public function will_throw_exception_if_using_invalid_driver()
  {
    $this->expectException(DriverNotFoundException::class);
    DriverFactory::make('nonexistent-currency-api-driver');
  }

  /** @test */
  public function can_get_list_of_drivers()
  {
    $this->assertIsArray(DriverFactory::getDrivers());
    $this->assertContains('mock', DriverFactory::getDrivers());
  }
}
