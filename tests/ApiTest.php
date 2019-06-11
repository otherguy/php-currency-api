<?php

use Otherguy\Currency\API;
use Otherguy\Currency\Results\ConversionResult;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 */
class ApiTest extends TestCase
{
  /** @test */
  public function can_get_latest_rates()
  {
    $this->assertInstanceOf(ConversionResult::class, API::make('mock')->get());
  }

  /** @test */
  public function can_get_historical_rates()
  {
    $this->assertInstanceOf(ConversionResult::class, API::make('mock')->historical('2015-01-01'));
  }
}
