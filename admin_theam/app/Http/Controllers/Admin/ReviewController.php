<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends AdminController
{
    public function __construct(protected ReviewRepository $repository) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());

        return view('admin.reviews.index', compact('items'));
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return $this->success('Review approved.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return $this->success('Review rejected.');
    }

    public function reply(Request $request, Review $review)
    {
        $request->validate(['admin_reply' => ['required', 'string']]);
        $review->update([
            'admin_reply' => $request->input('admin_reply'),
            'replied_at' => now(),
        ]);

        return $this->success('Reply saved.');
    }

    public function feature(Review $review)
    {
        $review->update(['is_featured' => ! $review->is_featured]);

        return $this->success($review->is_featured ? 'Review featured.' : 'Review unfeatured.');
    }

    public function hide(Review $review)
    {
        $review->update(['status' => 'hidden']);

        return $this->success('Review hidden.');
    }
}
