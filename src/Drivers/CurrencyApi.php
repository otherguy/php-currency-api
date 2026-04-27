<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use DateTimeImmutable;
use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Helpers\DateHelper;
use Otherguy\Currency\Results\ConversionResult;
use Override;

class CurrencyApi extends BaseCurrencyDriver
{
    protected string $apiURL       = 'api.currencyapi.com';
    protected string $baseCurrency = 'USD';

    #[Override]
    public function accessKey(string $accessKey): static
    {
        $this->httpHeaders['apikey'] = $accessKey;

        return $this;
    }

    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $response = $this->apiRequest('v3/latest', $this->buildRateParams());

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->responseDate($response),
            $this->ratesFromData($response['data'] ?? []),
        );
    }

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

        $response = $this->apiRequest('v3/historical', [
          ...$this->buildRateParams(),
          'date' => $this->getDate(),
        ]);

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->getDate(),
            $this->ratesFromData($response['data'] ?? []),
        );
    }

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

        $target = $this->currencies[0] ?? null;
        if ($target === null) {
            throw new ApiException('A target currency is required for convert().');
        }
        if ($this->amount === null) {
            throw new ApiException('An amount is required for convert().');
        }

        if ($this->getDate() !== null) {
            return $this->historical();
        }

        $response = $this->apiRequest('v3/convert', [
          'value'         => $this->amount,
          'base_currency' => $this->getBaseCurrency(),
          'currencies'    => $target,
        ]);

        $converted = $response['data']['value'] ?? null;
        if (!is_scalar($converted)) {
            throw new ApiException('CurrencyAPI response did not contain a converted value.');
        }

        $rate = BigDecimal::of((string) $converted)
          ->dividedBy(BigDecimal::of((string) $this->amount), ConversionResult::DEFAULT_SCALE, RoundingMode::HALF_UP);

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->responseDate($response),
            [$target => $rate],
        );
    }

    /**
     * @return array<string, scalar>
     */
    private function buildRateParams(): array
    {
        $params = [
          'base_currency' => $this->getBaseCurrency(),
        ];

        if ($this->getSymbols() !== []) {
            $params['currencies'] = implode(',', $this->getSymbols());
        }

        return $params;
    }

    /**
     * @param mixed $data
     *
     * @return array<string, scalar>
     */
    private function ratesFromData(mixed $data): array
    {
        if (!is_array($data)) {
            throw new ApiException('CurrencyAPI response did not contain rate data.');
        }

        $rates = [];
        foreach ($data as $currency => $rateData) {
            if (!is_array($rateData) || !isset($rateData['value']) || !is_scalar($rateData['value'])) {
                throw new ApiException('CurrencyAPI response did not contain a rate for ' . (string) $currency . '.');
            }

            $rates[(string) $currency] = $rateData['value'];
        }

        return $rates;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    private function responseDate(array $response): ?string
    {
        $timestamp = $response['meta']['last_updated_at'] ?? null;
        if (!is_string($timestamp) || $timestamp === '') {
            return null;
        }

        return DateHelper::format(new DateTimeImmutable($timestamp));
    }
}
