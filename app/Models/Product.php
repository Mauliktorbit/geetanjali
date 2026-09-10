<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'category_id',
        'subcategory_id',
        'brand_id',
        'product_type',
        'regular_price',
        'sale_price',
        'cost_price',
        'tax_rate_id',
        'hsn_sac',
        'min_order_qty',
        'max_order_qty',
        'weight',
        'length',
        'width',
        'height',
        'main_image',
        'gallery_images',
        'video_url',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'return_eligible',
        'return_days',
        'warranty',
        'badge',
        'metal',
        'purity',
        'stone',
        'style',
        'occasion',
        'certification',
        'dimensions_text',
        'tax_note',
        'highlights',
        'shipping_class_id',
        'estimated_delivery',
        'cod_available',
        'is_featured',
        'is_new_arrival',
        'is_bestseller',
        'is_active',
        'is_archived',
        'view_count',
        'wishlist_count',
        'avg_rating',
        'review_count',
        'sold_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'gallery_images' => 'array',
            'highlights' => 'array',
            'return_eligible' => 'boolean',
            'cod_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
            'is_archived' => 'boolean',
            'avg_rating' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function shippingClass(): BelongsTo
    {
        return $this->belongsTo(ShippingClass::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()
            ->where('status', 'approved')
            ->latest();
    }

    public function scopeStorefront($query)
    {
        return $query->where('is_active', true)->where('is_archived', false);
    }

    public function scopeInCollection($query, string $slug)
    {
        return $query->storefront()->whereHas(
            'collections',
            fn ($c) => $c->where('slug', $slug)->where('is_active', true)
        );
    }

    public function scopeKundan($query)
    {
        return $query->inCollection('kundan');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_product_id')
            ->wherePivot('relation_type', 'related')
            ->withPivot('relation_type');
    }

    public function frequentlyBoughtTogether(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_product_id')
            ->wherePivot('relation_type', 'fbt')
            ->withPivot('relation_type');
    }

    public function digitalAssets(): HasMany
    {
        return $this->hasMany(DigitalAsset::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->regular_price);
    }

    public function imagePath(): string
    {
        if (filled($this->main_image)) {
            return (string) $this->main_image;
        }

        foreach ((array) $this->gallery_images as $image) {
            if (filled($image)) {
                return (string) $image;
            }
        }

        $slug = $this->relationLoaded('category') ? $this->category?->slug : null;

        return \App\Services\StorefrontCatalogService::categoryImage($slug);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->inventories()->sum('available_stock');
    }
}
