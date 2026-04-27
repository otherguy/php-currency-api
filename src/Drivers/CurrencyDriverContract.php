<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Results\ConversionResult;

interface CurrencyDriverContract
{
    public function source(string|Currency $baseCurrency): static;

    public function from(string|Currency $baseCurrency): static;

    /**
     * @param string|Currency|array<int, string|Currency> $symbols
     */
    public function currencies(string|Currency|array $symbols = []): static;

    /**
     * @param string|Currency|array<int, string|Currency> $symbols
     */
    public function to(string|Currency|array $symbols = []): static;

    public function amount(?float $amount): static;

    public function date(?DateTimeInterface $date): static;

    /**
     * Returns the date in 'YYYY-mm-dd' format or null if not set.
     */
    public function getDate(): ?string;

    /**
     * @return list<string>
     */
    public function getSymbols(): array;

    /**
     * @param string|Currency|array<int, string|Currency> $forCurrency
     */
    public function get(string|Currency|array $forCurrency = []): ConversionResult;

    /**
     * Converts an amount of `$fromCurrency` into `$toCurrency`, optionally for a given date.
     */
    public function convert(
        ?float $amount = null,
        string|Currency|null $fromCurrency = null,
        string|Currency|null $toCurrency = null,
        ?DateTimeInterface $date = null,
    ): ConversionResult;

    /**
     * @param string|Currency|array<int, string|Currency> $forCurrency
     */
    public function historical(
        ?DateTimeInterface $date = null,
        string|Currency|array $forCurrency = [],
    ): ConversionResult;

    public function getBaseCurrency(): string;

    public function config(string $key, string $value): static;

    /**
     * Sets the API key to use.
     *
     * Shortcut for config('access_key', $accessKey).
     */
    public function accessKey(string $accessKey): static;

    /**
     * Switches all HTTP requests to HTTPS.
     *
     * Drivers default to HTTPS in 2.0; this exists for explicit toggling.
     */
    public function secure(): static;

    public function getProtocol(): string;
}
