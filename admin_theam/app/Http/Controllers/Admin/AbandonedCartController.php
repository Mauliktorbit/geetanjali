<?php

namespace App\Http\Controllers\Admin;

use App\Models\AbandonedCart;
use App\Models\CommunicationLog;
use App\Repositories\AbandonedCartRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbandonedCartController extends AdminController
{
    public function __construct(protected AbandonedCartRepository $repository) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());

        return view('admin.abandoned-carts.index', compact('items'));
    }

    public function show(AbandonedCart $abandonedCart)
    {
        $abandonedCart->load('customer');

        return view('admin.abandoned-carts.show', ['item' => $abandonedCart]);
    }

    public function sendReminder(Request $request, AbandonedCart $abandonedCart)
    {
        $request->validate([
            'channel' => ['required', 'in:email,sms,whatsapp,push'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $channel = $request->input('channel');
        $message = $request->input('message')
            ?? 'You left items in your cart worth ' . money($abandonedCart->cart_value) . '. Complete your purchase now!';

        CommunicationLog::create([
            'customer_id' => $abandonedCart->customer_id,
            'channel' => $channel,
            'type' => 'abandoned_cart_reminder',
            'subject' => $request->input('subject', 'Complete your purchase'),
            'message' => $message,
            'status' => 'queued',
            'sent_by' => Auth::id(),
            'meta' => [
                'abandoned_cart_id' => $abandonedCart->id,
                'cart_value' => $abandonedCart->cart_value,
                'email' => $abandonedCart->email,
                'phone' => $abandonedCart->phone,
            ],
        ]);

        $abandonedCart->update([
            'reminder_status' => $channel . '_sent',
            'recovery_status' => $abandonedCart->recovery_status === 'pending'
                ? 'reminded'
                : $abandonedCart->recovery_status,
        ]);

        return $this->success(ucfirst($channel) . ' reminder queued.');
    }
}
