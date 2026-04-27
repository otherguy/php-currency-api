<?php

declare(strict_types=1);

namespace Otherguy\Currency\Helpers;

use DateTimeImmutable;
use DateTimeInterface;

final class DateHelper
{
    public static function format(?DateTimeInterface $date, string $format = 'Y-m-d'): ?string
    {
        return $date?->format($format);
    }

    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    public static function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today');
    }
}
