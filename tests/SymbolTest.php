<?php

use PHPUnit\Framework\TestCase;
use Otherguy\Currency\Symbol;
/**
 * Class SymbolTest
 */
class SymbolTest extends TestCase
{
	/** @test */
	public function can_get_all_symbols() {
		$this->assertCount( 167, Symbol::all() );
	}
}
