<?php

namespace App\Services;

use App\Models\Page;

class PolicyPageService
{
    public const KEYS = ['shipping', 'terms', 'privacy'];

    /**
     * @return array<string, array{
     *     key: string,
     *     slug: string,
     *     slugs: list<string>,
     *     type: string,
     *     title: string,
     *     frontend_slug: string,
     *     preview_route: string,
     *     intro: string,
     *     sections: list<array{heading: string, body: string}>
     * }>
     */
    public function catalog(): array
    {
        $brand = config('brand.name');
        $phone = config('brand.contact.phone');
        $email = config('brand.contact.email');

        return [
            'shipping' => [
                'key' => 'shipping',
                'slug' => 'shipping-policy',
                'slugs' => ['shipping-policy'],
                'type' => 'shipping',
                'title' => 'Shipping Policy',
                'frontend_slug' => 'shipping-policy',
                'preview_route' => 'pages.shipping',
                'intro' => 'Transparent shipping information for every Geetanjali Jewellers order.',
                'sections' => [
                    [
                        'heading' => 'Delivery timeline',
                        'body' => 'Orders are typically delivered within 5–7 business days depending on your location. Prepaid orders may ship faster.',
                    ],
                    [
                        'heading' => 'Shipping charges',
                        'body' => 'Shipping charges are calculated at checkout based on your delivery option. Your jewellery is packed securely and dispatched with tracking.',
                    ],
                    [
                        'heading' => 'Need help?',
                        'body' => "Call us on {$phone} or email {$email} for shipping support.",
                    ],
                ],
            ],
            'terms' => [
                'key' => 'terms',
                'slug' => 'terms',
                'slugs' => ['terms', 'terms-and-conditions'],
                'type' => 'terms',
                'title' => 'Terms & Conditions',
                'frontend_slug' => 'terms',
                'preview_route' => 'pages.terms',
                'intro' => "Please read these terms carefully before using {$brand} website and services.",
                'sections' => [
                    [
                        'heading' => 'Use of website',
                        'body' => 'By browsing or placing an order, you agree to use this website for lawful purposes and provide accurate information.',
                    ],
                    [
                        'heading' => 'Product information',
                        'body' => 'We aim for accurate product details, weights and pricing. Minor variations can occur due to handmade craftsmanship.',
                    ],
                    [
                        'heading' => 'Pricing & offers',
                        'body' => 'Prices and offers are subject to change without notice. Coupon codes apply only when valid and as stated.',
                    ],
                ],
            ],
            'privacy' => [
                'key' => 'privacy',
                'slug' => 'privacy-policy',
                'slugs' => ['privacy-policy', 'privacy'],
                'type' => 'privacy',
                'title' => 'Privacy Policy',
                'frontend_slug' => 'privacy',
                'preview_route' => 'pages.privacy',
                'intro' => 'We respect your privacy and protect your personal information.',
                'sections' => [
                    [
                        'heading' => 'Information we collect',
                        'body' => 'We may collect name, contact details, delivery address and order information to fulfil your purchases and support requests.',
                    ],
                    [
                        'heading' => 'How we use information',
                        'body' => 'Your data is used for order processing, customer support, and optional marketing updates if you subscribe.',
                    ],
                    [
                        'heading' => 'Data security',
                        'body' => 'We use secure practices to protect your information. Payment transactions are processed through trusted gateways.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $key): array
    {
        $catalog = $this->catalog();
        if (! isset($catalog[$key])) {
            abort(404);
        }

        return $catalog[$key];
    }

    public function keyFromFrontendSlug(string $slug): ?string
    {
        foreach ($this->catalog() as $key => $definition) {
            if ($definition['frontend_slug'] === $slug || in_array($slug, $definition['slugs'], true)) {
                return $key;
            }
        }

        return null;
    }

    public function findOrCreate(string $key): Page
    {
        $definition = $this->definition($key);

        $page = Page::query()->whereIn('slug', $definition['slugs'])->orderByRaw(
            'CASE slug WHEN ? THEN 0 ELSE 1 END',
            [$definition['slug']]
        )->first();

        if ($page) {
            return $page;
        }

        return Page::query()->create([
            'title' => $definition['title'],
            'slug' => $definition['slug'],
            'type' => $definition['type'],
            'content' => $this->encodeContent($definition['intro'], $definition['sections']),
            'seo_title' => $definition['title'],
            'seo_description' => $definition['intro'],
            'is_active' => true,
        ]);
    }

    /**
     * @return array{title: string, intro: string, sections: list<array{heading: string, body: string}>, seo_title: ?string, seo_description: ?string, is_active: bool}
     */
    public function formPayload(Page $page, array $definition): array
    {
        $parsed = $this->parseContent((string) $page->content, $definition);

        return [
            'title' => $page->title ?: $definition['title'],
            'intro' => $parsed['intro'],
            'sections' => $parsed['sections'],
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
            'is_active' => (bool) $page->is_active,
        ];
    }

    /**
     * @param  array{title: string, intro: ?string, sections?: list<array{heading?: string, body?: string}>, seo_title?: ?string, seo_description?: ?string, is_active?: bool}  $data
     */
    public function updatePolicy(string $key, array $data): Page
    {
        $definition = $this->definition($key);
        $page = $this->findOrCreate($key);
        $sections = $this->cleanSections($data['sections'] ?? []);

        if ($sections === []) {
            $sections = $definition['sections'];
        }

        $page->update([
            'title' => $data['title'],
            'slug' => $definition['slug'],
            'type' => $definition['type'],
            'content' => $this->encodeContent((string) ($data['intro'] ?? ''), $sections),
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_description' => $data['seo_description'] ?: ($data['intro'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $page->refresh();
    }

    /**
     * @return array{title: string, heading: string, intro: string, sections: list<array{heading: string, body: string}>}|null
     */
    public function storefrontPage(string $slug): ?array
    {
        $key = $this->keyFromFrontendSlug($slug);
        if ($key === null) {
            return null;
        }

        $definition = $this->definition($key);
        $page = Page::query()->whereIn('slug', $definition['slugs'])->orderByRaw(
            'CASE slug WHEN ? THEN 0 ELSE 1 END',
            [$definition['slug']]
        )->first();

        if (! $page || ! $page->is_active) {
            return [
                'title' => $definition['title'],
                'heading' => $definition['title'],
                'intro' => $definition['intro'],
                'sections' => $definition['sections'],
            ];
        }

        $parsed = $this->parseContent((string) $page->content, $definition);

        return [
            'title' => $page->seo_title ?: $page->title ?: $definition['title'],
            'heading' => $page->title ?: $definition['title'],
            'intro' => $parsed['intro'],
            'sections' => $parsed['sections'],
        ];
    }

    /**
     * @param  list<array{heading?: string, body?: string}>  $sections
     * @return list<array{heading: string, body: string}>
     */
    public function cleanSections(array $sections): array
    {
        $clean = [];
        foreach ($sections as $section) {
            $heading = trim((string) ($section['heading'] ?? ''));
            $body = trim((string) ($section['body'] ?? ''));
            if ($heading === '' && $body === '') {
                continue;
            }
            $clean[] = [
                'heading' => $heading !== '' ? $heading : 'Details',
                'body' => $body,
            ];
        }

        return $clean;
    }

    /**
     * @param  list<array{heading: string, body: string}>  $sections
     */
    private function encodeContent(string $intro, array $sections): string
    {
        return json_encode([
            'intro' => $intro,
            'sections' => $sections,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array{intro: string, sections: list<array{heading: string, body: string}>}
     */
    private function parseContent(string $raw, array $definition): array
    {
        $raw = trim($raw);
        $placeholders = [
            'Privacy policy content.',
            'Terms content.',
            'Shipping policy content.',
        ];

        if ($raw === '' || in_array($raw, $placeholders, true)) {
            return [
                'intro' => $definition['intro'],
                'sections' => $definition['sections'],
            ];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded) && (isset($decoded['sections']) || array_key_exists('intro', $decoded))) {
            $sections = $this->cleanSections(is_array($decoded['sections'] ?? null) ? $decoded['sections'] : []);

            return [
                'intro' => trim((string) ($decoded['intro'] ?? '')) ?: $definition['intro'],
                'sections' => $sections !== [] ? $sections : $definition['sections'],
            ];
        }

        return [
            'intro' => $definition['intro'],
            'sections' => [
                [
                    'heading' => 'Overview',
                    'body' => $raw,
                ],
            ],
        ];
    }
}
