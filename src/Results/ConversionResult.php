<?php namespace Otherguy\Currency\Results;

use DateTime;
use Exception;
use Otherguy\Currency\Exceptions\CurrencyException;
use Otherguy\Currency\Helpers\DateHelper;

/**
 * Class ConversionResult
 *
 * @package Otherguy\Currency\Results
 */
class ConversionResult
{
  private $originalConversionRates = [];
  private $originalBaseCurrency    = [];

  protected $baseCurrency;
  protected $date;
  protected $conversionRates = [];


  /**
   * ConversionResult constructor.
   *
   * @param string              $baseCurrency
   * @param int|DateTime|string $date
   * @param array               $rates
   *
   * @throws Exception
   */
  public function __construct(string $baseCurrency, $date, array $rates)
  {
    $this->originalBaseCurrency = $baseCurrency;
    $this->baseCurrency         = $baseCurrency;

    $this->date = DateHelper::format($date, 'Y-m-d');

    $rates[$baseCurrency] = 1.0;

    $this->originalConversionRates = $rates;
    $this->conversionRates         = $rates;
  }

  /**
   * Get base currency.
   *
   * @return string
   */
  public function getBaseCurrency(): string
  {
    return $this->baseCurrency;
  }

  /**
   * Set new base currency.
   *
   * @param string $baseCurrency The new base currency.
   *
   * @return self
   *
   * @throws CurrencyException
   */
  public function setBaseCurrency(string $baseCurrency): ConversionResult
  {
    if (!isset($this->conversionRates[$baseCurrency])) {
      throw new CurrencyException("No conversion result for '$baseCurrency'!");
    }

    if ($baseCurrency == $this->originalBaseCurrency) {
      $this->conversionRates = $this->originalConversionRates;
      return $this;
    }

    // Calculate new conversion rates.
    foreach ($this->originalConversionRates as $currency => $rate) {
      $this->conversionRates[$currency] = (float)$rate / (float)$this->originalConversionRates[$baseCurrency];
    }

    // Set new base currency.
    $this->baseCurrency                   = $baseCurrency;
    $this->conversionRates[$baseCurrency] = 1.0;

    // Return self
    return $this;
  }

  /**
   * Get date.
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * @param string $currency
   *
   * @return float
   *
   * @throws CurrencyException
   */
  public function rate(string $currency): float
  {
    if (!isset($this->conversionRates[$currency])) {
      throw new CurrencyException("No conversion result for $currency!");
    }

    return $this->conversionRates[$currency];
  }

  /**
   * @param float  $amount
   * @param string $fromCurrency
   * @param string $toCurrency
   *
   * @return float
   *
   * @throws CurrencyException
   */
  function convert(float $amount, string $fromCurrency, string $toCurrency): float
  {
    if (!isset($this->conversionRates[$toCurrency])) {
      throw new CurrencyException("No conversion result for '$toCurrency'!");
    }

    if (!isset($this->conversionRates[$fromCurrency])) {
      throw new CurrencyException("No conversion result for '$fromCurrency'!");
    }

    return $amount * (float)$this->originalConversionRates[$toCurrency] / (float)$this->originalConversionRates[$fromCurrency];
  }

  /**
   * @return array
   */
  public function all(): array
  {
    return $this->conversionRates;
  }
}
