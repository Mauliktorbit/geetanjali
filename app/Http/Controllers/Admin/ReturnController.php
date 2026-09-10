<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Repositories\ReturnRequestRepository;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ReturnController extends AdminController
{
    public function __construct(
        protected ReturnService $returnService,
        protected ReturnRequestRepository $repository
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->only(['search', 'status']));

        return view('admin.returns.index', [
            'items' => $items,
            'statuses' => ReturnStatus::labels(),
        ]);
    }

    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.items.product', 'customer', 'items.orderItem.product', 'refunds']);

        return view('admin.returns.show', [
            'item' => $returnRequest,
            'statuses' => ReturnStatus::nextOptions((string) $returnRequest->status),
        ]);
    }

    public function updateStatus(Request $request, ReturnRequest $returnRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(ReturnStatus::labels()))],
        ], [
            'status.required' => 'Please choose a status.',
            'status.in' => 'Choose Requested, Approved, Rejected or Refunded.',
        ]);

        try {
            $this->returnService->updateStatus($returnRequest, $data['status']);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }

        $message = match (\App\Enums\ReturnStatus::normalize($data['status'])) {
            \App\Enums\ReturnStatus::APPROVED => 'Return approved. Refund is now pending.',
            \App\Enums\ReturnStatus::REJECTED => 'Return was not accepted.',
            \App\Enums\ReturnStatus::REFUNDED => 'Refund marked as completed.',
            default => 'Return status updated.',
        };

        return $this->success($message);
    }
}
