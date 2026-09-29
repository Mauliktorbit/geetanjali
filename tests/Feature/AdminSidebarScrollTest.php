<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminSidebarScrollTest extends TestCase
{
    public function test_admin_sidebar_keeps_scroll_and_active_item_in_view(): void
    {
        $js = (string) file_get_contents(public_path('js/admin.js'));
        $css = (string) file_get_contents(public_path('css/admin.css'));

        $this->assertStringContainsString('initSidebarScroll()', $js);
        $this->assertStringContainsString('admin.sidebar.navScroll', $js);
        $this->assertStringContainsString("block: 'nearest'", $js);
        $this->assertStringContainsString('.sidebar-nav', $css);
        $this->assertMatchesRegularExpression('/\.sidebar-nav\s*\{[^}]*overflow-y:\s*auto/s', $css);
    }
}
