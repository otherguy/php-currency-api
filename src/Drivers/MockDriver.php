<?php namespace Otherguy\Currency\Drivers;

/**
 * Class MockDriver
 *
 * @package Otherguy\Currency\Drivers
 */
class MockDriver extends BaseDriver implements DriverInterface
{
    public function symbols(): array
    {
        return [];
    }

    public function convert(string $fromCurrency, string $toCurrency, $amount): array
    {
        return [];
    }

    public function get(string $forCurrency = null): array
    {
        return [];
    }

    public function historical($date, string $forCurrency = null): array
    {
        return [];
    }

    public function between($fromDate, $toDate): array
    {
        return [];
    }

    public function fluctuationBetween($fromDate, $toDate): array
    {
        return [];
    }
}