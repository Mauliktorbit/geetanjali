<?php

namespace Tests\Unit;

use Tests\TestCase;

class ReviewCountLabelTest extends TestCase
{
    public function test_singular_and_plural_review_labels(): void
    {
        $this->assertSame('0 Reviews', review_count_label(0));
        $this->assertSame('1 Review', review_count_label(1));
        $this->assertSame('2 Reviews', review_count_label(2));
        $this->assertSame('(1 Review)', review_count_label(1, true));
        $this->assertSame('(12 Reviews)', review_count_label(12, true));
    }
}
