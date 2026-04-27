<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests;

use Http\Factory\Guzzle\RequestFactory;
use Otherguy\Currency\DriverFactory;
use Otherguy\Currency\Drivers\CurrencyApi;
use Otherguy\Currency\Drivers\CurrencyDriverContract;
use Otherguy\Currency\Drivers\FastForex;
use Otherguy\Currency\Drivers\Frankfurter;
use Otherguy\Currency\Drivers\MockCurrencyDriver;
use Otherguy\Currency\Exceptions\DriverNotFoundException;
use Otherguy\Currency\Tests\Support\MockHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DriverFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        DriverFactory::setDefault(null);
    }

    #[Test]
    public function default_factory_can_build_a_driver(): void
    {
        $this->assertInstanceOf(CurrencyDriverContract::class, DriverFactory::make('mock'));
    }

    #[Test]
    public function unknown_driver_throws_driver_not_found_exception(): void
    {
        $this->expectException(DriverNotFoundException::class);
        DriverFactory::make('nonexistent-currency-api-driver');
    }

    #[Test]
    public function default_drivers_include_built_in_set(): void
    {
        $drivers = DriverFactory::getDrivers();

        foreach (['mock', 'fixerio', 'currencylayer', 'openexchangerates', 'exchangeratesapi', 'frankfurter', 'currencyapi', 'fastforex'] as $name) {
            $this->assertArrayHasKey($name, $drivers);
        }
    }

    #[Test]
    public function default_factory_can_build_triptally_provider_drivers(): void
    {
        $factory = new DriverFactory();
        $http    = new MockHttpClient();

        $this->assertInstanceOf(
            CurrencyApi::class,
            $factory->build('currencyapi', $http, new RequestFactory()),
        );
        $this->assertInstanceOf(
            FastForex::class,
            $factory->build('fastforex', $http, new RequestFactory()),
        );
    }

    #[Test]
    public function build_accepts_custom_psr18_client_and_psr17_factory(): void
    {
        $factory = new DriverFactory();
        $http    = new MockHttpClient();

        $driver = $factory->build('frankfurter', $http, new RequestFactory());

        $this->assertInstanceOf(Frankfurter::class, $driver);
    }

    #[Test]
    public function register_adds_a_custom_driver_class(): void
    {
        $factory = new DriverFactory();
        $factory->register('custom-mock', MockCurrencyDriver::class);

        $this->assertArrayHasKey('custom-mock', $factory->drivers());
        $this->assertInstanceOf(
            MockCurrencyDriver::class,
            $factory->build('custom-mock', new MockHttpClient(), new RequestFactory()),
        );
    }

    #[Test]
    public function unregister_removes_a_driver(): void
    {
        $factory = new DriverFactory();
        $factory->register('temp', MockCurrencyDriver::class);
        $factory->unregister('temp');

        $this->assertArrayNotHasKey('temp', $factory->drivers());

        $this->expectException(DriverNotFoundException::class);
        $factory->build('temp', new MockHttpClient(), new RequestFactory());
    }

    #[Test]
    public function set_default_replaces_static_singleton(): void
    {
        $custom = new DriverFactory(['only' => MockCurrencyDriver::class]);
        DriverFactory::setDefault($custom);

        $this->assertSame(['only' => MockCurrencyDriver::class], DriverFactory::getDrivers());
    }
}
