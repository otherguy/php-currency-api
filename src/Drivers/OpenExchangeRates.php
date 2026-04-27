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

class OpenExchangeRates extends BaseCurrencyDriver
{
    protected string $apiURL       = 'openexchangerates.org/api';
    protected string $baseCurrency = 'USD';

    /** @var array<string, scalar> */
    protected array $httpParams = [
      'prettyprint'      => 'false',
      'show_alternative' => 'true',
    ];

    #[Override]
    public function accessKey(string $accessKey): static
    {
        return $this->config('app_id', $accessKey);
    }

    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $response = $this->apiRequest('latest.json', [
          'base'    => $this->getBaseCurrency(),
          'symbols' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            $this->responseString($response, 'base', 'OpenExchangeRates'),
            $this->timestampToDate($this->responseInt($response, 'timestamp', 'OpenExchangeRates')),
            $this->responseRates($response, 'rates', 'OpenExchangeRates'),
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

        $response = $this->apiRequest("historical/{$this->getDate()}.json", [
          'base'    => $this->getBaseCurrency(),
          'symbols' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            $this->responseString($response, 'base', 'OpenExchangeRates'),
            $this->timestampToDate($this->responseInt($response, 'timestamp', 'OpenExchangeRates')),
            $this->responseRates($response, 'rates', 'OpenExchangeRates'),
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

        $response = $this->apiRequest("convert/{$this->amount}/{$this->getBaseCurrency()}/{$target}");

        $rate = BigDecimal::of($this->responseString($response, 'response', 'OpenExchangeRates'))
          ->dividedBy(BigDecimal::of((string) $this->amount), ConversionResult::DEFAULT_SCALE, RoundingMode::HALF_UP);

        $meta = $response['meta'] ?? [];
        $timestamp = is_array($meta) ? ($meta['timestamp'] ?? null) : null;
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

        if (($response['error'] ?? false) === true) {
            throw new ApiException(
                sprintf(
                    '[%s] %s',
                    (string) ($response['message'] ?? ''),
                    (string) ($response['description'] ?? ''),
                ),
                (int) ($response['status'] ?? 0),
            );
        }

        return $response;
    }

    private function timestampToDate(int|string|null $timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        return DateHelper::format(new DateTimeImmutable('@' . $timestamp));
    }
}
