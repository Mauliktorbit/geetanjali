<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
        private readonly CartService $cart,
    ) {}

    public function index(): View
    {
        $summary = $this->wishlist->summary();

        return view('frontend.wishlist.index', [
            'wishlist' => $summary,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'My Wishlist', 'url' => null],
            ],
            'trustItems' => [
                ['icon' => 'bi-shield-check', 'title' => '100% Hallmarked', 'subtitle' => 'Certified Jewellery'],
                ['icon' => 'bi-bag-check', 'title' => 'Secure Payment', 'subtitle' => '100% Safe & Secure'],
                ['icon' => 'bi-truck', 'title' => 'Free Shipping', 'subtitle' => 'On All Orders'],
                ['icon' => 'bi-box-seam', 'title' => 'Easy Returns', 'subtitle' => '15 Day Return Policy'],
                ['icon' => 'bi-tools', 'title' => 'Lifetime Service', 'subtitle' => 'Maintenance & Repair'],
            ],
        ]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'product_id' => ['required', 'integer'],
        ], $this->productSnapshotRules()));

        $ok = $this->wishlist->add((int) $validated['product_id'], $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Added to wishlist.' : 'Product not found.',
                'wishlist_count' => $this->wishlist->count(),
            ], $ok ? 200 : 404);
        }

        return $ok
            ? back()->with('success', 'Added to wishlist.')
            : back()->with('error', 'Product not found.');
    }

    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'product_id' => ['required', 'integer'],
        ], $this->productSnapshotRules()));

        $result = $this->wishlist->toggle((int) $validated['product_id'], $validated);
        $inWishlist = (bool) ($result['in_wishlist'] ?? false);
        $message = $inWishlist ? 'Added to wishlist.' : 'Removed from wishlist.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'in_wishlist' => $inWishlist,
                'message' => $message,
                'wishlist_count' => $this->wishlist->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function remove(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $this->wishlist->remove($id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist.',
                'wishlist_count' => $this->wishlist->count(),
            ]);
        }

        return back()->with('success', 'Removed from wishlist.');
    }

    public function moveToBag(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $ok = $this->wishlist->moveToBag($id, $this->cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Moved to bag.' : 'Item not found in wishlist.',
                'wishlist_count' => $this->wishlist->count(),
                'cart_count' => $this->cart->count(),
            ], $ok ? 200 : 404);
        }

        return $ok
            ? redirect()->route('cart.index')->with('success', 'Moved to bag.')
            : back()->with('error', 'Item not found in wishlist.');
    }

    public function moveAllToBag(Request $request): JsonResponse|RedirectResponse
    {
        $moved = $this->wishlist->moveAllToBag($this->cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $moved > 0 ? 'All items moved to bag.' : 'Wishlist is empty.',
                'moved' => $moved,
                'wishlist_count' => $this->wishlist->count(),
                'cart_count' => $this->cart->count(),
            ]);
        }

        return $moved > 0
            ? redirect()->route('cart.index')->with('success', 'All items moved to bag.')
            : back()->with('error', 'Wishlist is empty.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function productSnapshotRules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'image' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'discount_label' => ['nullable', 'string', 'max:40'],
            'metal' => ['nullable', 'string', 'max:80'],
            'weight' => ['nullable', 'string', 'max:80'],
            'url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
