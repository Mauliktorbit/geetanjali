<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'minimum_cart',
        'maximum_discount',
        'included_products',
        'excluded_products',
        'included_categories',
        'customer_groups',
        'new_customers_only',
        'payment_methods',
        'locations',
        'is_stackable',
        'meta',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'minimum_cart' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'included_products' => 'array',
            'excluded_products' => 'array',
            'included_categories' => 'array',
            'customer_groups' => 'array',
            'payment_methods' => 'array',
            'locations' => 'array',
            'meta' => 'array',
            'new_customers_only' => 'boolean',
            'is_stackable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function offersWithCode(): HasMany
    {
        return $this->hasMany(Offer::class, 'promo_code', 'code');
    }

    public function linkedOffers(): Collection
    {
        $byId = $this->relationLoaded('offers') ? $this->offers : $this->offers()->get();
        $byCode = $this->relationLoaded('offersWithCode') ? $this->offersWithCode : $this->offersWithCode()->get();

        return $byId->merge($byCode)->unique('id')->values();
    }

    public function typeKey(): string
    {
        return in_array((string) $this->discount_type, ['percent', 'percentage'], true)
            ? 'percent'
            : 'fixed';
    }

    public function typeLabel(): string
    {
        return $this->typeKey() === 'percent' ? 'Percent' : 'Fixed amount';
    }

    public function discountLabel(): string
    {
        $value = (float) $this->discount_value;
        $clean = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $this->typeKey() === 'percent' ? $clean.'%' : money($value);
    }

    public function dateRange(): string
    {
        $start = $this->starts_at?->format('d/m/Y') ?: 'Anytime';
        $end = $this->ends_at?->format('d/m/Y') ?: 'No end';

        return $start.' – '.$end;
    }

    public function statusKey(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function statusLabel(): string
    {
        return match ($this->statusKey()) {
            'scheduled' => 'Scheduled',
            'expired' => 'Expired',
            'inactive' => 'Inactive',
            default => 'Active',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->statusKey()) {
            'scheduled' => 'info',
            'expired' => 'warning',
            'inactive' => 'inactive',
            default => 'active',
        };
    }
}
