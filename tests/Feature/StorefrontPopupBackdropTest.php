<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontPopupBackdropTest extends TestCase
{
    public function test_popup_overlays_use_frosted_blur_instead_of_green(): void
    {
        $appCss = (string) file_get_contents(public_path('assets/css/app.css'));
        $this->assertStringContainsString('.bridal-filter-drawer__backdrop', $appCss);
        $this->assertStringContainsString('.checkout-modal', $appCss);
        $this->assertStringContainsString('body .swal2-container.swal2-backdrop-show', $appCss);
        $this->assertStringContainsString('body .offcanvas-backdrop', $appCss);
        $this->assertStringContainsString('backdrop-filter: blur(var(--popup-backdrop-blur))', $appCss);

        $greenPopup = '/(?:filter-drawer__backdrop|checkout-modal|dr-modal__backdrop|quick-view__backdrop|product-lightbox)\s*\{[^}]*rgba\(\s*(?:2,\s*47,\s*39|6,\s*78,\s*59)/s';

        $files = [
            'assets/css/home.css',
            'assets/css/collection.css',
            'assets/css/bridal.css',
            'assets/css/new-arrivals.css',
            'assets/css/checkout.css',
            'assets/css/account.css',
            'assets/css/product.css',
        ];

        foreach ($files as $relative) {
            $css = (string) file_get_contents(public_path($relative));
            $this->assertStringContainsString(
                'var(--popup-backdrop)',
                $css,
                $relative.' should use the shared frosted popup overlay.'
            );
            $this->assertDoesNotMatchRegularExpression(
                $greenPopup,
                $css,
                $relative.' still uses a green popup overlay.'
            );
        }

        $theme = (string) file_get_contents(public_path('assets/css/theme.css'));
        $this->assertStringContainsString('--popup-backdrop: rgba(20, 20, 20, 0.22)', $theme);

        $swal = (string) file_get_contents(public_path('js/sweet-alerts.js'));
        $this->assertStringContainsString('rgba(20, 20, 20, 0.22)', $swal);
        $this->assertStringContainsString('backdrop-filter: blur(16px)', $swal);
    }
}
