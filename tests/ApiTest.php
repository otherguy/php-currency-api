<?php

use Otherguy\Currency\API;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 */
class ApiTest extends TestCase
{
    /** @test */
    public function can_get_latest_rates()
    {
        $this->assertEquals([], API::make('mock')->get());
    }
}
