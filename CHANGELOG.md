# Changelog

All notable changes to this project will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] — 2026-04-27

A full modernization. See the [README upgrade section](README.md#upgrading-from-1x) for migration steps.

### Added

- `Otherguy\Currency\Currency` backed enum with one case per supported ISO-4217 code, plus `displayName()` and `tryFromCode()` helpers.
- `Frankfurter` driver — free, key-less access to `api.frankfurter.dev`.
- `CurrencyApi` driver — latest, historical, and conversion support for `api.currencyapi.com`.
- `FastForex` driver — latest, historical, and conversion support for `api.fastforex.io`.
- `DriverFactory::register()` / `unregister()` / `build()` — instance-based registry for plugging in custom drivers.
- `ConversionResult::rateAsFloat()` and `allAsFloats()` helpers for callers that need legacy float output.
- `MockCurrencyDriver::withRates()` for seeding test rates.
- `composer check` aggregate script (`lint` + `analyse` + `rector` + `test`).
- GitHub Actions CI workflow (PHP 8.3 + 8.4, Pint, PHPStan, Rector, PHPUnit, Coveralls).
- PHPStan at `level: max`, Laravel Pint, Rector, all wired into CI.
- `tests/Support/MockHttpClient.php` — in-process PSR-18 test double.

### Changed

- **PHP 8.3+** required (was `>=7.3`).
- HTTP layer is now **PSR-18 / PSR-17**. `BaseCurrencyDriver::__construct()` takes a `Psr\Http\Client\ClientInterface` and a `Psr\Http\Message\RequestFactoryInterface`.
- `ConversionResult::rate()`, `convert()`, and `all()` now return `Brick\Math\BigDecimal` instead of `float`.
- Driver `date()`, `historical()`, and `convert()` accept `?DateTimeInterface` instead of strings/ints/intervals.
- Default protocol flipped to `https`.
- `CurrencyDriverContract` no longer declares `apiRequest()`; the base implementation is now `protected`.
- `ExchangeRatesApi` driver rewritten to target `api.apilayer.com/exchangerates_data` (the original endpoint was discontinued in 2021). `accessKey()` and `convert()` are now functional.
- `ConversionResult` properties `originalBaseCurrency` and `originalConversionRates` are `readonly`.
- `BaseCurrencyDriver::apiRequest()` uses `JSON_THROW_ON_ERROR`; failures wrap the underlying `JsonException` as `getPrevious()` on the resulting `ApiException`.
- Test suite uses namespaced classes (`Otherguy\Currency\Tests\…`) with PHPUnit `#[Test]` attributes and an in-process PSR-18 mock.

### Deprecated

- `Otherguy\Currency\Symbol` and its static helpers (`all()`, `name()`, `names()`). Kept as a shim that emits `E_USER_DEPRECATED` on first use; will be removed in 3.0. Use `Otherguy\Currency\Currency` instead.

### Removed

- `nikic/php-parser` dependency (was unused at runtime).
- Duplicate `phpunit/php-code-coverage` declaration (PHPUnit pulls it transitively).
- `Helpers\DateHelper::parse()` and `create()`. Use `new DateTimeImmutable(...)` directly.
- Travis CI configuration (`.travis.yml`), `.mergify.yml`, stale GitHub config files.

### Fixed

- `ExchangeRatesApi` driver no longer hits a dead endpoint.
- Currency math now uses arbitrary-precision decimals; round-trip rebasing (`setBaseCurrency`) is lossless within the configured scale (default 8).

### Security

- HTTP requests default to TLS. `->secure()` is a no-op toggle to HTTPS; there is no opt-out to plaintext without subclassing.

[2.0.0]: https://github.com/otherguy/php-currency-api/releases/tag/v2.0.0
