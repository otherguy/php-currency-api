<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;

/**
 * Interface DriverInterface
 *
 * @package Otherguy\Currency\Drivers
 */
interface DriverInterface
{
  /**
   * @param string $baseCurrency
   *
   * @return self
   */
  function source(string $baseCurrency): DriverInterface;
  function from(string $baseCurrency): DriverInterface; // Alias

  /**
   * @param string|array $symbols
   *
   * @return self
   */
  function currencies($symbols = []): DriverInterface;
  function to($symbols = []): DriverInterface;

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  function amount($amount): DriverInterface;

  /**
   * @param int|string|DateTime $date
   *
   * @return self
   */
  function date($date): DriverInterface;

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
   * Sets the API key to use.
   *
   * @param string $accessKey Your API key.
   *
   * @return DriverInterface
   */
  function accessKey(string $accessKey): DriverInterface;

  /**
   * Secures all HTTP requests by switching to HTTPS.
   *
   * Note: Most free APIs do not support this!
   *
   * @return self
   */
  function secure(): DriverInterface;

  /**
   * Returns the protocol that is currently being used.
   *
   * @return string
   */
  function getProtocol(): string;

  /**
   * Returns an array of default HTTP params.
   *
   * @return array
   */
  function getDefaultParams(): array;

  /**
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param array  $params   The URL query parameters.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|string|bool The response as decoded JSON.
   */
  function apiRequest(string $endpoint, array $params = [], string $method = 'GET');
}
