<?php

namespace Tests\Unit;

use Tests\TestCase;

class ParseDmyTest extends TestCase
{
    public function test_parse_dmy_accepts_calendar_and_typed_dates(): void
    {
        $fromPicker = parse_dmy('2026-12-26');
        $fromTyped = parse_dmy('26/12/2026');

        $this->assertNotNull($fromPicker);
        $this->assertNotNull($fromTyped);
        $this->assertTrue($fromPicker->isSameDay($fromTyped));
        $this->assertSame('2026-12-26', $fromPicker->toDateString());
    }
}
