
# Upgrading from 1.x

| Area | 1.x | 2.0 |
| --- | --- | --- |
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
