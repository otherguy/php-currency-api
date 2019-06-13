<?php namespace Otherguy\Currency\Helpers;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;

/**
 * Class DateHelper
 *
 * @package Otherguy\Currency\Helpers
 */
class DateHelper
{

  /**
   * Parse a date from string with a format to a DateTime object
   *
   * @param string $string
   * @param string $format
   *
   * @return DateTime
   */
  public static function parse(string $string, string $format): DateTime
  {
    return new DateTime::createFromFormat($format, $string);
  }

  /**
   * Format a date (or interval) to a string with a given format
   *
   * See formatting options as in PHP date()
   *
   * @param int|string|DateTime|DateInterval|DateTimeInterface $date
   * @param string                                             $format
   *
   * @return string
   *
   * @throws Exception
   *
   * @see date()
   */
  public static function format($date, string $format): string
  {
    if ($date instanceof DateTime || $date instanceof DateTimeInterface || $date instanceof DateInterval) {
      return $date->format($format);
    } else if ($date === 'now') {
      return date($format);
    } else if (is_string($date)) {
      return (new DateTime($date))->format($format);
    } else {
      $timestamp = (integer)$date;
      return date($format, $timestamp);
    }
  }


  /**
   * Get a date object by given date or time format
   *
   * Examples::
   *
   *     Date.create('2018-12-04')
   *     Date.create('first day of next year')
   *
   * @param String $time A date/time string. For valid formats see http://php.net/manual/en/datetime.formats.php
   *
   * @return DateTime
   *
   * @throws Exception
   */
  public static function create(string $time): DateTime
  {
    return new DateTime($time);
  }

  /**
   * Get the current date and time
   *
   * Examples::
   *
   *     Date.now().timestamp
   *
   * @return DateTime
   *
   * @throws Exception
   */
  public static function now(): DateTime
  {
    return new DateTime('now');
  }

  /**
   * Get the current date
   *
   * @return DateTime
   *
   * @throws Exception
   */
  public static function today(): DateTime
  {
    return new DateTime('today');
  }
}
