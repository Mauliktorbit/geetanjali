<?php

namespace App\Http\Controllers\Admin;

use App\Models\ReturnRequest;
use App\Repositories\ReturnRequestRepository;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends AdminController
{
    public function __construct(
        protected ReturnService $returnService,
        protected ReturnRequestRepository $repository
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());

        return view('admin.returns.index', compact('items'));
    }

    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.items', 'customer', 'items.orderItem', 'reason', 'reviewer']);

        return view('admin.returns.show', ['item' => $returnRequest]);
    }

    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $this->returnService->approve($returnRequest, $request->input('note'));

        return $this->success('Return approved.');
    }

    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate(['reason' => ['required', 'string']]);
        $this->returnService->reject($returnRequest, $request->input('reason'));

        return $this->success('Return rejected.');
    }

    public function inspect(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'inspection_status' => ['required', 'in:passed,failed,partial'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->returnService->completeInspection(
            $returnRequest,
            $request->input('inspection_status'),
            $request->input('notes')
        );

        return $this->success('Inspection completed.');
    }

    public function refund(ReturnRequest $returnRequest)
    {
        $this->returnService->processRefund($returnRequest);

        return $this->success('Return refund processed.');
    }

    public function replacement(ReturnRequest $returnRequest)
    {
        $order = $this->returnService->createReplacement($returnRequest);

        return $this->success('Replacement order created.', 'admin.orders.show', [$order]);
    }
}
