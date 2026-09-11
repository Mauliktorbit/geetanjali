<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreReviewRequest;
use App\Services\AccountService;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly AccountService $account,
        private readonly ReviewService $reviews,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);
        $pendingOrders = $this->reviews->deliveredOrdersNeedingReview($customer);
        $pendingByOrder = $pendingOrders->mapWithKeys(
            fn ($order) => [$order->id => $this->reviews->unreviewedProducts($customer, $order)]
        );

        return view('frontend.account.reviews', [
            'user' => $user,
            'customer' => $customer,
            'accountSection' => 'reviews',
            'pendingOrders' => $pendingOrders,
            'pendingByOrder' => $pendingByOrder,
            'reviews' => $customer->reviews()->with(['product', 'order'])->latest()->paginate(10),
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'My Account', 'url' => route('account.index')],
                ['label' => 'My Reviews', 'url' => null],
            ],
        ]);
    }

    public function store(StoreReviewRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $review = $this->reviews->submit($customer, $request->validated());
        $remaining = $this->reviews->remainingCount($customer, (int) $review->order_id);
        $message = $remaining > 0
            ? 'Saved. You can review the next item in this order.'
            : 'Thank you. Your review is with our team and will appear after approval.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'remaining' => $remaining,
            ]);
        }

        return back()->with('success', $message);
    }

    public function later(Request $request): JsonResponse|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $data = $request->validate([
            'order_id' => ['required', 'integer'],
        ]);

        $skipped = collect((array) $request->session()->get('review_prompt_later', []))
            ->map(fn ($id) => (int) $id)
            ->push((int) $data['order_id'])
            ->unique()
            ->values()
            ->all();

        $request->session()->put('review_prompt_later', $skipped);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    private function staffRedirect(Request $request): ?RedirectResponse
    {
        if ($request->user()?->is_staff) {
            return redirect()->route('admin.dashboard');
        }

        return null;
    }
}
