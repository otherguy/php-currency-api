<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

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

    #[Override]
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
