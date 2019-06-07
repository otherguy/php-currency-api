<?php namespace Otherguy\Currency;

use Otherguy\Currency\Drivers\DriverInterface;
use Otherguy\Currency\Exceptions\DriverNotFoundException;

/**
 * Class API
 *
 * @package Otherguy\Currency
 */
class API
{
  /**
   * @param string $driver
   * @param string $base
   * @param array  $symbols
   *
   * @return DriverInterface
   *
   * @throws DriverNotFoundException
   */
  public static function make(string $driver, string $base = 'USD', array $symbols = []): DriverInterface
  {
    return (DriverFactory::make($driver))
      ->source($base)
      ->currencies($symbols);
  }
}
