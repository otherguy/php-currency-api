# 💱 Wrapper for popular Currency Exchange Rate APIs

_A PHP API Wrapper offering a unified, fluent programming interface for popular currency rate APIs._

[![Version](https://img.shields.io/packagist/v/otherguy/php-currency-api.svg?style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![Installs](https://img.shields.io/packagist/dt/otherguy/php-currency-api?color=blue&label=installs&style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![PHP version](https://img.shields.io/packagist/php-v/otherguy/php-currency-api?style=flat-square)](https://packagist.org/packages/otherguy/php-currency-api)
[![CI](https://img.shields.io/github/actions/workflow/status/otherguy/php-currency-api/ci.yml?branch=master&style=flat-square)](https://github.com/otherguy/php-currency-api/actions)
[![Coverage](https://img.shields.io/coveralls/otherguy/php-currency-api.svg?style=flat-square)](https://coveralls.io/github/otherguy/php-currency-api?branch=master)
[![License](https://img.shields.io/github/license/otherguy/php-currency-api.svg?style=flat-square&color=orange)](LICENSE.md)

Don't worry about your favorite currency conversion service shutting down or changing plans. Switch providers without changing your code.

## What's new in 2.0

- **PHP 8.3+** with strict types everywhere.
- **PSR-18 / PSR-17** HTTP layer — bring your own client (Guzzle, Symfony, anything PSR-compliant).
- **`brick/math` `BigDecimal`** for precise rate math instead of floats.
- **`Currency` backed enum** replaces the old `Symbol` constants class (which is kept as a deprecation shim).
- **New `frankfurter` driver** — free, no API key required.
- **New `currencyapi` and `fastforex` drivers** — provider parity with TripTally's backend FX stack.
- **Rewritten `exchangeratesapi` driver** — now points at the working `api.apilayer.com` endpoint with full `convert()` support.
- **Pluggable `DriverFactory`** — register your own provider at runtime.

The fluent chain shape is unchanged from 1.x. The main upgrade steps are covered below.

## Upgrading from 1.x

| Area | 1.x | 2.0 |
|---|---|---|
| PHP | `>=7.3` | `^8.3` |
| HTTP client | Hard-wired Guzzle | Any PSR-18 client + PSR-17 factory |
| Currency catalog | `Symbol` constants | `Currency` backed enum (`Symbol` remains as a deprecation shim) |
| Rate type | `float` | `Brick\Math\BigDecimal` |
| Dates | strings, timestamps, intervals, date objects | `?DateTimeInterface` |
| Default protocol | `http` | `https` |

Required changes:

1. Bump composer constraints:

   ```bash
   composer require otherguy/php-currency-api:^2.0
   composer require guzzlehttp/guzzle:^7.9 http-interop/http-factory-guzzle:^1.2
   ```

   If you do not want Guzzle, install any PSR-18 client and PSR-17 factory and pass them to `DriverFactory::build()`.

2. Wrap date strings in `DateTimeImmutable`:

   ```diff
   - $driver->historical('2018-07-01');
   + $driver->historical(new DateTimeImmutable('2018-07-01'));

   - $driver->convert(122.50, 'NPR', 'EUR', '2019-01-01');
   + $driver->convert(122.50, 'NPR', 'EUR', new DateTimeImmutable('2019-01-01'));
   ```

3. Treat rates as `BigDecimal`:

   ```diff
   - $rate = $result->rate('EUR'); // float
   - $total = $rate * 100;
   + $rate = $result->rate('EUR'); // BigDecimal
   + $total = $rate->multipliedBy(100);
   ```

   For legacy reporting or JSON output, use `rateAsFloat()` or `allAsFloats()`.

4. Prefer `Currency` over `Symbol` in new code:

   ```diff
   - use Otherguy\Currency\Symbol;
   - $driver->to(Symbol::EUR);
   + use Otherguy\Currency\Currency;
   + $driver->to(Currency::EUR);
   ```

   Plain strings such as `'USD'` still work everywhere a currency is accepted, so this can be incremental.

5. Update direct driver construction if you used it:

   ```diff
   - $driver = new FixerIo(new GuzzleHttp\Client());
   + $driver = (new DriverFactory())->build('fixerio', new GuzzleHttp\Client(), new Http\Factory\Guzzle\RequestFactory());
   ```

   `DriverFactory::make('fixerio')` still works and auto-discovers Guzzle.

Provider notes:

- `exchangeratesapi` now targets APILayer's `api.apilayer.com/exchangerates_data` API. `accessKey()` now sends the required `apikey` parameter, and `convert()` is implemented.
- 2.0 adds `frankfurter`, `currencyapi`, and `fastforex` as built-in driver identifiers.
- `apiRequest()` is now protected. Call `get()`, `historical()`, or `convert()` from consumers, or extend the driver for custom behavior.

## Features

* Multiple drivers behind a single interface — switch providers by changing one string.
* Fluent setter chain (`source`, `to`, `amount`, `date`, …) on every driver.
* `ConversionResult` value object with lossless rebasing (`setBaseCurrency()`).
* Hermetic test surface — inject any PSR-18 client, including in-memory mocks.

## Supported APIs

| Service                                              | Identifier          | Free tier without key |
|------------------------------------------------------|---------------------|-----------------------|
| [Frankfurter](https://www.frankfurter.dev)           | `frankfurter`       | ✅                     |
| [FixerIO](https://fixer.io)                          | `fixerio`           | —                     |
| [CurrencyLayer](https://currencylayer.com)           | `currencylayer`     | —                     |
| [Open Exchange Rates](https://openexchangerates.org) | `openexchangerates` | —                     |
| [APILayer Exchange Rates](https://apilayer.com/marketplace/exchangerates_data-api) | `exchangeratesapi`  | — |
| [CurrencyAPI](https://currencyapi.com)               | `currencyapi`       | —                     |
| [fastFOREX](https://fastforex.io)                    | `fastforex`         | —                     |

A `mock` driver is also bundled for testing without network access.

_Want another provider? [Open an issue](https://github.com/otherguy/php-currency-api/issues) — or register a custom driver at runtime (see below)._

## Requirements

* PHP **8.3** or higher.
* A PSR-18 HTTP client and PSR-17 request factory of your choice.
* An API account with the chosen provider, except for `frankfurter`.

## Installation

```bash
composer require otherguy/php-currency-api
```

You also need a PSR-18 client and PSR-17 factory. The most common choice is Guzzle:

```bash
composer require guzzlehttp/guzzle http-interop/http-factory-guzzle
```

Alternatively, with Symfony HttpClient:

```bash
composer require symfony/http-client nyholm/psr7
```

## Quickstart

```php
use Otherguy\Currency\Currency;
use Otherguy\Currency\DriverFactory;

$result = DriverFactory::make('frankfurter')
    ->from(Currency::USD)
    ->to([Currency::EUR, Currency::GBP])
    ->get();

echo $result->rate(Currency::EUR);     // BigDecimal '0.92'
echo $result->convert(100, Currency::USD, Currency::EUR); // BigDecimal '92.00'
```

`DriverFactory::make()` auto-discovers Guzzle if it's installed and wires up a default PSR-18 client. To inject your own:

```php
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Otherguy\Currency\DriverFactory;

$factory = new DriverFactory();
$driver  = $factory->build('fixerio', new Client(), new RequestFactory());

$result = $driver->accessKey('YOUR_KEY')
    ->from(Currency::EUR)
    ->to(Currency::USD)
    ->get();
```

### Bring your own HTTP client (Symfony + nyholm/psr7)

```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Otherguy\Currency\DriverFactory;
use Symfony\Component\HttpClient\Psr18Client;

$psr17  = new Psr17Factory();
$client = new Psr18Client();

$driver = (new DriverFactory())->build('frankfurter', $client, $psr17);
```

## Usage

### The `Currency` enum

`Otherguy\Currency\Currency` is a backed enum with one case per ISO-4217 code (plus a few common crypto/precious-metal codes).

```php
use Otherguy\Currency\Currency;

Currency::USD->value;          // 'USD'
Currency::USD->displayName();  // 'United States Dollar'
Currency::tryFromCode('EUR');  // Currency::EUR
Currency::tryFromCode('XYZ');  // null
Currency::cases();             // every supported currency
```

Every method that takes a currency accepts either the enum or its string code, so plain `'USD'` keeps working.

### Setting the access key

Most providers require authentication. `accessKey()` is sugar for `config('access_key', …)` and is wired per-driver to the right query-string parameter.

```php
$driver->accessKey('YOUR_KEY');
```

Frankfurter has no API key — calling `accessKey()` on it throws `ApiException`. CurrencyAPI is the exception to the query-string rule: its driver sends the key in the `apikey` request header.

Provider-specific key mapping:

| Driver | `accessKey()` mapping |
|---|---|
| `fixerio` | `access_key` query parameter |
| `currencylayer` | `access_key` query parameter |
| `openexchangerates` | `app_id` query parameter |
| `exchangeratesapi` | `apikey` query parameter |
| `currencyapi` | `apikey` request header |
| `fastforex` | `api_key` query parameter |
| `frankfurter` | no key; throws `ApiException` |

### Configuration options

For provider-specific options use `config()`:

```php
$driver->config('format', '1'); // CurrencyLayer pretty-printed JSON
```

### Base currency

`from()` and `source()` are aliases.

```php
$driver->from(Currency::USD);
$driver->source('USD');
```

Each driver has its own default base currency: `EUR` for FixerIO, APILayer Exchange Rates, and Frankfurter; `USD` for CurrencyLayer, Open Exchange Rates, CurrencyAPI, fastFOREX, and the mock driver. Most providers only allow base-currency changes on paid plans — they'll respond with an error envelope which the driver translates into `ApiException`.

### Target currencies

`to()` and `currencies()` are aliases. Pass a single currency, an array, or variadic arguments. Pass nothing (or an empty array) to ask for every currency the provider supports.

```php
$driver->to(Currency::BTC);
$driver->currencies([Currency::BTC, Currency::EUR, Currency::USD]);
$driver->to(Currency::EUR, Currency::GBP);
```

### Latest rates

```php
$driver->get();              // current rates for the configured target currencies
$driver->get(Currency::DKK); // override base currency for this call
```

### Historical rates

Dates must be `DateTimeInterface` (or `null`).

```php
use DateTimeImmutable;

$driver->date(new DateTimeImmutable('2010-01-01'))->historical();
$driver->historical(new DateTimeImmutable('2018-07-01'));
```

### Convert amount

```php
$driver->convert(10.00, Currency::USD, Currency::THB);
$driver->convert(122.50, Currency::NPR, Currency::EUR, new DateTimeImmutable('2019-01-01'));
```

For providers without a native `/convert` endpoint (e.g. Frankfurter), the driver fetches the rate via `get()` / `historical()` and multiplies client-side using `BigDecimal`.

CurrencyAPI and fastFOREX both expose native latest conversion endpoints. For dated conversions, their drivers fetch historical rates and return a `ConversionResult` for the requested pair.

### Fluent chain

```php
DriverFactory::make('fixerio')->from(Currency::USD)->to(Currency::EUR)->get();
DriverFactory::make('fixerio')->from(Currency::USD)->to(Currency::NPR)->date(new DateTimeImmutable('2013-03-02'))->historical();
DriverFactory::make('fixerio')->from(Currency::USD)->to(Currency::NPR)->amount(12.10)->convert();
```

### `ConversionResult`

`get()` and `historical()` return a [`ConversionResult`](src/Results/ConversionResult.php). Rates are stored as `BigDecimal` and rebasing is lossless (default scale: 8 decimals).

```php
use Brick\Math\BigDecimal;

$result = DriverFactory::make('frankfurter')
    ->from(Currency::USD)
    ->to([Currency::EUR, Currency::GBP])
    ->get();

$result->all();                     // ['USD' => BigDecimal '1', 'EUR' => BigDecimal '0.89', 'GBP' => BigDecimal '0.79']
$result->allAsFloats();             // legacy float view
$result->getBaseCurrency();         // 'USD'
$result->getDate();                 // '2026-04-25'
$result->rate(Currency::EUR);       // BigDecimal '0.89'
$result->rateAsFloat(Currency::EUR);// 0.89

$result->convert(5.0, Currency::EUR, Currency::USD); // BigDecimal '5.618...'

$rebased = $result->setBaseCurrency(Currency::EUR);
$rebased->getBaseCurrency();        // 'EUR'
$rebased->originalBaseCurrency;     // 'USD' — readonly, never mutated
```

`rate()` on a code that wasn't fetched throws `Otherguy\Currency\Exceptions\CurrencyException`. To convert between two arbitrary currencies, request both in the original `get()` / `historical()` call.

## Registering custom drivers

The factory is instance-based. Bring your own driver class (extending `BaseCurrencyDriver`) and register it:

```php
use Otherguy\Currency\DriverFactory;

$factory = new DriverFactory();
$factory->register('mybank', \Acme\MyBankDriver::class);

$driver = $factory->build('mybank');
```

The static `DriverFactory::make($name)` continues to work via a process-wide default factory — `DriverFactory::setDefault($factory)` lets you swap it for tests.

### Adding a new driver

A driver is the bridge between this library's fluent interface and a specific upstream rate provider. Every driver implements [`CurrencyDriverContract`](src/Drivers/CurrencyDriverContract.php) by extending [`BaseCurrencyDriver`](src/Drivers/BaseCurrencyDriver.php).

The base class supplies:

- All fluent setters (`source`, `from`, `currencies`, `to`, `amount`, `date`, `config`, `accessKey`, `secure`).
- A PSR-18 / PSR-17 HTTP layer in `apiRequest()` that builds the URI, merges `$httpParams` with per-call params, decodes JSON with `JSON_THROW_ON_ERROR`, and wraps every failure mode in `ApiException`.

You only need to:

1. Set the right defaults for `$apiURL`, `$protocol`, and `$baseCurrency`.
2. Implement `get()`, `historical()`, and `convert()`.
3. Override `apiRequest()` only if the provider's successful HTTP response can still carry an error envelope, such as `success: false`.

### Driver skeleton

```php
<?php

declare(strict_types=1);

namespace Otherguy\Currency\Drivers;

use DateTimeInterface;
use Otherguy\Currency\Currency;
use Otherguy\Currency\Exceptions\ApiException;
use Otherguy\Currency\Results\ConversionResult;

class MyProvider extends BaseCurrencyDriver
{
    protected string $apiURL       = 'api.myprovider.example/v1';
    protected string $protocol     = 'https';
    protected string $baseCurrency = 'USD';

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
            throw new ApiException('Date is required for historical().');
        }

        $response = $this->apiRequest('history/' . $this->getDate(), [
            'base' => $this->getBaseCurrency(),
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
        if ($amount !== null) {
            $this->amount = $amount;
        }
        if ($fromCurrency !== null) {
            $this->source($fromCurrency);
        }
        if ($toCurrency !== null) {
            $this->to($toCurrency);
        }
        if ($date instanceof DateTimeInterface) {
            $this->date($date);
        }

        if ($this->amount === null) {
            throw new ApiException('An amount is required for convert().');
        }
        if ($this->currencies === []) {
            throw new ApiException('A target currency is required for convert().');
        }

        $target = $this->getSymbols()[0];
        $response = $this->apiRequest('convert', [
            'from'   => $this->getBaseCurrency(),
            'to'     => $target,
            'amount' => $this->amount,
        ]);

        return new ConversionResult(
            $this->getBaseCurrency(),
            isset($response['date']) ? (string) $response['date'] : null,
            [$target => $response['result']],
        );
    }
}
```

For providers without a native conversion endpoint, fetch a rate through `get()` / `historical()` and multiply client-side with `BigDecimal`; [`Frankfurter`](src/Drivers/Frankfurter.php) is the compact example.

### Driver authentication

`accessKey()` defaults to writing `access_key=...` into `$httpParams`. If your provider uses a different parameter name, override it:

```php
#[\Override]
public function accessKey(string $accessKey): static
{
    return $this->config('apikey', $accessKey);
}
```

For header authentication, write to `$httpHeaders`:

```php
#[\Override]
public function accessKey(string $accessKey): static
{
    $this->httpHeaders['apikey'] = $accessKey;

    return $this;
}
```

If the provider has no keys, throw to make misuse loud:

```php
#[\Override]
public function accessKey(string $accessKey): static
{
    throw new ApiException('MyProvider does not require an API key.');
}
```

### Provider-specific error envelopes

Many providers return HTTP 200 with an error body. Override `apiRequest()` to translate those into `ApiException` before the value reaches `get()` / `historical()` / `convert()`:

```php
#[\Override]
protected function apiRequest(string $endpoint, array $params = []): array
{
    $response = parent::apiRequest($endpoint, $params);

    if (($response['success'] ?? null) !== true) {
        $info = (string) ($response['error']['info'] ?? 'Unknown API error.');
        throw new ApiException($info);
    }

    return $response;
}
```

### First-party driver registration

For first-party drivers shipped with this package, add the class to the built-in map in [`DriverFactory`](src/DriverFactory.php):

```php
public function __construct(?array $drivers = null)
{
    $this->drivers = $drivers ?? [
        // ...
        'myprovider' => MyProvider::class,
    ];
}
```

For third-party drivers, use runtime registration as shown above. `register()` returns `$this`, `unregister(string $name)` removes a driver, and `build()` accepts optional PSR-18 + PSR-17 collaborators. If those collaborators are omitted, the factory tries to auto-discover Guzzle.

### Driver tests

Driver tests live under `tests/Drivers/`. Use [`tests/Support/DriverHarness.php`](tests/Support/DriverHarness.php) to wire up an in-process PSR-18 mock:

```php
use Otherguy\Currency\Currency;
use Otherguy\Currency\Tests\Support\DriverHarness;
use Otherguy\Currency\Tests\Support\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MyProviderTest extends TestCase
{
    private DriverHarness $harness;

    protected function setUp(): void
    {
        $this->harness = new DriverHarness();
    }

    #[Test]
    public function get_parses_provider_envelope(): void
    {
        $this->harness->http->enqueue(JsonResponse::ok(json_encode([
            'base'  => 'USD',
            'date'  => '2026-04-01',
            'rates' => ['EUR' => 0.92],
        ], JSON_THROW_ON_ERROR)));

        $result = $this->harness->make('myprovider')
            ->accessKey('test-key')
            ->from(Currency::USD)
            ->to(Currency::EUR)
            ->get();

        $this->assertSame('0.92', (string) $result->rate(Currency::EUR));

        $request = $this->harness->http->lastRequest();
        $this->assertNotNull($request);
        $this->assertStringContainsString('apikey=test-key', $request->getUri()->getQuery());
    }
}
```

`DriverHarness` instantiates a fresh `MockHttpClient` on each test. Use `enqueue()` to queue responses, `lastRequest()` to assert URI/query/headers, and `sentRequests()` for multi-request flows.

### Driver checklist

- [ ] `declare(strict_types=1)` and `Override` attributes where you override.
- [ ] `$apiURL` does not include the `https://` prefix; `BaseCurrencyDriver` adds the protocol.
- [ ] `get()`, `historical()`, and `convert()` return `ConversionResult`, not arrays.
- [ ] Error envelopes are wrapped in `ApiException` so callers see one consistent failure type.
- [ ] PHPStan is clean at `level: max` (`composer analyse`).
- [ ] Tests cover happy path, error envelope, and any `accessKey()` quirks.
- [ ] First-party drivers are registered in `DriverFactory` and listed in the Supported APIs table.

For real examples, browse the existing drivers. They range from thin happy-path code in [`Frankfurter`](src/Drivers/Frankfurter.php), to header authentication in [`CurrencyApi`](src/Drivers/CurrencyApi.php), to envelope translation in [`FixerIo`](src/Drivers/FixerIo.php), [`CurrencyLayer`](src/Drivers/CurrencyLayer.php), and [`ExchangeRatesApi`](src/Drivers/ExchangeRatesApi.php).

## Testing

The library exposes `Otherguy\Currency\Drivers\MockCurrencyDriver` for consumers writing tests without a network. Seed it with rates and use it like any other driver:

```php
use Otherguy\Currency\Drivers\MockCurrencyDriver;

$driver = (new MockCurrencyDriver(/* PSR-18 + factory */))
    ->withRates(['EUR' => '0.92', 'GBP' => '0.79']);

$driver->get()->rate('EUR'); // BigDecimal '0.92'
```

For testing this library itself, see `tests/Support/MockHttpClient.php` — a tiny in-process PSR-18 double that records sent requests and replays queued responses. CONTRIBUTING.md walks through it.

## Project commands

| Command                       | What it does                          |
|-------------------------------|---------------------------------------|
| `composer test`               | Run the test suite                    |
| `composer test:coverage`      | Run with coverage (requires Xdebug)   |
| `composer lint`               | Pint code-style check (read-only)     |
| `composer lint:fix`           | Pint auto-fix                         |
| `composer analyse`            | PHPStan at level max                  |
| `composer rector`             | Rector dry-run                        |
| `composer rector:fix`         | Rector apply                          |
| `composer check`              | All of the above, in order            |

## Contributing

Pull requests are welcome — please run `composer check` before opening one. Coverage target is ≥ 98% on `src/`. See [`CONTRIBUTING.md`](CONTRIBUTING.md) for the full guide.

## License

[MIT](LICENSE.md).
