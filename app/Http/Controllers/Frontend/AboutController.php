<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Display the About Us page.
     * Content arrays are structured for easy replacement with CMS/database later.
     */
    public function index(): View
    {
        return view('frontend.about', [
            'hero' => $this->hero(),
            'story' => $this->story(),
            'values' => $this->values(),
            'stats' => $this->stats(),
            'promise' => $this->promise(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function hero(): array
    {
        return [
            'heading_line_1' => 'A Legacy of Trust,',
            'heading_line_2' => 'Crafted with Care',
            'description' => 'For over three decades, Geetanjali Jewellers has been a name synonymous with trust, elegance and unmatched craftsmanship.',
            'image' => 'public/assets/images/about/about-hero.jpg',
            'image_alt' => 'Geetanjali Jewellers premium Kundan jewellery on silk',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function story(): array
    {
        return [
            'heading_line_1' => 'Where Tradition',
            'heading_line_2' => 'Meets Timeless Beauty',
            'paragraphs' => [
                'Founded with a passion for perfection and a commitment to excellence, Geetanjali Jewellers began its journey with a simple belief — every piece of jewellery tells a story.',
                'From traditional craftsmanship to modern designs, we create jewellery that celebrates every milestone and emotion in your life.',
            ],
            'image' => 'public/assets/images/about/showroom.jpg',
            'image_alt' => 'Premium jewellery showroom ambience at Geetanjali Jewellers',
            'cta_label' => 'Know More About Us',
            'cta_url' => '#promise',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function values(): array
    {
        return [
            [
                'icon' => 'bi-gem',
                'title' => 'Trust & Transparency',
                'description' => 'Honest pricing, clear product details and transparent policies.',
            ],
            [
                'icon' => 'bi-award',
                'title' => 'Quality Craftsmanship',
                'description' => 'Every piece is crafted with precision and attention to detail.',
            ],
            [
                'icon' => 'bi-people',
                'title' => 'Customer First',
                'description' => 'Your satisfaction and trust are at the heart of everything we do.',
            ],
            [
                'icon' => 'bi-pen',
                'title' => 'Timeless Designs',
                'description' => 'Blending tradition with contemporary designs for every generation.',
            ],
            [
                'icon' => 'bi-heart',
                'title' => 'Relationships That Last',
                'description' => 'Building lifelong relationships through trust, care and service.',
            ],
        ];
    }

    /**
     * Real showroom and workshop facts — not placeholder metrics.
     *
     * @return list<array<string, string>>
     */
    private function stats(): array
    {
        return [
            ['icon' => 'bi-geo-alt', 'value' => 'Ahmedabad', 'label' => 'Showroom at PNTC Tower, Vejalpur'],
            ['icon' => 'bi-hammer', 'value' => 'Workshop', 'label' => 'Kundan jewellery crafted in-house'],
            ['icon' => 'bi-heart', 'value' => 'Skin-friendly', 'label' => 'Anti-tarnish finish, made for everyday wear'],
            ['icon' => 'bi-clock', 'value' => 'Mon–Sat', 'label' => '10:00 AM – 7:00 PM'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function promise(): array
    {
        return [
            'heading_line_1' => 'Crafted with Passion,',
            'heading_line_2' => 'Delivered with Pride',
            'description' => 'Each creation goes through a meticulous process to ensure it meets our high standards of finish, comfort and design.',
            'features' => [
                [
                    'icon' => 'bi-heart',
                    'title' => 'Skin-friendly Jewellery',
                    'description' => 'Lightweight artificial jewellery with a premium anti-tarnish finish, made for everyday wear.',
                ],
                [
                    'icon' => 'bi-truck',
                    'title' => 'Secure & Insured Delivery',
                    'description' => 'Your precious jewellery is delivered safely, securely and on time.',
                ],
                [
                    'icon' => 'bi-headset',
                    'title' => 'After-Sales Support',
                    'description' => 'We are always here to assist you, even after your purchase.',
                ],
            ],
            'image' => 'public/assets/images/about/craftsmanship.jpg',
            'image_alt' => 'Artisan crafting fine Geetanjali jewellery by hand',
        ];
    }
}
