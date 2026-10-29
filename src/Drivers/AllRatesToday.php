<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Override;

/**
 * AllRatesToday keyless open endpoints.
 *
 * By default the driver serves the European Central Bank reference table re-based on the
 * source currency. With `bank()` (or `config('bank', …)`) it serves the latest official table
 * of one central bank instead, e.g. `sama`, `cbuae`, `bok`, `rbnz` or `sbv`. The open endpoints
 * publish the latest table only, so `historical()` is not available without an API key.
 */
class AllRatesToday extends BaseCurrencyDriver
{
    protected string $apiURL       = 'allratestoday.com/api/open';
    protected string $baseCurrency = 'EUR';

    private ?string $bank = null;

    #[Override]
    public function accessKey(string $accessKey): static
    {
        throw new ApiException('AllRatesToday open endpoints do not take an API key.');
    }

    /**
     * Central bank whose official table should be used, e.g. 'sama'. Codes are listed at
     * https://allratestoday.com/central-bank-rates-api/institutions/. Pass null for the ECB table.
     */
    public function bank(?string $bank): static
    {
        $bank       = $bank === null ? '' : strtolower(trim($bank));
        $this->bank = $bank === '' ? null : $bank;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    #[Override]
    public function config(string $key, string $value): static
    {
        if ($key === 'bank') {
            return $this->bank($value);
        }

        return parent::config($key, $value);
    }

    #[Override]
    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        return $this->bank === null ? $this->getEcbRates() : $this->getBankRates($this->bank);
    }

    #[Override]
    public function historical(
        ?DateTimeInterface $date = null,
        string|Currency|array $forCurrency = [],
    ): ConversionResult {
        throw new ApiException(
            'AllRatesToday open endpoints serve the latest published table only; '
            . 'dated rates need an API key (https://allratestoday.com/docs/).',
        );
    }

    #[Override]
    public function convert(
        ?float $amount = null,
        string|Currency|null $fromCurrency = null,
        string|Currency|null $toCurrency = null,
        ?DateTimeInterface $date = null,
    ): ConversionResult {
        if ($date instanceof DateTimeInterface) {
            $this->date($date);
        }

        if ($amount !== null) {
            $this->amount = $amount;
        }

        if ($fromCurrency !== null) {
            $this->baseCurrency = Currency::code($fromCurrency);
        }

        if ($toCurrency !== null) {
            $this->currencies = [Currency::code($toCurrency)];
        }

        if ($this->currencies === []) {
            throw new ApiException('A target currency is required for convert().');
        }
        if ($this->amount === null) {
            throw new ApiException('An amount is required for convert().');
        }

        return $this->getDate() === null ? $this->get() : $this->historical();
    }

    private function getEcbRates(): ConversionResult
    {
        $response = $this->apiRequest('latest/' . $this->getBaseCurrency());

        if (($response['result'] ?? null) !== 'success') {
            throw new ApiException($this->optionalResponseString($response, 'error') ?? 'AllRatesToday returned an unexpected response.');
        }

        $rates = $this->responseRates($response, 'rates', 'AllRatesToday');

        return new ConversionResult(
            $this->responseString($response, 'base_code', 'AllRatesToday'),
            $this->responseString($response, 'date', 'AllRatesToday'),
            $this->pickSymbols($rates, 'the ECB table'),
        );
    }

    private function getBankRates(string $bank): ConversionResult
    {
        $response = $this->apiRequest('central-bank/' . rawurlencode($bank));

        $table = $response['rates'] ?? null;
        if (!is_array($table)) {
            throw new ApiException($this->optionalResponseString($response, 'error') ?? "AllRatesToday returned no table for central bank {$bank}.");
        }

        // Each row says "1 base = value quote". Banks publish either foreign-per-home (the quote
        // is the home currency) or home-per-foreign (the base is), so the home currency is the
        // code common to every row.
        $rows = [];
        $seen = [];
        foreach ($table as $row) {
            if (!is_array($row)) {
                continue;
            }

            $base  = $row['base'] ?? null;
            $quote = $row['quote'] ?? null;
            $value = $row['value'] ?? null;
            if (!is_string($base) || !is_string($quote) || !is_numeric($value) || (float) $value <= 0) {
                continue;
            }

            $base   = strtoupper($base);
            $quote  = strtoupper($quote);
            $rows[] = [$base, $quote, BigDecimal::of((string) $value)];
            $seen[$base]  = ($seen[$base] ?? 0) + 1;
            $seen[$quote] = ($seen[$quote] ?? 0) + 1;
        }

        if ($rows === []) {
            throw new ApiException("AllRatesToday returned no rates for central bank {$bank}.");
        }

        arsort($seen, SORT_NUMERIC);
        $home = (string) array_key_first($seen);

        // Home-currency units per one unit of each currency.
        $units = [$home => BigDecimal::one()];
        foreach ($rows as [$base, $quote, $value]) {
            if ($quote === $home) {
                $units[$base] = $value;
            } elseif ($base === $home) {
                $units[$quote] = BigDecimal::one()->dividedBy($value, ConversionResult::DEFAULT_SCALE * 2, RoundingMode::HalfUp);
            }
        }

        $source = $this->getBaseCurrency();
        if (!isset($units[$source])) {
            throw new ApiException("Central bank {$bank} does not publish a {$source} rate.");
        }

        $rates = [];
        foreach ($units as $code => $unit) {
            $rates[$code] = $units[$source]->dividedBy($unit, ConversionResult::DEFAULT_SCALE, RoundingMode::HalfUp)->stripTrailingZeros();
        }

        return new ConversionResult(
            $source,
            $this->responseString($response, 'rate_date', 'AllRatesToday'),
            $this->pickSymbols($rates, "central bank {$bank}"),
        );
    }

    /**
     * The open endpoints always answer with the whole table, so the requested symbols are
     * picked on the client side. A requested code the table lacks is an error, not a silent gap.
     *
     * @param array<string, BigDecimal|float|int|string> $rates
     *
     * @return array<string, BigDecimal|float|int|string>
     */
    private function pickSymbols(array $rates, string $tableName): array
    {
        $symbols = $this->getSymbols();
        if ($symbols === []) {
            return $rates;
        }

        $picked = [];
        foreach ($symbols as $symbol) {
            if (!isset($rates[$symbol])) {
                throw new ApiException("{$symbol} is not in {$tableName} published by AllRatesToday.");
            }
            $picked[$symbol] = $rates[$symbol];
        }

        return $picked;
    }
}
