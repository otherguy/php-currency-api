<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Otherguy\Currency\Currency;
use Otherguy\Currency\DriverFactory;

// Frankfurter is free and key-less, so this example runs end-to-end with no setup.
$frankfurter = DriverFactory::make('frankfurter');

$result = $frankfurter
    ->from(Currency::EUR)
    ->to([Currency::USD, Currency::GBP, Currency::JPY])
    ->get();

echo 'Base:       ', $result->getBaseCurrency(), PHP_EOL;
echo 'Date:       ', $result->getDate() ?? '(unknown)', PHP_EOL;
echo 'EUR -> USD: ', $result->rate(Currency::USD), PHP_EOL;
echo 'EUR -> GBP: ', $result->rate(Currency::GBP), PHP_EOL;
echo '100 EUR -> JPY: ', $result->convert(100, Currency::EUR, Currency::JPY), PHP_EOL;

// Rebase the same dataset to USD without re-fetching.
$rebased = $result->setBaseCurrency(Currency::USD);
echo 'USD -> EUR: ', $rebased->rate(Currency::EUR), PHP_EOL;

// For paid providers, swap driver name and pass an access key:
//
// $fixer  = DriverFactory::make('fixerio');
// $fixer->accessKey('your-fixer-io-key')->from(Currency::EUR)->to(Currency::USD)->get();
//
// $currencyApi = DriverFactory::make('currencyapi');
// $currencyApi->accessKey('your-currencyapi-key')->from(Currency::USD)->to(Currency::EUR)->get();
//
// $fastForex = DriverFactory::make('fastforex');
// $fastForex->accessKey('your-fastforex-key')->from(Currency::USD)->to(Currency::EUR)->get();
