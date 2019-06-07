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
   *
   * @return DriverInterface
   *
   * @throws DriverNotFoundException
   */
  public static function make(string $driver): DriverInterface
  {
    return (DriverFactory::make($driver));
  }
}
