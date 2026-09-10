<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * @var array<string, array{title: string, heading: string, intro: string, sections: list<array{heading: string, body: string}>}>
     */
    private array $pages = [];

    public function __construct()
    {
        $brand = config('brand.name');
        $phone = config('brand.contact.phone');
        $email = config('brand.contact.email');
        $address = config('brand.contact.address');

        $this->pages = [
            'faq' => [
                'title' => 'FAQs',
                'heading' => "FAQ's",
                'intro' => "Find quick answers about shopping, shipping, returns and jewellery care at {$brand}.",
                'sections' => [
                    [
                        'heading' => 'Do you ship across India?',
                        'body' => 'Yes. We offer free shipping on all orders across India with secure packaging and insured delivery.',
                    ],
                    [
                        'heading' => 'Are your jewellery pieces hallmarked?',
                        'body' => 'Yes. All gold jewellery is BIS hallmarked and certified for purity and quality.',
                    ],
                    [
                        'heading' => 'How can I track my order?',
                        'body' => 'Use the Track Order page with your order ID and registered mobile/email to view live status.',
                    ],
                    [
                        'heading' => 'What is your return policy?',
                        'body' => 'We offer an easy 15-day return policy on eligible products. Please see Returns & Refunds for full details.',
                    ],
                ],
            ],
            'shipping-policy' => [
                'title' => 'Shipping Policy',
                'heading' => 'Shipping Policy',
                'intro' => 'Transparent shipping information for every Geetanjali Jewellers order.',
                'sections' => [
                    [
                        'heading' => 'Delivery timeline',
                        'body' => 'Orders are typically delivered within 5–7 business days depending on your location. Prepaid orders may ship faster.',
                    ],
                    [
                        'heading' => 'Shipping charges',
                        'body' => 'Shipping is free on all orders. Your jewellery is packed securely and dispatched with tracking.',
                    ],
                    [
                        'heading' => 'Need help?',
                        'body' => "Call us on {$phone} or email {$email} for shipping support.",
                    ],
                ],
            ],
            'returns' => [
                'title' => 'Returns & Refunds',
                'heading' => 'Returns & Refunds',
                'intro' => 'We want you to love every piece. Here is how returns and refunds work.',
                'sections' => [
                    [
                        'heading' => '15-day easy returns',
                        'body' => 'Eligible products can be returned within 15 days of delivery in original condition with tags and packaging.',
                    ],
                    [
                        'heading' => 'Refund process',
                        'body' => 'Once the return is inspected, refunds are processed to the original payment method within 5–7 business days.',
                    ],
                    [
                        'heading' => 'Non-returnable items',
                        'body' => 'Customised, engraved or made-to-order jewellery may not be eligible for return unless damaged in transit.',
                    ],
                ],
            ],
            'terms' => [
                'title' => 'Terms & Conditions',
                'heading' => 'Terms & Conditions',
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
                'title' => 'Privacy Policy',
                'heading' => 'Privacy Policy',
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
            'help' => [
                'title' => 'Help',
                'heading' => 'Help Centre',
                'intro' => 'Need assistance? Start here or contact our team directly.',
                'sections' => [
                    [
                        'heading' => 'Popular help topics',
                        'body' => 'Visit FAQs for common questions, Track Order for shipment status, or Contact Us for personalised support.',
                    ],
                    [
                        'heading' => 'Talk to us',
                        'body' => "Phone: {$phone}\nEmail: {$email}\nAddress: {$address}",
                    ],
                ],
            ],
            'track-order' => [
                'title' => 'Track Order',
                'heading' => 'Track Order',
                'intro' => 'Enter your order details to check the latest delivery status.',
                'sections' => [
                    [
                        'heading' => 'How tracking works',
                        'body' => 'After your order is confirmed, a tracking update is shared by SMS/email. You can also contact us with your order ID for live help.',
                    ],
                    [
                        'heading' => 'Support',
                        'body' => "If tracking is unavailable, reach us at {$phone} or {$email} and we will assist you immediately.",
                    ],
                ],
            ],
            'store-locator' => [
                'title' => 'Store Locator',
                'heading' => 'Store Locator',
                'intro' => 'Visit our showroom and experience Geetanjali Jewellers in person.',
                'sections' => [
                    [
                        'heading' => 'Our store',
                        'body' => "{$address}\n\nHours: ".config('brand.contact.hours')."\n".(config('brand.contact.hours_sunday') ?? 'Sunday: Closed'),
                    ],
                    [
                        'heading' => 'Get directions',
                        'body' => 'Open the Contact Us page for map directions, or call us before your visit for the best appointment experience.',
                    ],
                ],
            ],
            'compare' => [
                'title' => 'Compare',
                'heading' => 'Compare Jewellery',
                'intro' => 'Compare your favourite pieces side by side. Save items to wishlist while you decide.',
                'sections' => [
                    [
                        'heading' => 'Getting started',
                        'body' => 'Browse Kundan Collection, Bridal Collection or New Arrivals and add pieces to your wishlist to shortlist and compare.',
                    ],
                ],
            ],
        ];
    }

    public function show(string $slug): View
    {
        if (! isset($this->pages[$slug])) {
            throw new NotFoundHttpException();
        }

        $page = $this->pages[$slug];

        return view('frontend.pages.show', [
            'page' => $page,
            'slug' => $slug,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => $page['heading'], 'url' => null],
            ],
            'contact' => config('brand.contact'),
        ]);
    }

    public function trackOrder(Request $request): View|RedirectResponse
    {
        $page = $this->pages['track-order'];

        $orderId = trim((string) $request->query('order_id', ''));
        $statusMessage = null;

        $lookup = function (string $id) use ($request): ?string {
            $order = \App\Models\Order::query()->where('order_number', $id)->first();
            if (! $order) {
                return null;
            }

            if ($request->filled('phone')) {
                $digits = preg_replace('/\D+/', '', (string) $request->input('phone'));
                $orderDigits = preg_replace('/\D+/', '', (string) $order->customer_phone);
                if ($digits && $orderDigits && ! str_contains($orderDigits, $digits) && ! str_contains($digits, $orderDigits)) {
                    return null;
                }
            }

            return 'Order '.$order->order_number.' is currently '.str_replace('_', ' ', $order->status).'. Payment: '.$order->payment_status.'.';
        };

        if ($request->isMethod('post')) {
            $request->merge([
                'phone' => indian_mobile($request->input('phone')),
            ]);

            $validated = $request->validate([
                'order_id' => ['required', 'string', 'max:50'],
                'phone' => indian_mobile_rules(false),
            ], [
                'phone.regex' => 'Enter a valid 10-digit mobile number.',
            ]);

            $orderId = $validated['order_id'];
            $statusMessage = $lookup($orderId)
                ?? 'We could not find a live tracking record for this order ID yet. Please contact support with your order details and we will help you immediately.';
        } elseif ($orderId !== '') {
            $statusMessage = $lookup($orderId);
        }

        return view('frontend.pages.track-order', [
            'page' => $page,
            'orderId' => $orderId,
            'statusMessage' => $statusMessage,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Track Order', 'url' => null],
            ],
            'contact' => config('brand.contact'),
        ]);
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:150'],
        ]);

        return back()->with('success', 'Thank you for subscribing. Enjoy 10% off on your first order!');
    }
}
