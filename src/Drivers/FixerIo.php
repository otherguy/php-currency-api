<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;
use Override;

class FixerIo extends BaseCurrencyDriver
{
    protected string $apiURL       = 'data.fixer.io/api';
    protected string $baseCurrency = 'EUR';

    public function get(string|Currency|array $forCurrency = []): ConversionResult
    {
        if ($forCurrency !== []) {
            $this->currencies($forCurrency);
        }

        $response = $this->apiRequest('latest', [
          'base'    => $this->getBaseCurrency(),
          'symbols' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            (string) $response['base'],
            (string) $response['date'],
            $response['rates'],
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

        $response = $this->apiRequest($this->getDate(), [
          'base'    => $this->getBaseCurrency(),
          'symbols' => implode(',', $this->getSymbols()),
        ]);

        return new ConversionResult(
            (string) $response['base'],
            (string) $response['date'],
            $response['rates'],
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

        $rate = BigDecimal::of((string) $response['result'])
          ->dividedBy(BigDecimal::of((string) $this->amount), ConversionResult::DEFAULT_SCALE, RoundingMode::HALF_UP);

        return new ConversionResult(
            $this->getBaseCurrency(),
            isset($response['date']) ? (string) $response['date'] : $this->getDate(),
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
