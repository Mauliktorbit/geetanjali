<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends AdminController
{
    public function __construct(protected ReviewService $service) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->only(['search', 'status', 'rating']));

        return view('admin.reviews.index', [
            'items' => $items,
            'statuses' => Review::statuses(),
        ]);
    }

    public function show(Review $review)
    {
        $review->load(['product', 'customer', 'order']);

        return view('admin.reviews.show', ['item' => $review]);
    }

    public function approve(Review $review)
    {
        $this->service->approve($review);

        return $this->success('Review approved. It will now show on the product page.');
    }

    public function reject(Review $review)
    {
        $this->service->reject($review);

        return $this->success('Review rejected.');
    }
}
