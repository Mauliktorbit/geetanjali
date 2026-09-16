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

    public function scopeAvailable($query)
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function ($inner) use ($now) {
                $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($inner) use ($now) {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function toCartListItem(float $subtotal = 0, ?string $appliedCode = null): array
    {
        $code = strtoupper((string) $this->code);
        $min = (float) ($this->minimum_cart ?? 0);
        $eligible = $min <= 0 || $subtotal >= $min;
        $need = (! $eligible && $min > 0) ? max(0, $min - $subtotal) : 0;

        return [
            'code' => $code,
            'name' => $this->name,
            'discount' => $this->discountLabel(),
            'valid_until' => $this->ends_at?->format('d M Y'),
            'min_order' => $min > 0 ? money($min) : null,
            'eligible' => $eligible,
            'need_more' => $need > 0 ? money($need) : null,
            'applied' => strtoupper((string) $appliedCode) === $code,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontCard(int $index = 0): array
    {
        $images = [
            'public/assets/images/offers/diamond.jpg',
            'public/assets/images/offers/gold.jpg',
            'public/assets/images/offers/kundan.jpg',
            'public/assets/images/offers/prepaid.jpg',
            'public/assets/images/occasions/festival.jpg',
        ];

        $percent = $this->typeKey() === 'percent';
        $value = (float) $this->discount_value;
        $clean = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return [
            'id' => $this->id,
            'theme' => $index % 2 === 0 ? 'dark' : 'light',
            'category' => 'all',
            'label' => 'Flat',
            'discount_value' => $percent ? $clean.'%' : money($value),
            'discount_suffix' => 'Off',
            'title' => $this->name,
            'promo_code' => strtoupper((string) $this->code),
            'image' => $images[$index % count($images)],
            'image_alt' => $this->name,
            'valid_until' => $this->ends_at?->format('d M Y') ?: 'Limited period',
            'min_order' => $this->minimum_cart ? 'Min. order '.money((float) $this->minimum_cart) : null,
        ];
    }
}
