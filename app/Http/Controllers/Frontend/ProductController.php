<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockNotification;
use App\Services\CartService;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly StorefrontCatalogService $catalog) {}

    public function show(string $slug): View
    {
        $model = $this->catalog->findActiveBySlug($slug);
        abort_if($model === null, 404);

        $model->increment('view_count');

        $product = $this->catalog->toDetail($model);
        $relatedProducts = $this->catalog->relatedCards($model);

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Shop', 'url' => route('products.new-arrivals')],
                ['label' => $product->category, 'url' => route('collections.kundan')],
                ['label' => $product->name, 'url' => null],
            ],
        ]);
    }

    public function checkDelivery(Request $request): JsonResponse
    {
        $request->merge([
            'pincode' => digits_only($request->input('pincode')),
        ]);

        $validated = $request->validate([
            'pincode' => indian_pincode_rules(true),
            'product_id' => ['nullable', 'integer'],
        ], [
            'pincode.regex' => 'Enter a valid 6-digit pincode.',
        ]);

        $pincode = $validated['pincode'];
        $available = strlen((string) $pincode) === 6;

        $eta = '3–5 business days';
        if (! empty($validated['product_id'])) {
            $product = Product::query()->storefront()->find($validated['product_id']);
            if ($product?->estimated_delivery) {
                $eta = $product->estimated_delivery;
            }
        }

        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Delivery available to '.$pincode
                : 'Delivery not available for this pincode',
            'estimated_delivery' => $available ? $eta : null,
        ]);
    }

    public function addToCart(Request $request, CartService $cart): JsonResponse
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

        $added = $cart->add((int) $validated['product_id'], (int) $validated['quantity'], $validated);

        if (! $added) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $cart->count(),
            'product_id' => (int) $validated['product_id'],
            'quantity' => (int) $validated['quantity'],
        ]);
    }

    public function notifyStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'email' => ['nullable', 'email', 'max:180'],
        ]);

        $product = Product::query()->storefront()->with('inventories')->find($validated['product_id']);
        if ($product === null) {
            return response()->json([
                'success' => false,
                'message' => 'This product is not available.',
            ], 404);
        }

        $inStock = (int) $product->inventories->sum('available_stock') > 0;
        if ($inStock) {
            return response()->json([
                'success' => false,
                'message' => 'This piece is back in stock. You can add it to your bag.',
            ], 422);
        }

        $user = $request->user();
        $email = strtolower(trim((string) ($validated['email'] ?? $user?->email ?? '')));
        if ($email === '') {
            return response()->json([
                'success' => false,
                'needs_email' => true,
                'message' => 'Enter your email to be notified.',
            ], 422);
        }

        StockNotification::query()->updateOrCreate(
            [
                'product_id' => (int) $product->id,
                'email' => $email,
            ],
            [
                'customer_id' => $user?->customer?->id,
                'notified_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'We’ll email you when this piece is back in stock.',
        ]);
    }
}
