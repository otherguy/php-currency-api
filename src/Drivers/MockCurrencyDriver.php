<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Helpers\DateHelper;
use Otherguy\Currency\Results\ConversionResult;
use Override;

class MockCurrencyDriver extends BaseCurrencyDriver
{
    protected string $apiURL       = 'localhost';
    protected string $baseCurrency = 'USD';

    /** @var array<string, BigDecimal|float|int|string> */
    private array $rates = [];

    /**
     * @param array<string, BigDecimal|float|int|string> $rates
     */
    public function withRates(array $rates): self
    {
        $this->rates = $rates;

        return $this;
    }

    #[Override]
    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        return new ConversionResult(
            $this->getBaseCurrency(),
            DateHelper::format(DateHelper::today()),
            $this->rates,
        );
    }

    #[Override]
    public function historical(
        ?DateTimeInterface $date = null,
        string|Currency|array $forCurrency = [],
    ): ConversionResult {
        if ($date instanceof DateTimeInterface) {
            $this->date($date);
        }

        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->getDate() ?? DateHelper::format(DateHelper::today()),
            $this->rates,
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

        $target = $this->currencies[0] ?? 'EUR';

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->getDate() ?? DateHelper::format(DateHelper::today()),
            [$target => BigDecimal::of('12.34')],
        );
    }
}
