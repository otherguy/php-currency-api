# Contributing

Thanks for considering a contribution! This guide covers the local workflow and the bar this project holds itself to.

## Getting started

```bash
git clone https://github.com/otherguy/php-currency-api.git
cd php-currency-api
composer install
composer check
```

`composer check` runs the same gates as CI (Pint, PHPStan, Rector, PHPUnit). It should be green before you push.

## Project commands

| Command                  | What it does                              |
|--------------------------|-------------------------------------------|
| `composer test`          | PHPUnit                                   |
| `composer test:coverage` | PHPUnit with coverage (requires Xdebug)   |
| `composer lint`          | Pint check (read-only)                    |
| `composer lint:fix`      | Pint apply                                |
| `composer analyse`       | PHPStan at `level: max`                   |
| `composer rector`        | Rector dry-run                            |
| `composer rector:fix`    | Rector apply                              |
| `composer check`         | All four checks in order                  |

## Code style

- PHP **8.3+**, `declare(strict_types=1)` in every file.
- PSR-12 with 4-space indentation. Pint enforces this; don't fight it.
- Real types over `@var` docblocks. Constructor property promotion is preferred when it reads naturally.
- Names describe behavior, not implementation. No `Manager`, `Wrapper`, `Helper` unless it genuinely is one.
- Comments are evergreen — explain *why* the code looks weird, not *what* it does, and never reference past versions of the code.

## Testing

- Tests live under `tests/`, namespaced `Otherguy\Currency\Tests\…`.
- Use PHPUnit `#[Test]` attributes (no `/** @test */`).
- HTTP is mocked with [`tests/Support/MockHttpClient.php`](tests/Support/MockHttpClient.php), an in-process PSR-18 double. Build drivers via `tests/Support/DriverHarness.php`:

  ```php
  $harness = new DriverHarness();
  $harness->http->enqueue(JsonResponse::ok('{"success":true,"rates":{"EUR":0.92}}'));
  $driver = $harness->make('fixerio');

  $result = $driver->accessKey('key')->from('USD')->to('EUR')->get();

  $this->assertSame('0.92', (string) $result->rate(Currency::EUR));
  $this->assertStringContainsString('access_key=key', $harness->http->lastRequest()->getUri()->getQuery());
  ```

- Coverage target: **≥ 98% on `src/`**. New code without tests is unlikely to be merged.
- Tests must exercise real code paths. Don't write tests that only verify mock behavior.

## Static analysis

- PHPStan runs at `level: max`. If you hit a genuine `mixed` from upstream JSON, prefer narrowing with assertions or specific type guards. The existing `ignoreErrors` block in `phpstan.neon` is scoped to `src/Drivers/*.php` for unverifiable provider responses — please don't widen it.
- Rector checks are advisory in CI but blocking on PR. If Rector suggests a rewrite that loses meaning, exclude the rule rather than ignoring the diff.

## Adding a driver

See the [driver guide](README.md#adding-a-new-driver) in the root README for the full walkthrough. The short version:

1. Extend `BaseCurrencyDriver`, set `$apiURL`, `$protocol`, default `$baseCurrency`.
2. Implement `get()`, `historical()`, `convert()` against the provider's endpoints.
3. Override `apiRequest()` only if the provider's error envelope differs from raw HTTP failures.
4. Register in `DriverFactory`'s built-in map (or expose via `register()` for third-party drivers).
5. Add tests under `tests/Drivers/` using `DriverHarness` and `MockHttpClient`.

## Pull request checklist

- [ ] `composer check` is green locally.
- [ ] New behavior has tests.
- [ ] Public API changes are documented in `README.md` and listed in `CHANGELOG.md` under `## [Unreleased]`.
- [ ] BC breaks include a README upgrade note with a before/after snippet.
- [ ] Commit messages are descriptive (`fix:` / `feat:` / `chore:` prefixes are welcome but not required).

## Releasing (maintainers)

1. Bump the version in the relevant `CHANGELOG.md` heading and move `[Unreleased]` items under it.
2. Tag: `git tag -s vX.Y.Z -m "Release X.Y.Z"`.
3. Push: `git push origin main --tags`.
4. Create a GitHub release pasting the changelog entry.
5. Packagist auto-syncs; verify the new version appears.

## Reporting issues

When filing a bug, please include:

- PHP version (`php -v`).
- Library version (`composer show otherguy/php-currency-api`).
- The PSR-18 client you're using.
- A minimal reproduction (driver, fluent chain, observed vs. expected).
- The full exception trace if any.

Thanks again — this library is healthier with every contribution.
