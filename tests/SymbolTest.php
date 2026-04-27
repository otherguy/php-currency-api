<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests;

use Otherguy\Currency\Currency;
use Otherguy\Currency\Symbol;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SymbolTest extends TestCase
{
    #[Test]
    public function can_get_all_symbols(): void
    {
        $this->assertCount(167, @Symbol::all());
    }

    #[Test]
    public function can_get_a_symbol_name(): void
    {
        $this->assertSame('Lithuanian Litas', @Symbol::name(Symbol::LTL));
        $this->assertSame('Bitcoin', @Symbol::name(Symbol::BTC));
    }

    #[Test]
    public function can_get_a_list_of_all_symbols(): void
    {
        $names = @Symbol::names();

        $this->assertCount(167, $names);
        $this->assertSame('Lithuanian Litas', $names[Symbol::LTL]);
        $this->assertSame('Bitcoin', $names[Symbol::BTC]);
    }

    #[Test]
    public function symbol_constants_resolve_to_currency_codes(): void
    {
        $this->assertSame('USD', Symbol::USD);
        $this->assertSame('EUR', Symbol::EUR);
    }

    #[Test]
    public function deprecated_methods_trigger_user_deprecated_notice(): void
    {
        Symbol::resetDeprecationNotice();
        $messages = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$messages): bool {
            if ($errno === E_USER_DEPRECATED) {
                $messages[] = $errstr;

                return true;
            }

            return false;
        });

        try {
            Symbol::all();
        } finally {
            restore_error_handler();
        }

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString(Currency::class, $messages[0]);
    }
}
