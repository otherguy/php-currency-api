<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
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

    #[Override]
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
