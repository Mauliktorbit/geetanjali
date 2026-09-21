<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'coupon_id',
        'category',
        'theme',
        'label',
        'discount_display',
        'discount_suffix',
        'title',
        'promo_code',
        'minimum_order',
        'image',
        'image_alt',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'minimum_order' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function offerCategory(): BelongsTo
    {
        return $this->belongsTo(OfferCategory::class, 'category', 'slug');
    }

    public function scopeStorefront($query)
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
     * @return array<string, string>
     */
    public static function themes(): array
    {
        return [
            'dark' => 'Dark card',
            'light' => 'Light card',
        ];
    }

    /**
     * @return list<string>
     */
    public static function cardLabels(): array
    {
        return ['Flat', 'Up To', 'Extra', 'Special'];
    }

    public function categoryLabel(): string
    {
        return $this->offerCategory?->name ?: OfferCategory::label((string) $this->category);
    }

    public function displayCode(): string
    {
        return strtoupper(trim((string) ($this->promo_code ?: $this->coupon?->code)));
    }

    public function validTillAdmin(): string
    {
        return $this->ends_at?->format('d/m/Y') ?: 'No end';
    }

    public function dateRange(): string
    {
        return $this->validTillAdmin();
    }

    public function validUntilLabel(): string
    {
        return $this->ends_at?->format('d M Y') ?: 'Limited period';
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

    /**
     * @return array<string, mixed>
     */
    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'theme' => in_array((string) $this->theme, ['dark', 'light'], true) ? $this->theme : 'dark',
            'category' => $this->category,
            'label' => $this->label,
            'discount_value' => $this->discount_display,
            'discount_suffix' => $this->discount_suffix ?: 'Off',
            'title' => $this->title,
            'promo_code' => $this->displayCode(),
            'image' => $this->image,
            'image_alt' => $this->image_alt ?: $this->title,
            'valid_until' => $this->validUntilLabel(),
            'min_order' => $this->minimumOrderLabel(),
        ];
    }

    public function minimumOrderLabel(): ?string
    {
        $amount = (float) ($this->minimum_order ?: $this->coupon?->minimum_cart ?: 0);

        return $amount > 0 ? 'Min. order '.money($amount) : null;
    }
}
