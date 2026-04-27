<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Drivers;

use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Drivers\BaseCurrencyDriver;
use Otherguy\Currency\Results\ConversionResult;
use Override;

class TestableDriver extends BaseCurrencyDriver
{
    protected string $apiURL       = 'example.test';
    protected string $baseCurrency = 'USD';

    #[Override]
    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        return new ConversionResult($this->getBaseCurrency());
    }

    #[Override]
    public function historical(
        ?DateTimeInterface $date = null,
        string|Currency|array $forCurrency = [],
    ): ConversionResult {
        return new ConversionResult($this->getBaseCurrency());
    }

    #[Override]
    public function convert(
        ?float $amount = null,
        string|Currency|null $fromCurrency = null,
        string|Currency|null $toCurrency = null,
        ?DateTimeInterface $date = null,
    ): ConversionResult {
        return new ConversionResult($this->getBaseCurrency());
    }

    /**
     * @param array<string, scalar> $params
     *
     * @return array<array-key, mixed>
     */
    public function callApi(string $endpoint, array $params = []): array
    {
        return $this->apiRequest($endpoint, $params);
    }
}
