<?php namespace Otherguy\Currency;

use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\ClientInterface;
use Otherguy\Currency\Drivers\CurrencyLayer;
use Otherguy\Currency\Drivers\CurrencyDriverContract;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Drivers\OpenExchangeRates;
use Otherguy\Currency\Drivers\ExchangeRatesApi;
use Otherguy\Currency\Exceptions\DriverNotFoundException;

/**
 * Class DriverFactory
 *
 * @package Otherguy\Currency
 */
class DriverFactory
{
  protected const DRIVERS = [
    'mock'              => MockCurrencyDriver::class,
    'fixerio'           => FixerIo::class,
    'currencylayer'     => CurrencyLayer::class,
    'openexchangerates' => OpenExchangeRates::class,
    'exchangeratesapi'  => ExchangeRatesApi::class,
  ];

  /**
   * @param string               $name
   * @param ClientInterface|null $client
   *
   * @return CurrencyDriverContract
   *
   * @throws DriverNotFoundException
   */
  public static function make(string $name, ClientInterface $client = null): CurrencyDriverContract
  {
    if (!isset(static::DRIVERS[$name])) {
      throw new DriverNotFoundException("{$name} is not a valid driver.");
    }

    $class = static::DRIVERS[$name];

    // If no client is specified, create a HTTPClient instance.
    $client = $client == null ? new HTTPClient() : $client;
    return new $class($client);
  }

  /**
   * Get all of the available drivers.
   *
   * @return array
   */
  public static function getDrivers()
  {
    return array_keys(self::DRIVERS);
  }
}
