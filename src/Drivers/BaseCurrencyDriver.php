<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use Brick\Math\BigDecimal;
use DateTimeInterface;
use JsonException;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Helpers\DateHelper;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

abstract class BaseCurrencyDriver implements CurrencyDriverContract
{
    protected string $apiURL   = 'localhost';
    protected string $protocol = 'https';
    protected string $baseCurrency;

    /** @var list<string> */
    protected array $currencies = [];

    protected ?float $amount = null;
    protected ?string $date  = null;

    /** @var array<string, scalar> */
    protected array $httpParams = [];

    /** @var array<string, string> */
    protected array $httpHeaders = [];

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
        if (!isset($this->baseCurrency)) {
            $this->baseCurrency = 'USD';
        }
    }

    public function source(string|Currency $baseCurrency): static
    {
        $this->baseCurrency = Currency::code($baseCurrency);

        return $this;
    }

    public function from(string|Currency $baseCurrency): static
    {
        return $this->source($baseCurrency);
    }

    public function currencies(string|Currency|array $symbols = []): static
    {
        $list = is_array($symbols) ? $symbols : [$symbols];

        $this->currencies = array_values(array_map(
            Currency::code(...),
            $list,
        ));

        return $this;
    }

    public function to(string|Currency|array $symbols = []): static
    {
        return $this->currencies($symbols);
    }

    public function amount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function date(?DateTimeInterface $date): static
    {
        $this->date = DateHelper::format($date);

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @return list<string>
     */
    public function getSymbols(): array
    {
        return $this->currencies;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function secure(): static
    {
        $this->protocol = 'https';

        return $this;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function config(string $key, string $value): static
    {
        $this->httpParams[$key] = $value;

        return $this;
    }

    public function accessKey(string $accessKey): static
    {
        return $this->config('access_key', $accessKey);
    }

    /**
     * Performs an HTTP GET against the driver's API and decodes the JSON body.
     *
     * @param array<string, scalar> $params
     *
     * @return array<array-key, mixed>
     *
     * @throws ApiException
     */
    protected function apiRequest(string $endpoint, array $params = []): array
    {
        $query = http_build_query([...$this->httpParams, ...$params]);
        $uri   = sprintf(
            '%s://%s/%s%s',
            $this->protocol,
            $this->apiURL,
            ltrim($endpoint, '/'),
            $query === '' ? '' : '?' . $query,
        );

        $request = $this->requestFactory->createRequest('GET', $uri)
          ->withHeader('Accept', 'application/json');

        foreach ($this->httpHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new ApiException(trim($body) === '' ? "API request failed with HTTP {$statusCode}." : $body, $statusCode);
        }

        try {
            /** @var array<array-key, mixed> $data */
            $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_array($data)) {
            throw new ApiException('Expected JSON object from API, got ' . get_debug_type($data) . '.');
        }

        return $data;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    protected function responseString(array $response, string $key, string $provider): string
    {
        $value = $response[$key] ?? null;
        if (!is_scalar($value)) {
            throw new ApiException("{$provider} response did not contain {$key}.");
        }

        return (string) $value;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    protected function optionalResponseString(array $response, string $key): ?string
    {
        $value = $response[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    protected function responseInt(array $response, string $key, string $provider): int
    {
        $value = $response[$key] ?? null;
        if (!is_scalar($value)) {
            throw new ApiException("{$provider} response did not contain {$key}.");
        }

        return (int) $value;
    }

    /**
     * @param array<array-key, mixed> $response
     *
     * @return array<string, BigDecimal|float|int|string>
     */
    protected function responseRates(array $response, string $key, string $provider): array
    {
        $rates = $response[$key] ?? null;
        if (!is_array($rates)) {
            throw new ApiException("{$provider} response did not contain {$key}.");
        }

        $normalised = [];
        foreach ($rates as $currency => $rate) {
            if (!$rate instanceof BigDecimal && !is_float($rate) && !is_int($rate) && !is_string($rate)) {
                throw new ApiException("{$provider} response did not contain a numeric rate for {$currency}.");
            }

            $normalised[(string) $currency] = $rate;
        }

        return $normalised;
    }
}
