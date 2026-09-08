<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        $summary = $this->cart->summary();

        return view('frontend.cart.index', [
            'cart' => $summary,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Your Cart', 'url' => null],
            ],
            'benefits' => [
                ['icon' => 'bi-patch-check', 'title' => '100% Hallmarked Jewellery', 'subtitle' => 'Certified for purity and quality'],
                ['icon' => 'bi-shield-lock', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure Transactions'],
                ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders'],
                ['icon' => 'bi-arrow-repeat', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
                ['icon' => 'bi-gem', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
            ],
        ]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'name' => ['nullable', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'discount_label' => ['nullable', 'string', 'max:40'],
            'metal' => ['nullable', 'string', 'max:80'],
            'weight' => ['nullable', 'string', 'max:80'],
            'url' => ['nullable', 'string', 'max:255'],
        ]);

        $added = $this->cart->add((int) $validated['product_id'], (int) $validated['quantity'], $validated);

        if (! $added) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not available.',
                ], 404);
            }

            return back()->with('error', 'This product is not available.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => $this->cart->count(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $this->cart->update((int) $validated['product_id'], (int) $validated['quantity']);
        $summary = $this->cart->summary();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $summary,
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $this->cart->remove($id);
        $summary = $this->cart->summary();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart' => $summary,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function applyCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'coupon' => ['required', 'string', 'max:50'],
        ]);

        $result = $this->cart->applyCoupon($validated['coupon']);
        $summary = $this->cart->summary();

        if ($request->expectsJson()) {
            return response()->json(array_merge($result, ['cart' => $summary]));
        }

        return $result['success']
            ? back()->with('success', $result['message'])
            : back()->withErrors(['coupon' => $result['message']]);
    }

    public function saveGiftMessage(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'gift_message' => ['nullable', 'string', 'max:500'],
        ]);

        $this->cart->setGiftMessage($validated['gift_message'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Gift message saved.']);
        }

        return back()->with('success', 'Gift message saved.');
    }
}
