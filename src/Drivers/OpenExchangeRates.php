<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;

/**
 * Class OpenExchangeRates
 *
 * @package Otherguy\Currency\Drivers
 */
class OpenExchangeRates extends BaseCurrencyDriver implements CurrencyDriverContract
{
  protected $protocol = 'http';
  protected $apiURL   = 'openexchangerates.org/api';

  /** @var string $baseCurrency OpenExchangeRates' Free Plan base currency is 'USD' */
  protected $baseCurrency = Symbol::USD;

  protected $httpParams = [
    'prettyprint'      => 'false',
    'show_alternative' => 'true',
  ];

  /**
   * Sets the API key to use. OpenExchangeRates uses app_id instead of access_key
   *
   * Shortcut for config('app_id', $accessKey)
   *
   * @param string $accessKey Your API key.
   *
   * @return self
   * @see CurrencyDriverContract::config()
   *
   */
  public function accessKey(string $accessKey): CurrencyDriverContract
  {
    $this->config('app_id', $accessKey);
    return $this;
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
    $response = $this->apiRequest('latest.json', [
      'base'    => $this->getBaseCurrency(),
      'symbols' => join(',', $this->getSymbols()),
    ]);

    return new ConversionResult($response['base'], $response['timestamp'], $response['rates']);
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
    $response = $this->apiRequest("historical/{$this->getDate()}.json", [
      'base'    => $this->getBaseCurrency(),
      'symbols' => join(',', $this->getSymbols()),
    ]);

    return new ConversionResult($response['base'], $response['timestamp'], $response['rates']);
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
    // Set date
    $this->date($date);

    // Overwrite/set params
    if ($amount !== null) {
      $this->amount = $amount;
    }

    if ($fromCurrency !== null) {
      $this->baseCurrency = $fromCurrency;
    }

    if ($toCurrency !== null) {
      $this->currencies = [$toCurrency];
    }

    if (null !== $this->getDate()) {
      $params['date'] = $this->getDate();
    }

    $targetCurrency = reset($this->currencies);

    // Get API response
    $response = $this->apiRequest("convert/{$this->amount}/{$this->getBaseCurrency()}/{$targetCurrency}");

    // Return the rate as a float
    return floatval($response['response']);
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
    if (isset($response['error']) && $response['error'] == true) {
      throw new ApiException("[{$response['message']}] {$response['description']}", $response['status']);
    }

    return $response;
  }
}
