<?php

namespace Tests\Unit;

use Tests\TestCase;

class StorefrontImageTest extends TestCase
{
    public function test_uploaded_images_use_app_storage_url(): void
    {
        $url = storefront_image('uploads/products/sample.png');

        $this->assertStringContainsString('/storage/uploads/products/sample.png', $url);
        $this->assertStringNotContainsString('://localhost/storage/', $url);
    }

    public function test_public_asset_paths_stay_under_public(): void
    {
        $url = storefront_image('public/assets/images/products/gold-drop-earrings.jpg');

        $this->assertStringContainsString('/public/assets/images/products/gold-drop-earrings.jpg', $url);
    }
}
