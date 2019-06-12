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
  function source(string $baseCurrency): CurrencyDriverContract;
  function from(string $baseCurrency): CurrencyDriverContract; // Alias

  /**
   * @param string|array $symbols
   *
   * @return self
   */
  function currencies($symbols = []): CurrencyDriverContract;
  function to($symbols = []): CurrencyDriverContract; // Alias

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  function amount($amount): CurrencyDriverContract;

  /**
   * @param int|string|DateTime $date
   *
   * @return self
   */
  function date($date): CurrencyDriverContract;

  /**
   * Retrieve the date in a 'YYYY-mm-dd' format.
   *
   * @return string
   */
  function getDate(): string;

  /**
   * @return array
   */
  function getSymbols(): array;

  /**
   * @param string|array $forCurrency
   *
   * @return ConversionResult
   */
  function get($forCurrency = []): ConversionResult;

  /**
   * Converts any amount in a given currency to another currency.
   *
   * @param float  $amount       The amount to convert.
   * @param string $fromCurrency The base currency.
   * @param string $toCurrency   The target currency.
   *
   * @return float The conversion result.
   */
  function convert(float $amount = null, string $fromCurrency = null, string $toCurrency = null): float;

  /**
   * @param int|string|DateTime $date
   * @param string|array        $forCurrency
   *
   * @return ConversionResult
   */
  function historical($date = null, $forCurrency = []): ConversionResult;

  /**
   * @return string
   */
  function getBaseCurrency(): string;

  /**
   * Set a config parameter.
   *
   * @param string $key
   * @param string $value
   *
   * @return self
   */
  function config(string $key, string $value): CurrencyDriverContract;

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
  function accessKey(string $accessKey): CurrencyDriverContract;

  /**
   * Secures all HTTP requests by switching to HTTPS.
   *
   * Note: Most free APIs do not support this!
   *
   * @return self
   */
  function secure(): CurrencyDriverContract;

  /**
   * Returns the protocol that is currently being used.
   *
   * @return string
   */
  function getProtocol(): string;

  /**
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|bool The response as decoded JSON.
   */
  function apiRequest(string $endpoint, string $method = 'GET');
}
