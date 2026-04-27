<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests;

use Otherguy\Currency\Currency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    #[Test]
    public function exposes_all_supported_currency_cases(): void
    {
        $this->assertCount(167, Currency::cases());
    }

    #[Test]
    public function backed_value_matches_iso_code(): void
    {
        $this->assertSame('USD', Currency::USD->value);
        $this->assertSame('EUR', Currency::EUR->value);
        $this->assertSame('BTC', Currency::BTC->value);
    }

    #[Test]
    public function display_name_resolves_human_readable_label(): void
    {
        $this->assertSame('Bitcoin', Currency::BTC->displayName());
        $this->assertSame('Lithuanian Litas', Currency::LTL->displayName());
        $this->assertSame('United States Dollar', Currency::USD->displayName());
    }

    #[Test]
    public function try_from_code_returns_matching_case(): void
    {
        $this->assertSame(Currency::USD, Currency::tryFromCode('USD'));
        $this->assertNull(Currency::tryFromCode('XYZ'));
    }

    #[Test]
    public function code_helper_coerces_strings_and_enum_values(): void
    {
        $this->assertSame('USD', Currency::code('USD'));
        $this->assertSame('USD', Currency::code(Currency::USD));
    }
}
