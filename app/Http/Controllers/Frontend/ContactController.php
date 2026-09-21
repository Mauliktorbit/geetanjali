<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\EnquiryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(private readonly EnquiryService $enquiries) {}

    public function index(): View
    {
        return view('frontend.contact.index', [
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Contact Us', 'url' => null],
            ],
            'contact' => config('brand.contact'),
            'heroImage' => 'public/assets/images/contact/hero-bg.jpg',
            'trustItems' => [
                ['icon' => 'bi-heart', 'title' => 'Skin-friendly', 'subtitle' => 'Anti-tarnish finish'],
                ['icon' => 'bi-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
                ['icon' => 'bi-box-seam', 'title' => 'Secure Packaging', 'subtitle' => 'Packed with care'],
                ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
                ['icon' => 'bi-stars', 'title' => 'Quality-checked', 'subtitle' => 'Premium finish'],
                ['icon' => 'bi-gift', 'title' => 'Gift Wrapping', 'subtitle' => 'Available on Request'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => indian_mobile($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => indian_mobile_rules(false),
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
        ]);

        $this->enquiries->submit($data);

        return back()->with('success', 'Thank you for contacting us. We will get back to you shortly.');
    }
}
