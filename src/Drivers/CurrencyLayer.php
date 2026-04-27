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

class CurrencyLayer extends BaseCurrencyDriver
{
    protected string $apiURL       = 'apilayer.net/api';
    protected string $baseCurrency = 'USD';

    /** @var array<string, scalar> */
    protected array $httpParams = [
      'format' => 0,
    ];

    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $response = $this->apiRequest('live', [
          'source'     => $this->getBaseCurrency(),
          'currencies' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            $this->responseString($response, 'source', 'CurrencyLayer'),
            $this->timestampToDate($this->responseInt($response, 'timestamp', 'CurrencyLayer')),
            $this->stripQuotes($this->responseRates($response, 'quotes', 'CurrencyLayer')),
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

        $response = $this->apiRequest('historical', [
          'date'       => $this->getDate(),
          'source'     => $this->getBaseCurrency(),
          'currencies' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            $this->responseString($response, 'source', 'CurrencyLayer'),
            $this->timestampToDate($this->responseInt($response, 'timestamp', 'CurrencyLayer')),
            $this->stripQuotes($this->responseRates($response, 'quotes', 'CurrencyLayer')),
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

        $params = [
          'from'   => $this->getBaseCurrency(),
          'to'     => $target,
          'amount' => $this->amount,
        ];

        if ($this->getDate() !== null) {
            $params['date'] = $this->getDate();
        }

        $response = $this->apiRequest('convert', $params);

        $rate = BigDecimal::of($this->responseString($response, 'result', 'CurrencyLayer'))
          ->dividedBy(BigDecimal::of((string) $this->amount), ConversionResult::DEFAULT_SCALE, RoundingMode::HALF_UP);

        $info = $response['info'] ?? [];
        $timestamp = is_array($info) ? ($info['timestamp'] ?? null) : null;
        $timestamp = is_int($timestamp) || is_string($timestamp) ? $timestamp : null;

        return new ConversionResult(
            $this->getBaseCurrency(),
            $this->getDate() ?? $this->timestampToDate($timestamp),
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

        if (!($response['success'] ?? false)) {
            throw new ApiException(
                (string) ($response['error']['info'] ?? 'CurrencyLayer API error'),
                (int) ($response['error']['code'] ?? 0),
            );
        }

        return $response;
    }

    /**
     * @param array<string, BigDecimal|float|int|string> $quotes
     *
     * @return array<string, BigDecimal|float|int|string>
     */
    private function stripQuotes(array $quotes): array
    {
        $rates = [];
        foreach ($quotes as $currency => $rate) {
            $rates[substr((string) $currency, 3, 3)] = $rate;
        }

        return $rates;
    }

    private function timestampToDate(int|string|null $timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        return DateHelper::format(new DateTimeImmutable('@' . $timestamp));
    }
}
