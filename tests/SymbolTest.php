<?php

use PHPUnit\Framework\TestCase;
use Otherguy\Currency\Symbol;

/**
 * SymbolTest
 */
class SymbolTest extends TestCase
{
  /** @test */
  public function can_get_all_symbols()
  {
    $this->assertCount(167, Symbol::all());
  }

  /** @test */
  public function can_get_a_symbol_name()
  {
    $this->assertEquals('Lithuanian Litas', Symbol::name(Symbol::LTL));
    $this->assertEquals('Bitcoin', Symbol::name(Symbol::BTC));
  }

  /** @test */
  public function can_get_a_list_of_all_symbols()
  {
    $this->assertCount(167, Symbol::names());
    $this->assertEquals('Lithuanian Litas', Symbol::names()[Symbol::LTL]);
    $this->assertEquals('Bitcoin', Symbol::names()[Symbol::BTC]);
  }
}
