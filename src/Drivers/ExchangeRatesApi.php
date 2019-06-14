<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;

/**
 * Class RatesApi
 *
 * @package Otherguy\Currency\Drivers
 */
class ExchangeRatesApi extends BaseCurrencyDriver implements CurrencyDriverContract
{
  protected $protocol = 'https';
  protected $apiURL   = 'api.exchangeratesapi.io';

  /** @var string $baseCurrency Exchange Rates API default base currency is 'EUR' */
  protected $baseCurrency = Symbol::EUR;

  /**
   * Sets the API key to use. ExchangeRatesAPI has no API keys and is open to use.
   *
   * @param string $accessKey Your API key.
   *
   * @return CurrencyDriverContract
   * @throws ApiException
   */
  public function accessKey(string $accessKey): CurrencyDriverContract
  {
    throw new ApiException('No Access Key is required for this driver!', 400);
  }

  /**
   * @param string|array $forCurrency
   *
   * @return ConversionResult
   *
   * @throws CurrencyException
   */
  public function get($forCurrency = []): ConversionResult
  {
    if (!empty((array)$forCurrency)) {
      $this->currencies((array)$forCurrency);
    }

    // Get API response
    $response = $this->apiRequest('latest', [
      'base'    => $this->getBaseCurrency(),
      'symbols' => join(',', $this->getSymbols()),
    ]);

    return new ConversionResult($response['base'], $response['date'], $response['rates']);
  }

  /**
   * @param int|string|DateTime $date
   * @param string|array        $forCurrency
   *
   * @return ConversionResult
   *
   * @throws CurrencyException
   */
  public function historical($date = null, $forCurrency = []): ConversionResult
  {
    // Set date
    $this->date($date);

    if (!empty((array)$forCurrency)) {
      $this->currencies((array)$forCurrency);
    }

    if (null === $this->getDate()) {
      throw new ApiException('Date needs to be set!');
    }

    // Get API response
    $response = $this->apiRequest($this->getDate(), [
      'base'    => $this->getBaseCurrency(),
      'symbols' => join(',', $this->getSymbols()),
    ]);

    return new ConversionResult($response['base'], $response['date'], $response['rates']);
  }

  /**
   * Converts any amount in a given currency to another currency.
   *
   * @param float               $amount       The amount to convert.
   * @param string              $fromCurrency The base currency.
   * @param string              $toCurrency   The target currency.
   * @param int|string|DateTime $date         The date to get the conversion rate for.
   *
   * @return float The conversion result.
   *
   * @throws ApiException
   */
  public function convert(float $amount = null, string $fromCurrency = null, string $toCurrency = null, $date = null): float
  {
    throw new ApiException("Endpoint 'convert' is not supported for this driver!", 404);
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
    // Perform actual API request.
    $response = parent::apiRequest($endpoint, $params, $method);


    // Handle response exceptions.
    if (isset($response['error'])) {
      throw new ApiException((string)$response['error'], 500);
    }

    return $response;
  }
}
