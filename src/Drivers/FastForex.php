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

class FastForex extends BaseCurrencyDriver
{
    protected string $apiURL       = 'api.fastforex.io';
    protected string $baseCurrency = 'USD';

    #[Override]
    public function accessKey(string $accessKey): static
    {
        return $this->config('api_key', $accessKey);
    }

    #[Override]
    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $endpoint = match (count($this->getSymbols())) {
            0 => 'fetch-all',
            1 => 'fetch-one',
            default => 'fetch-multi',
        };

        $response = $this->apiRequest($endpoint, $this->buildRateParams());

        return new ConversionResult(
            $this->optionalResponseString($response, 'base') ?? $this->getBaseCurrency(),
            $this->responseDate($response),
            $this->ratesFromResponse($response),
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

        $response = $this->apiRequest('historical', [
          ...$this->buildRateParams(),
          'date' => $this->getDate(),
        ]);

        return new ConversionResult(
            $this->optionalResponseString($response, 'base') ?? $this->getBaseCurrency(),
            $this->optionalResponseString($response, 'date') ?? $this->getDate(),
            $this->ratesFromResponse($response),
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

        $response = $this->apiRequest('convert', [
          'from'   => $this->getBaseCurrency(),
          'to'     => $target,
          'amount' => $this->amount,
        ]);

        $rates = $this->ratesFromResponse($response);
        if (!isset($rates[$target])) {
            throw new ApiException("fastFOREX response did not contain a conversion for {$target}.");
        }

        $rate = BigDecimal::of((string) $rates[$target])
          ->dividedBy(BigDecimal::of((string) $this->amount), ConversionResult::DEFAULT_SCALE, RoundingMode::HALF_UP);

        return new ConversionResult(
            $this->optionalResponseString($response, 'base') ?? $this->getBaseCurrency(),
            $this->responseDate($response),
            [$target => $rate],
        );
    }

    /**
     * @param array<string, scalar> $params
     *
     * @return array<array-key, mixed>
     */
    #[Override]
    protected function apiRequest(string $endpoint, array $params = []): array
    {
        $response = parent::apiRequest($endpoint, $params);

        if (isset($response['error'])) {
            throw new ApiException(is_scalar($response['error']) ? (string) $response['error'] : 'fastFOREX API error');
        }

        return $response;
    }

    /**
     * @return array<string, scalar>
     */
    private function buildRateParams(): array
    {
        $params = [
          'from' => $this->getBaseCurrency(),
        ];

        if ($this->getSymbols() !== []) {
            $params['to'] = implode(',', $this->getSymbols());
        }

        return $params;
    }

    /**
     * @param array<array-key, mixed> $response
     *
     * @return array<string, BigDecimal|float|int|string>
     */
    private function ratesFromResponse(array $response): array
    {
        $rates = $response['result'] ?? $response['results'] ?? null;
        if (!is_array($rates)) {
            throw new ApiException('fastFOREX response did not contain rate data.');
        }

        $normalised = [];
        foreach ($rates as $currency => $rate) {
            if (!$rate instanceof BigDecimal && !is_float($rate) && !is_int($rate) && !is_string($rate)) {
                throw new ApiException('fastFOREX response did not contain a rate for ' . (string) $currency . '.');
            }

            $normalised[(string) $currency] = $rate;
        }

        return $normalised;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    private function responseDate(array $response): ?string
    {
        if (isset($response['date']) && is_scalar($response['date'])) {
            return (string) $response['date'];
        }

        if (isset($response['updated']) && is_string($response['updated']) && $response['updated'] !== '') {
            return DateHelper::format(new DateTimeImmutable(strtok($response['updated'], ' ') ?: $response['updated']));
        }

        return null;
    }
}
