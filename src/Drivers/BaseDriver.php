<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use GuzzleHttp\Client as HTTPClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Middleware;
use Otherguy\Currency\Middleware\JsonAwareResponse;
use Psr\Http\Message\ResponseInterface;

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

  protected $httpClient   = null;
  protected $clientConfig = [
    'http_errors'     => true,
    'timeout'         => 30,
    'allow_redirects' => true,
    'decode_content'  => true
  ];

  /**
   * BaseDriver constructor.
   */
  public function __construct()
  {
    $this->httpClient = $this->makeHttpClient();
  }

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
   * Alias for 'source'.
   *
   * @see DriverInterface::source()
   *
   * @param string $baseCurrency
   *
   * @return DriverInterface
   */
  public function from(string $baseCurrency): DriverInterface
  {
    return $this->source($baseCurrency);
  }

  /**
   * @param string|array $symbols
   *
   * @return self
   */
  public function currencies($symbols = []): DriverInterface
  {
    $this->currencies = (array)$symbols;
    return $this;
  }

  /**
   * Alias for 'currencies'.
   *
   * @see DriverInterface::currencies()
   *
   * @param array $symbols
   *
   * @return DriverInterface
   */
  public function to($symbols = []): DriverInterface
  {
    return $this->currencies($symbols);
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
   * @param int|string|DateTime $date
   *
   * @return self
   */
  public function date($date): DriverInterface
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
    $this->protocol   = 'https';
    $this->httpClient = $this->makeHttpClient();

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
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param array  $params   The URL query parameters.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|string|bool The response as decoded JSON.
   */
  public function apiRequest(string $endpoint, array $params = [], string $method = 'GET')
  {
    try {
      $response = $this->httpClient
        ->request($method, $endpoint, [
          'query' => array_merge($params, $this->getDefaultParams())
        ])
        ->getBody();
    } catch (GuzzleException $e ) {
      return false;
    }

    return $response;
  }

  /**
   * Creates an instance of HTTPClient
   *
   * @return HTTPClient
   */
  protected function makeHttpClient(): HTTPClient
  {
    $this->clientConfig['base_uri'] = sprintf('%s://%s', $this->getProtocol(), $this->apiURL);
    $client = new HTTPClient($this->clientConfig);

    // Push JSON decode middleware on the Guzzle middleware stack
    $client->getConfig('handler')->push(Middleware::mapResponse(function (ResponseInterface $response) {
      return new JsonAwareResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody(),
        $response->getProtocolVersion(),
        $response->getReasonPhrase()
      );
    }), 'json_decode_middleware');

    return $client;
  }
}
