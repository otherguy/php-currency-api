<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Results\ConversionResult;

/**
 * Interface DriverInterface
 *
 * @package Otherguy\Currency\Drivers
 */
interface CurrencyDriverContract
{
  /**
   * @param string $baseCurrency
   *
   * @return self
   */
  public function source(string $baseCurrency): CurrencyDriverContract;
  public function from(string $baseCurrency): CurrencyDriverContract; // Alias

  /**
   * @param string|array $symbols
   *
   * @return self
   */
  public function currencies($symbols = []): CurrencyDriverContract;
  public function to($symbols = []): CurrencyDriverContract; // Alias

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  public function amount($amount): CurrencyDriverContract;

  /**
   * @param int|string|DateTime $date
   *
   * @return self
   */
  public function date($date): CurrencyDriverContract;

  /**
   * Returns the date in 'YYYY-mm-dd' format or null if not set.
   *
   * @return string|null
   */
  public function getDate(): ?string;

  /**
   * @return array
   */
  public function getSymbols(): array;

  /**
   * @param string|array $forCurrency
   *
   * @return ConversionResult
   */
  public function get($forCurrency = []): ConversionResult;

  /**
   * Converts any amount in a given currency to another currency.
   *
   * @param float               $amount       The amount to convert.
   * @param string              $fromCurrency The base currency.
   * @param string              $toCurrency   The target currency.
   * @param int|string|DateTime $date         The date to get the conversion rate for.
   *
   * @return float The conversion result.
   */
  public function convert(float $amount = null, string $fromCurrency = null, string $toCurrency = null, $date = null): float;

  /**
   * @param int|string|DateTime $date
   * @param string|array        $forCurrency
   *
   * @return ConversionResult
   */
  public function historical($date = null, $forCurrency = []): ConversionResult;

  /**
   * @return string
   */
  public function getBaseCurrency(): string;

  /**
   * Set a config parameter.
   *
   * @param string $key
   * @param string $value
   *
   * @return self
   */
  public function config(string $key, string $value): CurrencyDriverContract;

  /**
   * Sets the API key to use.
   *
   * Shortcut for config('access_key', $accessKey)
   *
   * @param string $accessKey Your API key.
   *
   * @return self
   * @see CurrencyDriverContract::config()
   *
   */
  public function accessKey(string $accessKey): CurrencyDriverContract;

  /**
   * Secures all HTTP requests by switching to HTTPS.
   *
   * Note: Most free APIs do not support this!
   *
   * @return self
   */
  public function secure(): CurrencyDriverContract;

  /**
   * Returns the protocol that is currently being used.
   *
   * @return string
   */
  public function getProtocol(): string;

  /**
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param array  $params   The query parameters for this request.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|bool The response as decoded JSON.
   */
  public function apiRequest(string $endpoint, array $params = [], string $method = 'GET');
}
