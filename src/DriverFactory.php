<?php namespace Otherguy\Currency;

use Otherguy\Currency\Drivers\CurrencyLayer;
use Otherguy\Currency\Drivers\DriverInterface;
use Otherguy\Currency\Drivers\FixerIo;
use Otherguy\Currency\Drivers\MockDriver;
use Otherguy\Currency\Drivers\OpenExchangeRates;
use Otherguy\Currency\Exceptions\DriverNotFoundException;

/**
 * Class DriverFactory
 *
 * @package Otherguy\Currency
 */
class DriverFactory
{
    protected const DRIVERS = [
        'mock'              => MockDriver::class,
        'fixerio'           => FixerIo::class,
        'currencylayer'     => CurrencyLayer::class,
        'openexchangerates' => OpenExchangeRates::class,
    ];

    /**
     * @param string $name
     *
     * @return DriverInterface
     *
     * @throws DriverNotFoundException
     */
    public static function make(string $name): DriverInterface
    {
        $name = strtolower($name);

        if (!isset(static::DRIVERS[$name])) {
            throw new DriverNotFoundException("{$name} is not a valid driver.");
        }

        $class = static::DRIVERS[$name];

        return new $class;
    }
}
