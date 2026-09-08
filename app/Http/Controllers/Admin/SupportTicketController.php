<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketCategory;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Repositories\SupportTicketRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends AdminController
{
    public function __construct(protected SupportTicketRepository $repository) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());

        return view('admin.support-tickets.index', compact('items'));
    }

    public function create()
    {
        return view('admin.support-tickets.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'category_id' => ['nullable', 'exists:support_ticket_categories,id'],
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => $this->generateTicketNumber(),
            'customer_id' => $data['customer_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'subject' => $data['subject'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'assigned_to' => $data['assigned_to'] ?? Auth::id(),
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $data['message'],
            'is_internal' => false,
        ]);

        return $this->success('Ticket created.', 'admin.support-tickets.show', [$ticket]);
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['customer', 'order', 'category', 'assignee', 'messages.user']);
        $staff = User::where('is_staff', true)->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.support-tickets.show', ['item' => $supportTicket, 'staff' => $staff]);
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'subject' => ['sometimes', 'string', 'max:255'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'category_id' => ['nullable', 'exists:support_ticket_categories,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $supportTicket->update($data);

        return $this->success('Ticket updated.');
    }

    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->delete();

        return $this->success('Ticket deleted.', 'admin.support-tickets.index');
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'message' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if (! $supportTicket->first_response_at) {
            $supportTicket->update(['first_response_at' => now(), 'status' => 'pending']);
        } else {
            $supportTicket->update(['status' => 'pending']);
        }

        return $this->success('Reply added.');
    }

    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $request->validate(['assigned_to' => ['required', 'exists:users,id']]);
        $supportTicket->update(['assigned_to' => $request->input('assigned_to')]);

        return $this->success('Ticket assigned.');
    }

    public function escalate(SupportTicket $supportTicket)
    {
        $supportTicket->update([
            'priority' => 'urgent',
            'escalated_at' => now(),
            'status' => 'escalated',
        ]);

        return $this->success('Ticket escalated.');
    }

    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        if ($request->filled('message')) {
            SupportTicketMessage::create([
                'support_ticket_id' => $supportTicket->id,
                'user_id' => Auth::id(),
                'message' => $request->input('message'),
                'is_internal' => false,
            ]);
        }

        $supportTicket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return $this->success('Ticket resolved.');
    }

    protected function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->limit(300)->get(['id', 'name', 'email']),
            'orders' => Order::latest()->limit(100)->get(['id', 'order_number']),
            'categories' => SupportTicketCategory::orderBy('name')->get(['id', 'name']),
            'staff' => User::where('is_staff', true)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        } while (SupportTicket::where('ticket_number', $number)->exists());

        return $number;
    }
}
