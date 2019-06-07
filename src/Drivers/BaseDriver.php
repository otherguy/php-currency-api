<?php namespace Otherguy\Currency\Drivers;

use DateTime;

/**
 * Class BaseDriver
 *
 * @package Otherguy\Currency\Drivers
 */
abstract class BaseDriver implements DriverInterface
{
  protected $apiURL    = 'localhost';
  protected $protocol  = 'http';
  protected $accessKey = null;
  protected $headers   = [
    'Accept'       => 'application/json',
    'Content-Type' => 'application/json',
  ];


  protected $currencies   = [];
  protected $baseCurrency = 'USD';
  protected $amount       = 0.00;
  protected $date         = null;
  protected $start_date   = null;
  protected $end_date     = null;

  /**
   * @param string $baseCurrency
   *
   * @return self
   */
  public function source(string $baseCurrency): DriverInterface
  {
    $this->baseCurrency = $baseCurrency;
    return $this;
  }

  /**
   * @param array $symbols
   *
   * @return self
   */
  public function currencies(array $symbols = []): DriverInterface
  {
    $this->currencies = (array)$symbols;
    return $this;
  }

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  public function amount($amount): DriverInterface
  {
    $this->amount = $amount;
    return $this;
  }

  /**
   * @param string|DateTime $date
   *
   * @return self
   */
  public function date($date): DriverInterface
  {
    $this->date = $date;
    return $this;
  }

  /**
   * @param string|DateTime $startDate
   *
   * @return self
   */
  public function start_date($startDate): DriverInterface
  {
    $this->start_date = $startDate;
    return $this;
  }

  /**
   * @param string|DateTime $endDate
   *
   * @return self
   */
  public function end_date($endDate): DriverInterface
  {
    $this->end_date = $endDate;
    return $this;
  }

  /**
   * @param string|DateTime $startDate
   * @param string|DateTime $endDate
   *
   * @return self
   */
  public function between($startDate, $endDate): DriverInterface
  {
    $this->start_date = $startDate;
    $this->end_date = $endDate;
    return $this;
  }

  /**
   * @return array
   */
  public function getSymbols(): array
  {
    return $this->currencies;
  }

  /**
   * @return string
   */
  public function getBaseCurrency(): string
  {
    return $this->baseCurrency;
  }

  /**
   * @return self
   */
  public function secure(): DriverInterface
  {
    $this->protocol = 'https';

    return $this;
  }

  /**
   * @return string
   */
  public function getProtocol(): string
  {
    return $this->protocol;
  }

  /**
   * Sets the API key to use.
   *
   * @param string $accessKey Your API key
   *
   * @return self
   */
  public function accessKey(string $accessKey): DriverInterface
  {
    $this->accessKey = $accessKey;
    return $this;
  }

  /**
   * @param string $endpoint
   *
   * @return string
   */
  public function getAPIUrl(string $endpoint): string
  {
    return sprintf('%s://%s/%s', $this->getProtocol(), $this->apiURL, $endpoint);
  }
}
