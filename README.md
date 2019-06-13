# 💱 PHP Currency API
_A PHP 7 API Wrapper for Popular Currency Rate APIs._

[![Version](https://img.shields.io/packagist/v/otherguy/php-currency-api.svg?style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![GitHub issues](https://img.shields.io/github/issues/otherguy/php-currency-api.svg?style=flat-square)](https://github.com/otherguy/php-currency-api/issues)
[![Travis CI](https://img.shields.io/travis/otherguy/php-currency-api.svg?style=flat-square)](https://travis-ci.com/otherguy/php-currency-api)
[![Coverage](https://img.shields.io/coveralls/otherguy/php-currency-api.svg?style=flat-square)](https://coveralls.io/github/otherguy/php-currency-api?branch=master)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/otherguy/php-currency-api.svg?style=flat-square)](https://codeclimate.com/github/otherguy/php-currency-api)
[![License](https://img.shields.io/github/license/otherguy/php-currency-api.svg?style=flat-square)](LICENSE.md)

Dont worry about your favorite currency conversion service suddenly shutting down or switching plans on you. Switch away easily.

## Inspiration 💅

I needed a currency conversion API for [my travel website]() but couldn't find a good PHP package. The idea of the
[`Rackbeat/php-currency-api`](https://github.com/Rackbeat/php-currency-api) package came closest but unfortunately it
was just a stub and not implemented.

## Supported APIs 🌐

| Service                                              | Identifier          |
|------------------------------------------------------|---------------------|
| [FixerIO](https://fixer.io)                          | `fixerio`           |
| [CurrencyLayer](https://currencylayer.com)           | `currencylayer`     |
| [Open Exchange Rates](https://openexchangerates.org) | `openexchangerates` |
| [Rates API](http://ratesapi.io)                      | `ratesapi`          |
| [Exchange Rates API](https://exchangeratesapi.io)    | `exchangeratesapi`  |

_If you want to see more services added, feel free to [open an issue](https://github.com/otherguy/php-currency-api/issues)!_

## Prerequisites 📚

* `PHP 7.1` or higher (Tested on: PHP `7.1` ✅, `7.2` ✅ and `7.3` ✅)
* [`composer`](https://getcomposer.org)
* An account with one or more of the API providers above

## Installation 🚀

Just require the package using `composer` and you're good to go!

```bash
$ composer require otherguy/php-currency-api
```

## Usage 🛠

### Initialize API Instance

```php
$api = Otherguy\Currency\API::make('fixerio'); // driver identifier from supported drivers.
```

### Set base currency (default = USD)

```php
$api->setBase(Otherguy\Currency\Symbol::USD);
```

### Set symbols to return (default = all/[])

```php
$api->setSymbols([ Otherguy\Currency\Symbol::DKK, Otherguy\Currency\Symbol::EUR, Otherguy\Currency\Symbol::USD ]);
```

*Please note, you are not required to use `Otherguy\Currency\Symbol` to specify symbols. It's simply a convenience helper.*

### Get latest rates

```php
$api->get(); // Get latest rates for selected symbols, using set base currency
$api->get('DKK');  // Get latest rates for selected symbols, using DKK as base currency
```

### Convert amount from one currency to another

```php
$api->convert($fromCurrency = 'DKK', $toCurrency = 'EUR', 10.00); // Convert 10 DKK to EUR
```

### Get rate on specific date

```php
$api->historical($date = '2018-01-01'); // Get currency rate for base on 1st of January 2018
$api->historical($date = '2018-01-01', 'GBP'); // Get currency rate for GBP on 1st of January 2018
```

## Contributing 🚧

[Pull Requests](https://github.com/otherguy/php-currency-api/pulls) are more than welcome! I'm striving for 100% test 
coverage for this repository so please make sure to add tests for your code. 
