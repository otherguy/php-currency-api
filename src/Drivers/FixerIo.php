<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use GuzzleHttp\Client as HTTPClient;
use Otherguy\Currency\Exceptions\MissingAccessKeyException;

/**
 * Class FixerIo
 *
 * @package Otherguy\Currency\Drivers
 */
class FixerIo extends BaseDriver implements DriverInterface
{
  protected $protocol = 'http';
  protected $apiURL   = 'data.fixer.io/api';

  /** @var string $baseCurrency Fixer.io's Free Plan base currency is 'EUR' */
  protected $baseCurrency = 'EUR';


  /**
   * @param string|null $forCurrency
   *
   * @return array
   *
   * @throws MissingAccessKeyException
   */
  public function get(string $forCurrency = null): array
  {
    if ($this->accessKey == null) {
      throw new MissingAccessKeyException();
    }

    $client = new HTTPClient();
    $response = $client->get($this->getAPIUrl('latest'), [ 'query' => [
      'access_key' => $this->accessKey,
      'base'       => $this->getBaseCurrency(),
      'symbols'    => join(',', $this->getSymbols()),
    ]]);

    // echo $response->getStatusCode(); # 200

    return (array)json_decode($response->getBody()->getContents());
  }

  /**
   * @param double|integer|float $amount
   * @param string               $fromCurrency
   * @param string               $toCurrency
   *
   * @return array
   *
   * @throws MissingAccessKeyException
   */
  public function convert($amount = null, string $fromCurrency = null, string $toCurrency = null): array
  {
    if ($this->accessKey == null) {
      throw new MissingAccessKeyException();
    }

    return [];
  }

  /**
   * @param string|DateTime $date
   * @param null|string     $forCurrency
   *
   * @return array
   *
   * @throws MissingAccessKeyException
   */
  public function historical($date = null, string $forCurrency = null): array
  {
    if ($this->accessKey == null) {
      throw new MissingAccessKeyException();
    }
    return [];
  }
}
