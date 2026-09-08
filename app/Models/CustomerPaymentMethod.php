<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPaymentMethod extends Model
{
    protected $fillable = [
        'customer_id',
        'brand',
        'last_four',
        'holder_name',
        'expiry_month',
        'expiry_year',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'expiry_month' => 'integer',
            'expiry_year' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function maskedNumber(): string
    {
        return strtoupper($this->brand).' **** '.$this->last_four;
    }
}
