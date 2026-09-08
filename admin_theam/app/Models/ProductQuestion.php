<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'question',
        'answer',
        'status',
        'assigned_to',
        'answered_by',
        'notify_customer',
        'is_faq',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'notify_customer' => 'boolean',
            'is_faq' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
