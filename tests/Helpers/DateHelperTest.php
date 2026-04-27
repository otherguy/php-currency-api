<?php

declare(strict_types=1);

namespace Otherguy\Currency\Tests\Helpers;

use DateTimeImmutable;
use Otherguy\Currency\Helpers\DateHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    #[Test]
    public function format_returns_null_for_null_input(): void
    {
        $this->assertNull(DateHelper::format(null));
    }

    #[Test]
    public function format_uses_iso_date_by_default(): void
    {
        $date = new DateTimeImmutable('2019-01-01 12:34:56');
        $this->assertSame('2019-01-01', DateHelper::format($date));
    }

    #[Test]
    public function format_accepts_custom_format_string(): void
    {
        $date = new DateTimeImmutable('2019-01-01 12:34:56');
        $this->assertSame('01.01.2019 12:34', DateHelper::format($date, 'd.m.Y H:i'));
    }

    #[Test]
    public function now_returns_current_timestamp(): void
    {
        $delta = DateHelper::now()->getTimestamp() - time();
        $this->assertLessThanOrEqual(1, abs($delta));
    }

    #[Test]
    public function today_returns_midnight_today(): void
    {
        $today = DateHelper::today();
        $this->assertSame('00:00:00', $today->format('H:i:s'));
        $this->assertSame((new DateTimeImmutable('today'))->format('Y-m-d'), $today->format('Y-m-d'));
    }
}
