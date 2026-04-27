<?php

declare(strict_types=1);

namespace Otherguy\Currency\Results;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\CurrencyException;

class ConversionResult
{
    public const int DEFAULT_SCALE = 8;

    /**
     * @var array<string, BigDecimal>
     */
    public readonly array $originalConversionRates;

    public readonly string $originalBaseCurrency;

    /**
     * @var array<string, BigDecimal>
     */
    private array $conversionRates;

    private string $baseCurrency;

    /**
     * @param array<string, BigDecimal|float|int|string> $rates
     *
     * @throws MathException If a rate value is not a valid numeric.
     */
    public function __construct(
        string|Currency $baseCurrency,
        public readonly ?string $date = null,
        array $rates = [],
        public readonly int $scale = self::DEFAULT_SCALE,
    ) {
        $code = Currency::code($baseCurrency);

        $this->originalBaseCurrency = $code;
        $this->baseCurrency         = $code;

        $normalised = [];
        foreach ($rates as $currency => $rate) {
            $normalised[(string) $currency] = $this->toBigDecimal($rate);
        }
        $normalised[$code] = BigDecimal::one();

        $this->originalConversionRates = $normalised;
        $this->conversionRates         = $normalised;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @throws CurrencyException
     */
    public function setBaseCurrency(string|Currency $baseCurrency): self
    {
        $code = Currency::code($baseCurrency);

        if (!isset($this->originalConversionRates[$code])) {
            throw new CurrencyException("No conversion result for '{$code}'!");
        }

        if ($code === $this->originalBaseCurrency) {
            $this->conversionRates = $this->originalConversionRates;
            $this->baseCurrency    = $code;

            return $this;
        }

        $divisor = $this->originalConversionRates[$code];

        $rebased = [];
        foreach ($this->originalConversionRates as $currency => $rate) {
            $rebased[$currency] = $rate->dividedBy($divisor, $this->scale, RoundingMode::HALF_UP);
        }
        $rebased[$code] = BigDecimal::one();

        $this->conversionRates = $rebased;
        $this->baseCurrency    = $code;

        return $this;
    }

    /**
     * @throws CurrencyException
     */
    public function rate(string|Currency $currency): BigDecimal
    {
        $code = Currency::code($currency);

        if (!isset($this->conversionRates[$code])) {
            throw new CurrencyException("No conversion result for {$code}!");
        }

        return $this->conversionRates[$code];
    }

    /**
     * @throws CurrencyException
     */
    public function rateAsFloat(string|Currency $currency): float
    {
        return $this->rate($currency)->toFloat();
    }

    /**
     * @throws CurrencyException
     */
    public function convert(
        BigDecimal|float|int|string $amount,
        string|Currency $fromCurrency,
        string|Currency $toCurrency,
    ): BigDecimal {
        $from = Currency::code($fromCurrency);
        $to   = Currency::code($toCurrency);

        if (!isset($this->originalConversionRates[$to])) {
            throw new CurrencyException("No conversion result for '{$to}'!");
        }

        if (!isset($this->originalConversionRates[$from])) {
            throw new CurrencyException("No conversion result for '{$from}'!");
        }

        return $this->toBigDecimal($amount)
          ->multipliedBy($this->originalConversionRates[$to])
          ->dividedBy($this->originalConversionRates[$from], $this->scale, RoundingMode::HALF_UP);
    }

    /**
     * @return array<string, BigDecimal>
     */
    public function all(): array
    {
        return $this->conversionRates;
    }

    /**
     * @return array<string, float>
     */
    public function allAsFloats(): array
    {
        $floats = [];
        foreach ($this->conversionRates as $code => $rate) {
            $floats[$code] = $rate->toFloat();
        }

        return $floats;
    }

    /**
     * @throws MathException
     */
    private function toBigDecimal(BigDecimal|float|int|string $value): BigDecimal
    {
        return $value instanceof BigDecimal ? $value : BigDecimal::of($value);
    }
}
