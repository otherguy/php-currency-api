<?php namespace Otherguy\Currency\Drivers;

use DateTime;

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
  public function source(string $baseCurrency): DriverInterface;

  /**
   * @param array $symbols
   *
   * @return self
   */
  public function currencies(array $symbols): DriverInterface;

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  public function amount($amount): DriverInterface;

  /**
   * @param string|DateTime $date
   *
   * @return self
   */
  public function date($date): DriverInterface;

  /**
   * @param string|DateTime $startDate
   *
   * @return self
   */
  public function start_date($startDate): DriverInterface;

  /**
   * @param string|DateTime $endDate
   *
   * @return self
   */
  public function end_date($endDate): DriverInterface;

  /**
   * @param string|DateTime $startDate
   * @param string|DateTime $endDate
   *
   * @return self
   */
  public function between($startDate, $endDate): DriverInterface;

  /**
   * @return array
   */
  public function getSymbols(): array;

  /**
   * @param null|string $forCurrency
   *
   * @return array
   */
  public function get(string $forCurrency = null): array;

  /**
   * @param string               $fromCurrency
   * @param string               $toCurrency
   * @param double|integer|float $amount
   *
   * @return array
   */
  public function convert(string $fromCurrency = null, string $toCurrency = null, $amount = null): array;

  /**
   * @param string|DateTime $date
   * @param null|string     $forCurrency
   *
   * @return array
   */
  public function historical($date = null, string $forCurrency = null): array;

  /**
   * Sets the API key to use.
   *
   * @param string $accessKey Your API key.
   *
   * @return DriverInterface
   */
  public function setAccessKey(string $accessKey): DriverInterface;

  /**
   * Secures all HTTP requests by switching to HTTPS.
   *
   * Note: Most free APIs do not support this!
   *
   * @return self
   */
  public function secure(): DriverInterface;

  /**
   * Returns the protocol that is currently being used.
   *
   * @return string
   */
  public function getProtocol(): string;

  /**
   * Returns the API URL to use.
   *
   * @param string $endpoint
   *
   * @return string
   */
  public function getAPIUrl(string $endpoint): string;

  /**
   * @return string
   */
  public function getBaseCurrency(): string;
}
