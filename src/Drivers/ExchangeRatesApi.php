<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Otherguy\Currency\Exceptions\ApiException;
use Override;

class ExchangeRatesApi extends BaseCurrencyDriver
{
    protected string $apiURL       = 'api.apilayer.com/exchangerates_data';
    protected string $baseCurrency = 'EUR';

    #[Override]
    public function accessKey(string $accessKey): static
    {
        return $this->config('apikey', $accessKey);
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
                (string) ($response['error']['info'] ?? $response['message'] ?? 'ExchangeRatesApi error'),
                (int) ($response['error']['code'] ?? 0),
            );
        }

        return $response;
    }
}
