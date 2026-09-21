<?php

namespace Tests\Unit;

use Tests\TestCase;

class PriceDiscountLabelTest extends TestCase
{
    public function test_exact_percent_matches_prices(): void
    {
        $this->assertSame('10% OFF', price_discount_label(90000, 100000));
        $this->assertSame('7% OFF', price_discount_label(93000, 100000));
    }

    public function test_non_matching_percent_shows_save_amount(): void
    {
        $this->assertSame('Save ₹23,900', price_discount_label(298500, 322400));
        $this->assertSame('Save ₹33,200', price_discount_label(415000, 448200));
    }

    public function test_no_discount_when_prices_equal(): void
    {
        $this->assertNull(price_discount_label(1000, 1000));
        $this->assertNull(price_discount_label(0, 1000));
    }
}
