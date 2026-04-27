<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Override;

class Frankfurter extends BaseCurrencyDriver
{
    protected string $apiURL       = 'api.frankfurter.dev/v1';
    protected string $baseCurrency = 'EUR';

    #[Override]
    public function accessKey(string $accessKey): static
    {
        throw new ApiException('Frankfurter does not require an API key.');
    }

    #[Override]
    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $response = $this->apiRequest('latest', $this->buildSymbolsParams());

        return new ConversionResult(
            $this->responseString($response, 'base', 'Frankfurter'),
            $this->responseString($response, 'date', 'Frankfurter'),
            $this->responseRates($response, 'rates', 'Frankfurter'),
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

        if ($this->getDate() === null) {
            throw new ApiException('Date needs to be set!');
        }

        $response = $this->apiRequest($this->getDate(), $this->buildSymbolsParams());

        return new ConversionResult(
            $this->responseString($response, 'base', 'Frankfurter'),
            $this->responseString($response, 'date', 'Frankfurter'),
            $this->responseRates($response, 'rates', 'Frankfurter'),
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

    /**
     * @return array<string, string>
     */
    private function buildSymbolsParams(): array
    {
        $params = [
          'base' => $this->getBaseCurrency(),
        ];

        if ($this->getSymbols() !== []) {
            $params['symbols'] = implode(',', $this->getSymbols());
        }

        return $params;
    }
}
