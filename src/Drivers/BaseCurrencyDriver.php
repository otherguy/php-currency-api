<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Otherguy\Currency\Exceptions\ApiException;

/**
 * Class BaseDriver
 *
 * @package Otherguy\Currency\Drivers
 */
abstract class BaseCurrencyDriver implements CurrencyDriverContract
{
  protected $apiURL    = 'localhost';
  protected $protocol  = 'http';
  protected $headers   = [
    'Accept'       => 'application/json',
    'Content-Type' => 'application/json',
  ];

  protected $currencies   = [];
  protected $baseCurrency = 'USD';
  protected $amount       = 0.00;
  protected $date         = null;

  protected $httpClient   = null;
  protected $httpParams   = [];

  /**
   * BaseDriver constructor.
   *
   * @param ClientInterface $client
   */
  public function __construct(ClientInterface $client)
  {
    $this->httpClient = $client;
  }

  /**
   * @param string $baseCurrency
   *
   * @return self
   */
  public function source(string $baseCurrency): CurrencyDriverContract
  {
    $this->baseCurrency = $baseCurrency;
    return $this;
  }

  /**
   * Alias for 'source'.
   *
   * @param string $baseCurrency
   *
   * @return CurrencyDriverContract
   *@see CurrencyDriverContract::source()
   *
   */
  public function from(string $baseCurrency): CurrencyDriverContract
  {
    return $this->source($baseCurrency);
  }

  /**
   * @param string|array $symbols
   *
   * @return self
   */
  public function currencies($symbols = []): CurrencyDriverContract
  {
    $this->currencies = (array)$symbols;
    return $this;
  }

  /**
   * Alias for 'currencies'.
   *
   * @param array $symbols
   *
   * @return CurrencyDriverContract
   *@see CurrencyDriverContract::currencies()
   *
   */
  public function to($symbols = []): CurrencyDriverContract
  {
    return $this->currencies($symbols);
  }

  /**
   * @param double|integer|float $amount
   *
   * @return self
   */
  public function amount($amount): CurrencyDriverContract
  {
    $this->amount = $amount;
    return $this;
  }

  /**
   * @param int|string|DateTime $date
   *
   * @return self
   */
  public function date($date): CurrencyDriverContract
  {
    if (is_integer($date)) {
      $this->date = date('Y-m-d', $date);
    } else if ($date instanceof DateTime) {
      $this->date = $date->format('Y-m-d');
    } else if (is_string($date)) {
      $this->date = date('Y-m-d', strtotime($date));
    }

    return $this;
  }

  /**
   * Returns the date in 'YYYY-mm-dd' format or null if not set.
   *
   * @return string|null
   */
  public function getDate(): ?string
  {
    return $this->date;
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
  public function secure(): CurrencyDriverContract
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
   * Set a config parameter.
   *
   * @param string $key
   * @param string $value
   *
   * @return self
   */
  public function config(string $key, string $value): CurrencyDriverContract
  {
    $this->httpParams[$key] = $value;
    return $this;
  }

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
  public function accessKey(string $accessKey): CurrencyDriverContract
  {
    $this->config('access_key', $accessKey);
    return $this;
  }

  /**
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param array  $params   The query parameters for this request.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|bool The response as decoded JSON.
   *
   * @throws ApiException
   */
  function apiRequest(string $endpoint, array $params = [], string $method = 'GET')
  {
    $url = sprintf('%s://%s/%s', $this->getProtocol(), $this->apiURL, $endpoint);

    try {
      $response = $this->httpClient->request($method, $url, ['query' => array_merge($this->httpParams, $params)])->getBody();
    } catch (GuzzleException $e ) {
      throw new ApiException($e->getMessage(), $e->getCode(), $e);
    }

    $data = json_decode($response->getContents(), true);

    // Check for JSON errors
    if(json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
      throw new ApiException(json_last_error_msg(), json_last_error());
    }

    // Otherwise return data.
    return $data;
  }
}
