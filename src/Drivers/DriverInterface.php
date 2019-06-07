<?php namespace Otherguy\Currency\Drivers;

use DateTime;

/**
 * Interface DriverInterface
 *
 * @package Otherguy\Currency\Drivers
 */
interface DriverInterface
{
    /**
     * @param string $baseCurrency
     *
     * @return self
     */
    public function setBase(string $baseCurrency): DriverInterface;

    /**
     * @param array $symbols
     *
     * @return self
     */
    public function setSymbols(array $symbols): DriverInterface;

    /**
     * @return string
     */
    public function getBaseCurrency(): string;

    /**
     * @return array
     */
    public function getSymbols(): array;

    /**
     * @return array
     */
    public function symbols(): array;

    /**
     * @param null|string $forCurrency
     *
     * @return array
     */
    public function get(string $forCurrency = null): array;

    /**
     * @param string               $fromCurrency
     * @param string               $toCurrency
     * @param double|integer|float $amount
     *
     * @return array
     */
    public function convert(string $fromCurrency, string $toCurrency, $amount): array;

    /**
     * @param string|DateTime $date
     * @param null|string     $forCurrency
     *
     * @return array
     */
    public function historical($date, string $forCurrency = null): array;

    /**
     * @param string|DateTime $fromDate
     * @param string|DateTime $toDate
     *
     * @return array
     */
    public function between($fromDate, $toDate): array;

    /**
     * @param string|DateTime $fromDate
     * @param string|DateTime $toDate
     *
     * @return array
     */
    public function fluctuationBetween($fromDate, $toDate): array;

    /**
     * Secures all HTTP requests by switching to HTTPS.
     *
     * Note: Most free APIs do not support this!
     *
     * @return self
     */
    public function secure(): DriverInterface;

    /**
     * Returns the protocol that is currently being used.
     *
     * @return string
     */
    public function getProtocol() : string;


    /**
     * Sets the API key to use.
     *
     * @param string $accessKey Your API key.
     *
     * @return DriverInterface
     */
    public function setAccessKey(string $accessKey): DriverInterface;
}