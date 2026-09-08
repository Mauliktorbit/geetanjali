<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
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
                ['icon' => 'bi-shield-check', 'title' => '100% Hallmarked', 'subtitle' => 'Certified Jewellery'],
                ['icon' => 'bi-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
                ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders'],
                ['icon' => 'bi-box-seam', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
                ['icon' => 'bi-gem', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
                ['icon' => 'bi-gift', 'title' => 'Gift Wrapping', 'subtitle' => 'Available on Request'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Enquiry::create($data);

        return back()->with('success', 'Thank you for contacting us. We will get back to you shortly.');
    }
}
