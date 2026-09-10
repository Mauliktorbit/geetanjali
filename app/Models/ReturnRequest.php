<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'order_id',
        'customer_id',
        'return_reason_id',
        'type',
        'status',
        'customer_reason',
        'evidence_files',
        'refund_amount',
        'return_shipping_deduction',
        'refund_method',
        'restock',
        'inspection_notes',
        'inspection_status',
        'replacement_order_id',
        'reviewed_by',
        'approved_at',
        'rejected_at',
        'picked_up_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence_files' => 'array',
            'refund_amount' => 'decimal:2',
            'return_shipping_deduction' => 'decimal:2',
            'restock' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'return_id');
    }

    public function replacementOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replacement_order_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusKey(): string
    {
        return \App\Enums\ReturnStatus::normalize((string) $this->status);
    }

    public function statusLabel(): string
    {
        return \App\Enums\ReturnStatus::label((string) $this->status);
    }

    public function statusBadge(): string
    {
        return \App\Enums\ReturnStatus::badge((string) $this->status);
    }

    public function customerDisplayName(): string
    {
        return $this->customer?->name
            ?: $this->order?->customer_name
            ?: '—';
    }

    public function customerDisplayPhone(): string
    {
        return $this->customer?->phone
            ?: $this->order?->customer_phone
            ?: '—';
    }

    public function productSummary(): string
    {
        $lines = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with('orderItem')->get();

        if ($this->relationLoaded('items')) {
            $lines->loadMissing('orderItem');
        }

        $names = $lines
            ->map(fn ($line) => $line->orderItem?->product_name)
            ->filter(fn ($name) => filled($name))
            ->values();

        if ($names->isEmpty()) {
            return $this->order?->productSummary() ?: '—';
        }

        $first = (string) $names->first();
        $extra = $names->count() - 1;

        return $extra > 0 ? $first.' +'.$extra.' more' : $first;
    }

    /**
     * @return array{
     *     status: string,
     *     tone: string,
     *     title: string,
     *     text: string,
     *     product: string,
     *     amount: string,
     *     latest_date: \Carbon\Carbon|null,
     *     steps: list<array{key: string, title: string, text: string, date: \Carbon\Carbon|null, state: string}>
     * }
     */
    public function journey(bool $forCustomer = false): array
    {
        $status = $this->statusKey();
        $order = $this->order;
        $amount = money($this->refund_amount);
        $product = $this->productSummary();
        $orderNo = $order?->order_number ?: '—';

        $refund = $this->relationLoaded('refunds')
            ? $this->refunds->sortByDesc(fn ($row) => $row->processed_at ?: $row->created_at)->first()
            : null;

        $refundedAt = $this->completed_at
            ?? $refund?->processed_at
            ?? ($status === \App\Enums\ReturnStatus::REFUNDED ? $this->updated_at : null);

        $paid = $refund && $refund->amount !== null ? money($refund->amount) : $amount;
        $method = $this->refundMethodPhrase($forCustomer);
        $reason = trim((string) $this->customer_reason);

        $steps = [
            $this->journeyStep(
                'ordered',
                $forCustomer ? 'You placed the order' : 'Order placed',
                $forCustomer ? 'Order #'.$orderNo : 'Order '.$orderNo,
                $order?->created_at,
                'done'
            ),
            $this->journeyStep(
                'delivered',
                $forCustomer ? 'It was delivered to you' : 'Order delivered',
                $forCustomer ? 'The jewellery reached you.' : 'The order was delivered to the customer.',
                $order?->delivered_at,
                'done'
            ),
            $this->journeyStep(
                'requested',
                $forCustomer ? 'You asked for a return' : 'Return requested',
                $reason !== ''
                    ? 'Reason: '.$reason
                    : ($forCustomer ? 'We received your return request.' : 'Customer submitted this return request.'),
                $this->created_at,
                'done'
            ),
        ];

        if ($status === \App\Enums\ReturnStatus::REJECTED) {
            $note = trim((string) $this->inspection_notes);
            $steps[] = $this->journeyStep(
                'rejected',
                $forCustomer ? 'Return not accepted' : 'Return rejected',
                $note !== '' && $note !== 'Rejected by admin'
                    ? $note
                    : ($forCustomer ? 'We could not accept this return.' : 'This return was not accepted.'),
                $this->rejected_at ?: $this->updated_at,
                'failed'
            );
        } elseif (in_array($status, [\App\Enums\ReturnStatus::APPROVED, \App\Enums\ReturnStatus::REFUNDED], true)) {
            $steps[] = $this->journeyStep(
                'approved',
                $forCustomer ? 'We accepted your return' : 'Return approved',
                $forCustomer ? 'Your request was approved.' : 'The return was approved.',
                $this->approved_at,
                'done'
            );

            if ($status === \App\Enums\ReturnStatus::REFUNDED) {
                $steps[] = $this->journeyStep(
                    'refunded',
                    $forCustomer ? 'Money returned to you' : 'Refund completed',
                    $forCustomer
                        ? $paid.' was sent back via '.$method.'.'
                        : $paid.' returned via '.$method.'.',
                    $refundedAt,
                    'done'
                );
            } else {
                $steps[] = $this->journeyStep(
                    'refunded',
                    $forCustomer ? 'Refund on the way' : 'Refund pending',
                    $forCustomer
                        ? $amount.' will be returned after processing.'
                        : $amount.' is waiting to be refunded.',
                    null,
                    'current'
                );
            }
        } else {
            $steps[] = $this->journeyStep(
                'approved',
                $forCustomer ? 'We are reviewing it' : 'Under review',
                $forCustomer ? 'Our team will check your request and update you here.' : 'Waiting for approval.',
                null,
                'current'
            );
            $steps[] = $this->journeyStep(
                'refunded',
                $forCustomer ? 'Refund' : 'Refund',
                $forCustomer
                    ? $amount.' will be returned if the return is approved.'
                    : $amount.' will be refunded after approval.',
                null,
                'upcoming'
            );
        }

        $headline = match ($status) {
            \App\Enums\ReturnStatus::REFUNDED => [
                'tone' => 'success',
                'title' => 'Refund successful',
                'text' => $forCustomer
                    ? $paid.' was returned to you'.($refundedAt ? ' on '.$refundedAt->format('d M Y') : '').'.'
                    : $paid.' was returned'.($refundedAt ? ' on '.$refundedAt->format('d M Y') : '').'.',
            ],
            \App\Enums\ReturnStatus::REJECTED => [
                'tone' => 'danger',
                'title' => $forCustomer ? 'Return not accepted' : 'Return rejected',
                'text' => $forCustomer
                    ? 'We could not accept this return'.($this->rejected_at ? ' on '.$this->rejected_at->format('d M Y') : '').'.'
                    : 'This return was rejected'.($this->rejected_at ? ' on '.$this->rejected_at->format('d M Y') : '').'.',
            ],
            \App\Enums\ReturnStatus::APPROVED => [
                'tone' => 'info',
                'title' => 'Return approved',
                'text' => $forCustomer
                    ? 'Your refund of '.$amount.' is being processed.'
                    : 'Refund of '.$amount.' is pending.',
            ],
            default => [
                'tone' => 'warning',
                'title' => $forCustomer ? 'Return request received' : 'Return requested',
                'text' => $forCustomer
                    ? 'We are reviewing your request. The next update will appear here.'
                    : 'Waiting for review and approval.',
            ],
        };

        $latest = collect($steps)->reverse()->first(
            fn (array $step) => in_array($step['state'], ['done', 'failed'], true) && ! empty($step['date'])
        );

        return [
            'status' => $status,
            'tone' => $headline['tone'],
            'title' => $headline['title'],
            'text' => $headline['text'],
            'product' => $product,
            'amount' => $amount,
            'latest_date' => $latest['date'] ?? $this->created_at,
            'steps' => $steps,
        ];
    }

    /**
     * @return array{key: string, title: string, text: string, date: \Carbon\Carbon|null, state: string}
     */
    protected function journeyStep(string $key, string $title, string $text, $date, string $state): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'text' => $text,
            'date' => $date,
            'state' => $state,
        ];
    }

    protected function refundMethodPhrase(bool $forCustomer): string
    {
        $key = strtolower((string) ($this->refund_method ?: 'original'));

        return match ($key) {
            'upi' => 'UPI',
            'bank', 'bank_transfer' => $forCustomer ? 'your bank account' : 'bank transfer',
            'wallet' => $forCustomer ? 'your wallet' : 'wallet',
            default => $forCustomer ? 'your original payment method' : 'original payment method',
        };
    }
}
