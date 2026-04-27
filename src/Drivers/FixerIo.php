<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Otherguy\Currency\Exceptions\ApiException;
use Override;

class FixerIo extends BaseCurrencyDriver
{
    protected string $apiURL       = 'data.fixer.io/api';
    protected string $baseCurrency = 'EUR';

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
            $message = '';
            if (isset($response['error']['type'])) {
                $message = "[{$response['error']['type']}]";
            }
            if (isset($response['error']['info'])) {
                $message .= ' ' . $response['error']['info'];
            }

            throw new ApiException(trim($message), (int) ($response['error']['code'] ?? 0));
        }

        return $response;
    }
}
