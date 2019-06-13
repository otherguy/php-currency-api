<?php

use Otherguy\Currency\Helpers\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{

  /** @test */
  public function can_parse_a_date()
  {
    $this->assertEquals('23:15:03', DateHelper::parse('23h 15m 03s', 'H\h i\m s\s',)->format('H:i:s'));
  }

  /** @test */
  public function can_format_a_date()
  {
    $this->assertEqualsWithDelta((new DateTime())->format('Y-m-d'), DateHelper::format('now', 'Y-m-d'), 0.1);
    $this->assertEquals('2019-01-01', DateHelper::format(1546300800, 'Y-m-d'));
    $this->assertEquals('2019-01-01', DateHelper::format('2019-01-01', 'Y-m-d'));
    $this->assertEquals(DateHelper::today()->format('Y-m-d'), DateHelper::format(DateHelper::today(), 'Y-m-d'));
  }

  /** @test */
  public function can_create_a_date()
  {
    $this->assertEquals(1546300800, DateHelper::create('1.1.2019')->getTimestamp());
  }

  /** @test */
  public function can_get_current_date_and_time()
  {
    $this->assertEqualsWithDelta(new DateTime(), DateHelper::now(), 0.1);
  }

  /** @test */
  public function can_get_current_date()
  {
    $this->assertEquals(new DateTime('today'), DateHelper::today());
  }
}
