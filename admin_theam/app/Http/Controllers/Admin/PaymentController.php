<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends AdminController
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentRepository $repository
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());

        return view('admin.payments.index', [
            'items' => $items,
            'statuses' => PaymentStatus::labels(),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['order', 'customer', 'refunds']);

        return view('admin.payments.show', ['item' => $payment]);
    }

    public function record(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'gateway' => ['nullable', 'string', 'max:50'],
            'gateway_charges' => ['nullable', 'numeric', 'min:0'],
            'is_advance' => ['nullable', 'boolean'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $payment = $this->paymentService->recordPayment($order, $data);

        return $this->success('Payment recorded.', 'admin.payments.show', [$payment]);
    }

    public function refund(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->paymentService->processRefund(
            $payment->order,
            (float) $request->input('amount'),
            $payment,
            $request->input('method', 'original'),
            $request->input('reason')
        );

        return $this->success('Refund processed.');
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => ['required', 'string'],
            'failure_reason' => ['nullable', 'string'],
        ]);

        $status = $request->input('status');

        if ($status === PaymentStatus::FAILED) {
            $this->paymentService->markFailed($payment, $request->input('failure_reason', 'Marked failed'));
        } else {
            $payment->update(['status' => $status]);
            if ($payment->order) {
                $this->paymentService->syncOrderPaymentStatus($payment->order);
            }
        }

        return $this->success('Payment status updated.');
    }
}
