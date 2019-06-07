<?php namespace Otherguy\Currency\Drivers;

use DateTime;
use Requests;

/**
 * Class FixerIo
 *
 * @package Otherguy\Currency\Drivers
 */
class FixerIo extends BaseDriver implements DriverInterface
{
  protected $apiURL = 'fixerio.com';

  protected $headers = [
    'Accept'       => 'application/json',
    'Content-Type' => 'application/json',
  ];


  public function get(string $forCurrency = null): array
  {
    $request = Requests::get($this->getAPIUrl('live'), $this->headers);

    //var_dump($request->status_code);

    var_dump($request->body);
    return [];
  }

  /**
   * @param string               $fromCurrency
   * @param string               $toCurrency
   * @param double|integer|float $amount
   *
   * @return array
   */
  public function convert(string $fromCurrency = null, string $toCurrency = null, $amount = null): array
  {
    return [];
  }

  /**
   * @param string|DateTime $date
   * @param null|string     $forCurrency
   *
   * @return array
   */
  public function historical($date = null, string $forCurrency = null): array
  {
    return [];
  }
}
