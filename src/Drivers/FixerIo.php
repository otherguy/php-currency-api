<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Results\ConversionResult;
use Otherguy\Currency\Symbol;

/**
 * Class FixerIo
 *
 * @package Otherguy\Currency\Drivers
 */
class FixerIo extends BaseCurrencyDriver implements CurrencyDriverContract
{
  protected $protocol = 'http';
  protected $apiURL   = 'data.fixer.io/api';

  /** @var string $baseCurrency Fixer.io's Free Plan base currency is 'EUR' */
  protected $baseCurrency = Symbol::EUR;


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

    $params = [
      'from'   => $this->getBaseCurrency(),
      'to'     => reset($this->currencies),
      'amount' => $this->amount,
    ];

    if (null !== $this->getDate()) {
      $params['date'] = $this->getDate();
    }

    // Get API response
    $response = $this->apiRequest('convert', $params);

    // Return the rate as a float
    return floatval($response['result']);
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
    if ($response['success'] == false) {
      $message = '';
      if (isset($response['error']['type'])) {
        $message = "[{$response['error']['type']}]";
      }
      if (isset($response['error']['info'])) {
        $message .= ' ' . $response['error']['info'];
      }
      throw new ApiException(trim($message), $response['error']['code']);
    }

    return $response;
  }
}
