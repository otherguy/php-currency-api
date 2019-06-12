<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;

/**
 * Class CurrencyLayer
 *
 * @package Otherguy\Currency\Drivers
 */
class CurrencyLayer extends BaseCurrencyDriver implements CurrencyDriverContract
{
  protected $protocol = 'http';
  protected $apiURL   = 'apilayer.net/api/';

  /** @var string $baseCurrency CurrencyLayer's Free Plan base currency is 'USD' */
  protected $baseCurrency = 'USD';

  protected $httpParams   = [
    'format' => 1
  ];


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
    $response = $this->apiRequest('live', [
      'source'     => $this->getBaseCurrency(),
      'currencies' => join(',', $this->getSymbols())
    ]);

    // Transform rates response
    $rates = [];
    foreach($response['quotes'] as $currency => $rate) {
      $rates[substr($currency, 3, 3)] = $rate;
    }

    return new ConversionResult($response['source'], $response['timestamp'], $rates);
  }

  /**
   * Converts any amount in a given currency to another currency.
   *
   * @param float  $amount       The amount to convert.
   * @param string $fromCurrency The base currency.
   * @param string $toCurrency   The target currency.
   *
   * @return float The conversion result.
   *
   * @throws CurrencyException
   */
  public function convert(float $amount = null, string $fromCurrency = null, string $toCurrency = null): float
  {
    // Overwrite/set params
    if($amount !== null) {
      $this->amount = $amount;
    }

    if($fromCurrency !== null) {
      $this->baseCurrency = $fromCurrency;
    }

    if($toCurrency !== null) {
      $this->currencies = [$toCurrency];
    }

    // Get API response
    $response = $this->apiRequest('convert', [
      'from'   => $this->getBaseCurrency(),
      'to'     => reset($this->currencies),
      'amount' => $this->amount
    ]);

    // Return the rate as a float
    return floatval($response['info']['rate']);
  }

  /**
   * @param int|string|DateTime $date
   * @param string|array        $forCurrency
   *
   * @return ConversionResult
   *
   * @throws CurrencyException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function historical($date = null, $forCurrency = []): ConversionResult
  {
    // Set date
    $this->date($date);

    if (!empty((array)$forCurrency)) {
      $this->currencies((array)$forCurrency);
    }

    // Get API response
    $response = $this->apiRequest('historical', [
      'date'       => $this->date,
      'source'     => $this->getBaseCurrency(),
      'currencies' => join(',', $this->getSymbols())
    ]);

    // Transform rates response
    $rates = [];
    foreach($response['quotes'] as $currency => $rate) {
      $rates[substr($currency, 3, 3)] = $rate;
    }

    return new ConversionResult($response['source'], $response['timestamp'], $rates);
  }

  /**
   * Performs an HTTP request.
   *
   * @param string $endpoint The API endpoint.
   * @param string $method   The HTTP method (defaults to 'GET').
   *
   * @return array|bool The response as decoded JSON.
   *
   * @throws CurrencyException
   */
  public function apiRequest(string $endpoint, string $method = 'GET')
  {
    // Perform actual API request.
    $response = parent::apiRequest($endpoint, $method);

    // If the response is not an array, something went wrong.
    if(! is_array($response)) {
      throw new ApiException('Unexpected API response!');
    }

    // Handle response exceptions.
    if ($response['success'] == false) {
      throw new ApiException($response['error']['info'], $response['error']['code']);
    }

    return $response;
  }

}
