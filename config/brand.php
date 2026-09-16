<?php

return [

    'name' => env('BRAND_NAME', 'Geetanjali Jewellers'),

    'short_name' => env('BRAND_SHORT_NAME', 'GEETANJALI'),

    'tagline' => env('BRAND_TAGLINE', 'Mfg & Wholesale in Kundan Jewellery'),

    'business_type' => env('BRAND_BUSINESS_TYPE', 'Kundan Jewellery Manufacturing & Wholesale'),

    'logo' => 'public/assets/images/logo/geetanjali-logo-header.png',

    'promo' => [
        'message' => 'HANDCRAFTED KUNDAN, GOLD & DIAMOND JEWELLERY',
        'code' => '',
    ],

    'contact' => [
        'address' => 'C - 1209/1210, PNTC Tower, Times of India Press Road, Vejalpur, Ahmedabad - 380015',
        'phone' => '+91 9583959503',
        'email' => 'maulik@torbitmultisoft.com',
        'hours' => 'Mon - Sat: 10:00 AM - 7:00 PM',
        'hours_sunday' => 'Sunday: Closed',
        'map_embed' => 'https://maps.google.com/maps?q=PNTC%20Tower%2C%20Times%20of%20India%20Press%20Road%2C%20Vejalpur%2C%20Ahmedabad%20380015&t=&z=15&ie=UTF8&iwloc=&output=embed',
        'map_directions' => 'https://www.google.com/maps/dir/?api=1&destination=PNTC+Tower,+Times+of+India+Press+Road,+Vejalpur,+Ahmedabad+380015',
    ],

    'social' => [
        'facebook' => env('BRAND_FACEBOOK', 'https://www.facebook.com/'),
        'instagram' => env('BRAND_INSTAGRAM', 'https://www.instagram.com/'),
        'youtube' => env('BRAND_YOUTUBE', 'https://www.youtube.com/'),
    ],

    'colors' => [
        'primary_green' => '#064E3B',
        'dark_green' => '#03382B',
        'deep_green' => '#022F27',
        'gold' => '#C9A24D',
        'light_gold' => '#DDBB6A',
        'ivory' => '#FAF8F2',
        'white' => '#FFFFFF',
        'text_dark' => '#222222',
        'text_muted' => '#6B6B6B',
        'border' => '#E8E2D6',
    ],

];
