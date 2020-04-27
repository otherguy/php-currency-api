# 💱 PHP API Wrapper for popular Currency Exchange Rate APIs

Dont worry about your favorite currency conversion service suddenly shutting down or switching plans on you. Switch away easily, without changing your code.

[![Version](https://img.shields.io/packagist/v/otherguy/php-currency-api.svg?style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![Installs](https://img.shields.io/packagist/dt/otherguy/php-currency-api?color=blue&label=installs&style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![PHP version](https://img.shields.io/packagist/php-v/otherguy/php-currency-api?style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![GitHub issues](https://img.shields.io/github/issues/otherguy/php-currency-api.svg?style=flat-square)](https://github.com/otherguy/php-currency-api/issues)
[![Travis CI](https://img.shields.io/travis/com/otherguy/php-currency-api.svg?style=flat-square)](https://travis-ci.com/otherguy/php-currency-api)
[![Coverage](https://img.shields.io/coveralls/otherguy/php-currency-api.svg?style=flat-square)](https://coveralls.io/github/otherguy/php-currency-api?branch=master)
[![Coverage](https://img.shields.io/codeclimate/coverage-letter/otherguy/php-currency-api.svg?style=flat-square)](https://codeclimate.com/github/otherguy/php-currency-api)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/otherguy/php-currency-api.svg?style=flat-square)](https://codeclimate.com/github/otherguy/php-currency-api)
[![License](https://img.shields.io/github/license/otherguy/php-currency-api.svg?style=flat-square&color=orange)](LICENSE.md)

## Inspiration 💅

I needed a currency conversion API for [my travel website]() but could not find a good PHP package. The idea of the
[`Rackbeat/php-currency-api`](https://github.com/Rackbeat/php-currency-api) package came closest but unfortunately it
was just a stub and not implemented.

## Features 🌈

* Support for [multiple different APIs](#supported-apis-) through the use of drivers
* A [fluent interface](#fluent-interface) to make retrieving exchange rates convenient and fast
* Consistent return interface that is independent of the driver being used
* [Calculations](#conversion-result) can be made based on the returned data

## Supported APIs 🌐

| Service                                              | Identifier          |
|------------------------------------------------------|---------------------|
| [FixerIO](https://fixer.io)                          | `fixerio`           |
| [CurrencyLayer](https://currencylayer.com)           | `currencylayer`     |
| [Open Exchange Rates](https://openexchangerates.org) | `openexchangerates` |
| [Exchange Rates API](https://exchangeratesapi.io)    | `exchangeratesapi`  |

_If you want to see more services added, feel free to [open an issue](https://github.com/otherguy/php-currency-api/issues)!_

## Prerequisites 📚

* `PHP 7.1` or higher (Tested on: PHP `7.1` ✅, `7.2` ✅, `7.3` ✅ and `7.4` ✅)
* The [`composer`](https://getcomposer.org) dependency manager for PHP
* An account with one or more of the [API providers](#supported-apis-) listed above

## Installation 🚀

Simply require the package using `composer` and you're good to go!

```bash
$ composer require otherguy/php-currency-api
```

## Usage 🛠

### Currency Symbol Helper

The [`Otherguy\Currency\Symbol`](src/Symbol.php) class provides constants for each supported currency.

> ！**Note:** You are not required to use `Otherguy\Currency\Symbol` to specify symbols. It's simply a convenience helper
> and does not need to be used. You can simply pass strings like `'USD', 'EUR', ...` to all methods.

```php
// 'USD'
$symbol = Otherguy\Currency\Symbol::USD;
```

Use the `all()` method to retrieve an array of all currency symbols:

```php
// [ 'AED', 'AFN', ... 'ZWL' ]
$symbols = Otherguy\Currency\Symbol::all();
```

The `names()` method returns an associative array with currency names instead:

```php
// [ 'AED' => 'United Arab Emirates Dirham', 'AFN' => 'Afghan Afghani', ... ]
$symbols = Otherguy\Currency\Symbol::names();
```

To get the name of a single currency, use the `name()` method:

```php
// 'United States Dollar'
$symbols = Otherguy\Currency\Symbol::name(Otherguy\Currency\Symbol::USD);
```

### Initialize API Instance

```php
$currency = Otherguy\Currency\DriverFactory::make('fixerio'); // driver identifier from supported drivers.
```

To get a list of supported drivers, use the `getDrivers()` method:

```php
// [ 'mock', 'fixerio', 'currencylayer', ... ]
$drivers = Otherguy\Currency\DriverFactory::getDrivers()
```

### Set Access Key

Most API providers require you to sign up and use your issued access key to authenticate against their API. You can
specify your access key like so:

```php
$currency->accessKey('your-access-token-goes-here');
```

### Set Configuration Options

To set further configuration options, you can use the `config()` method. An example is
[CurrencyLayer's JSON formatting option](https://currencylayer.com/documentation#format).

```php
$currency->config('format', '1');
```

### Set Base Currency

You can use either `from()` or `source()` to set the base currency. The methods are identical.

> ！**Note:** Each driver sets its own default base currency. [FixerIO](https://fixer.io) uses `EUR` as base currency
> while [CurrencyLayer](https://currencylayer.com) uses `USD`.

Most services only allow you to change the base currency in their paid plans. The driver will throw a
`Otherguy\Currency\Exceptions\ApiException` if your current plan does not allow changing the base currency.

```php
$currency->source(Otherguy\Currency\Symbol::USD);
$currency->from(Otherguy\Currency\Symbol::USD);
```

### Set Return Currencies

You can use either `to()` or `symbols()` to set the return currencies. The methods are identical. Pass a single currency
or an array of currency symbols to either of these methods.

> ！**Note:** Pass an empty array to return all currency symbols supported by this driver. This is the default if you
> don't call the method at all.
 
```php
$currency->to(Otherguy\Currency\Symbol::BTC);
$currency->symbols([Otherguy\Currency\Symbol::BTC, Otherguy\Currency\Symbol::EUR, Otherguy\Currency\Symbol::USD]);
```

### Latest Rates

This retrieves the most recent exchange rates and returns a [`ConversionResult`](#conversion-result) object.

```php
$currency->get(); // Get latest rates for selected symbols, using set base currency
$currency->get('DKK');  // Get latest rates for selected symbols, using DKK as base currency
```

### Historical Rates

To retrieve historical exchange rates, use the `historical()` method. Note that you need to specify a date either as a
method parameter or by using the `date()` methods. See [Fluent Interface](#fluent-interface) for more information.

```php
$currency->date('2010-01-01')->historical();
$currency->historical('2018-07-01');
```

### Convert Amount

Use the `convert()` method to convert amounts between currencies.

> ！**Note:** Most API providers don't allow access to this method using your free account. You can still use the 
> [Latest Rates](#latest-rates) or [Historical Rates](#historical-rates) endpoints and perform calculations or conversions
> on the [`ConversionResult`](#conversion-result) object.

```php
$currency->convert(10.00, 'USD', 'THB'); // Convert 10 USD to THB
$currency->convert(122.50, 'NPR', 'EUR', '2019-01-01'); // Convert 122.50 NPR to EUR using the rates from January 1st, 2019
```

### Fluent Interface

Most methods can be used with a _fluent interface_, allowing you to chain method calls for more readable code:

```php
// Namespaces are omitted for readability!
DriverFactory::make('driver')->from(Symbol::USD)->to(Symbol::EUR)->get();
DriverFactory::make('driver')->from(Symbol::USD)->to(Symbol::NPR)->date('2013-03-02')->historical();
DriverFactory::make('driver')->from(Symbol::USD)->to(Symbol::NPR)->amount(12.10)->convert();
```

### Conversion Result

The [`get()`](#latest-rates) and [`historical()`](#historical-rates) endpoints return a 
[`ConversionResult`](src/Results/ConversionResult.php) object. This object allows you to perform calculations and 
conversions easily.

> ！**Note:** Even though free accounts of most providers do not allow you to change the base currency, you can still
> use the `ConversionResult` object to change the base currency later. This might not be as accurate as changing the
> base currency directly, though.

> ！**Note:** To convert between two currencies, you need to request both of them in your initial [`get()`](#latest-rates)
> or [`historical()`](#historical-rates) request. You can not convert between currencies that have not been fetched!

See the following code for some examples of what you can do with the `ConversionResult` object.

```php
$result = DriverFactory::make('driver')->from(Symbol::USD)->to([Symbol::EUR, Symbol::GBP])->get();

// [ 'USD' => 1.00, 'EUR' => 0.89, 'GBP' => 0.79 ]
$result->all();

// 'USD'
$result->getBaseCurrency();

// '2019-06-11'
$result->getDate();

// 0.89
$result->rate(Symbol::EUR);

// CurrencyException("No conversion result for BTC!");
$result->rate(Symbol::BTC);

// 5.618
$result->convert(5.0, Symbol::EUR, Symbol::USD);

// [ 'USD' => 1.13, 'EUR' => 1.00, 'GBP' => 0.89 ]
$result->setBaseCurrency(Symbol::EUR)->all();

// 1.12
$result->setBaseCurrency(Symbol::GBP)->rate(Symbol::EUR);
```

## Contributing 🚧

[Pull Requests](https://github.com/otherguy/php-currency-api/pulls) are more than welcome! I'm striving for 100% test
coverage for this repository so please make sure to add tests for your code.
