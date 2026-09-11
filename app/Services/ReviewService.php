<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReviewService extends BaseService
{
    public function __construct(
        ReviewRepository $repository,
        protected NotificationService $notifications,
    ) {
        parent::__construct($repository);
    }

    public function approve(Review $review): Review
    {
        $updated = $this->update($review, ['status' => 'approved']);
        $this->refreshProductStats((int) $updated->product_id);

        return $updated;
    }

    public function reject(Review $review): Review
    {
        $productId = (int) $review->product_id;
        $updated = $this->update($review, ['status' => 'rejected']);
        $this->refreshProductStats($productId);

        return $updated;
    }

    /**
     * Latest delivered order that still needs a review from this customer.
     *
     * @param  list<int>  $skipOrderIds
     * @return array<string, mixed>|null
     */
    public function pendingPrompt(Customer $customer, array $skipOrderIds = []): ?array
    {
        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->where('status', OrderStatus::DELIVERED)
            ->with(['items.product'])
            ->latest('delivered_at')
            ->latest('id')
            ->limit(15)
            ->get();

        foreach ($orders as $order) {
            if (in_array((int) $order->id, array_map('intval', $skipOrderIds), true)) {
                continue;
            }

            $products = $this->unreviewedProducts($customer, $order);
            if ($products->isEmpty()) {
                continue;
            }

            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'products' => $products->values()->all(),
            ];
        }

        return null;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function unreviewedProducts(Customer $customer, Order $order): Collection
    {
        $order->loadMissing('items.product');

        $items = $order->items
            ->filter(fn ($item) => (int) $item->product_id > 0)
            ->unique('product_id')
            ->values();

        if ($items->isEmpty()) {
            return collect();
        }

        $reviewed = Review::query()
            ->where('customer_id', $customer->id)
            ->where('order_id', $order->id)
            ->whereIn('product_id', $items->pluck('product_id'))
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $items
            ->reject(fn ($item) => in_array((int) $item->product_id, $reviewed, true))
            ->map(function ($item) {
                $product = $item->product;

                return [
                    'id' => (int) $item->product_id,
                    'name' => $item->product_name ?: ($product?->name ?: 'Jewellery'),
                    'image' => storefront_image($item->imagePath()),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, Order>
     */
    public function deliveredOrdersNeedingReview(Customer $customer): Collection
    {
        return Order::query()
            ->where('customer_id', $customer->id)
            ->where('status', OrderStatus::DELIVERED)
            ->with(['items.product'])
            ->latest('delivered_at')
            ->latest('id')
            ->get()
            ->filter(fn (Order $order) => $this->unreviewedProducts($customer, $order)->isNotEmpty())
            ->values();
    }

    /**
     * @param  array{order_id: int, product_id: int, rating: int, comment?: string|null}  $data
     */
    public function submit(Customer $customer, array $data): Review
    {
        $order = Order::query()
            ->where('customer_id', $customer->id)
            ->where('status', OrderStatus::DELIVERED)
            ->find($data['order_id']);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => 'This order is not available for review yet.',
            ]);
        }

        $productId = (int) $data['product_id'];
        $allowed = $this->unreviewedProducts($customer, $order)->pluck('id')->all();

        if (! in_array($productId, $allowed, true)) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not part of the delivered order, or you already reviewed it.',
            ]);
        }

        $item = $order->items->firstWhere('product_id', $productId);
        $name = trim((string) ($customer->name ?: $order->customer_name)) ?: 'Customer';

        $review = $this->create([
            'product_id' => $productId,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'customer_name' => $name,
            'rating' => (int) $data['rating'],
            'comment' => trim((string) ($data['comment'] ?? '')) ?: null,
            'is_verified_purchase' => true,
            'is_featured' => false,
            'status' => 'pending',
        ]);

        $this->notifications->notifyNewReview($review->loadMissing('product'), $item?->product_name);

        return $review;
    }

    public function remainingCount(Customer $customer, int $orderId): int
    {
        $order = Order::query()
            ->where('customer_id', $customer->id)
            ->find($orderId);

        if (! $order) {
            return 0;
        }

        return $this->unreviewedProducts($customer, $order)->count();
    }

    public function refreshProductStats(int $productId): void
    {
        if ($productId < 1) {
            return;
        }

        $product = Product::withTrashed()->find($productId);
        if (! $product) {
            return;
        }

        $approved = Review::query()
            ->where('product_id', $productId)
            ->where('status', 'approved');

        $count = (int) $approved->count();
        $avg = $count > 0 ? round((float) $approved->avg('rating'), 2) : 0;

        $product->forceFill([
            'review_count' => $count,
            'avg_rating' => $avg,
        ])->save();
    }
}
