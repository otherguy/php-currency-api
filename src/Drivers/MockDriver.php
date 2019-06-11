<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Results\ConversionResult;

/**
 * Class MockDriver
 *
 * @package Otherguy\Currency\Drivers
 */
class MockDriver extends BaseDriver implements DriverInterface
{
  protected $apiURL    = 'localhost';

  /**
   * @param string|array $forCurrency
   *
   * @return ConversionResult
   */
  function get($forCurrency = []): ConversionResult
  {
    return new ConversionResult($this->getBaseCurrency(), time(), []);
  }

  /**
   * Converts any amount in a given currency to another currency.
   *
   * @param float  $amount       The amount to convert.
   * @param string $fromCurrency The base currency.
   * @param string $toCurrency   The target currency.
   *
   * @return float The conversion result.
   */
  function convert(float $amount = null, string $fromCurrency = null, string $toCurrency = null): float
  {
    return 12.34;
  }

  /**
   * @param int|string|DateTime $date
   * @param string|array        $forCurrency
   *
   * @return ConversionResult
   */
  function historical($date = null, $forCurrency = []): ConversionResult
  {
    return new ConversionResult($this->getBaseCurrency(), time(), []);
  }

  /**
   * Returns an array of default HTTP params.
   *
   * @return array
   */
  public function getDefaultParams() : array
  {
    return [];
  }
}
