<?php

declare(strict_types=1);

namespace Otherguy\Currency;

use GuzzleHttp\Client as GuzzleClient;
use Http\Factory\Guzzle\RequestFactory as GuzzleRequestFactory;
use Otherguy\Currency\Drivers\CurrencyApi;
use Otherguy\Currency\Drivers\CurrencyDriverContract;
use Otherguy\Currency\Drivers\CurrencyLayer;
use Otherguy\Currency\Drivers\ExchangeRatesApi;
use Otherguy\Currency\Drivers\FastForex;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Drivers\Frankfurter;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Drivers\OpenExchangeRates;
use Otherguy\Currency\Exceptions\DriverNotFoundException;
use Otherguy\Currency\Exceptions\MissingDependencyException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class DriverFactory
{
    /**
     * @var array<string, class-string<CurrencyDriverContract>>
     */
    private array $drivers;

    private static ?self $defaultInstance = null;

    /**
     * @param array<string, class-string<CurrencyDriverContract>>|null $drivers
     */
    public function __construct(?array $drivers = null)
    {
        $this->drivers = $drivers ?? [
            'mock'              => MockCurrencyDriver::class,
            'fixerio'           => FixerIo::class,
            'currencylayer'     => CurrencyLayer::class,
            'openexchangerates' => OpenExchangeRates::class,
            'exchangeratesapi'  => ExchangeRatesApi::class,
            'frankfurter'       => Frankfurter::class,
            'currencyapi'       => CurrencyApi::class,
            'fastforex'         => FastForex::class,
        ];
    }

    /**
     * @param class-string<CurrencyDriverContract> $driverClass
     */
    public function register(string $name, string $driverClass): self
    {
        $this->drivers[$name] = $driverClass;

        return $this;
    }

    public function unregister(string $name): self
    {
        unset($this->drivers[$name]);

        return $this;
    }

    /**
     * @return array<string, class-string<CurrencyDriverContract>>
     */
    public function drivers(): array
    {
        return $this->drivers;
    }

    /**
     * @throws DriverNotFoundException
     */
    public function build(
        string $name,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ): CurrencyDriverContract {
        if (!isset($this->drivers[$name])) {
            throw new DriverNotFoundException("{$name} is not a valid driver.");
        }

        $class   = $this->drivers[$name];
        $client  = $httpClient ?? $this->defaultClient();
        $factory = $requestFactory ?? $this->defaultRequestFactory();

        return new $class($client, $factory);
    }

    /**
     * Static facade preserved for backwards compatibility.
     *
     * @throws DriverNotFoundException
     */
    public static function make(
        string $name,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ): CurrencyDriverContract {
        return self::default()->build($name, $httpClient, $requestFactory);
    }

    /**
     * @return array<string, class-string<CurrencyDriverContract>>
     */
    public static function getDrivers(): array
    {
        return self::default()->drivers();
    }

    public static function default(): self
    {
        return self::$defaultInstance ??= new self();
    }

    public static function setDefault(?self $instance): void
    {
        self::$defaultInstance = $instance;
    }

    private function defaultClient(): ClientInterface
    {
        if (!class_exists(GuzzleClient::class)) {
            throw new MissingDependencyException(
                'No PSR-18 HTTP client supplied and guzzlehttp/guzzle is '
                . 'not installed. Either install guzzlehttp/guzzle, or pass '
                . 'a ClientInterface to DriverFactory::make().',
            );
        }

        $client = $this->buildDefaultClient();
        if (!$client instanceof ClientInterface) {
            throw new MissingDependencyException(
                'The installed guzzlehttp/guzzle package does not provide a PSR-18 '
                . 'ClientInterface implementation.',
            );
        }

        return $client;
    }

    private function defaultRequestFactory(): RequestFactoryInterface
    {
        if (!class_exists(GuzzleRequestFactory::class)) {
            throw new MissingDependencyException(
                'No PSR-17 RequestFactory supplied and '
                . 'http-interop/http-factory-guzzle is not installed. '
                . 'Either install http-interop/http-factory-guzzle, or pass '
                . 'a RequestFactoryInterface to DriverFactory::make().',
            );
        }

        $requestFactory = $this->buildDefaultRequestFactory();
        if (!$requestFactory instanceof RequestFactoryInterface) {
            throw new MissingDependencyException(
                'The installed http-interop/http-factory-guzzle package does not '
                . 'provide a PSR-17 RequestFactoryInterface implementation.',
            );
        }

        return $requestFactory;
    }

    private function buildDefaultClient(): object
    {
        $class = GuzzleClient::class;

        return new $class();
    }

    private function buildDefaultRequestFactory(): object
    {
        $class = GuzzleRequestFactory::class;

        return new $class();
    }
}
