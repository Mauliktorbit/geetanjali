<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OfferCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'category', 'slug');
    }

    public static function firstOrCreateNamed(string $name): self
    {
        $name = trim($name);
        $slug = Str::slug($name) ?: 'offer';

        $existing = static::query()
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::query()->create([
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) static::query()->max('sort_order') + 1,
            'is_active' => true,
        ]);
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function tabs(): array
    {
        $tabs = [['key' => 'all', 'label' => 'All Offers']];

        foreach (static::ordered()->where('is_active', true)->get() as $category) {
            $tabs[] = ['key' => $category->slug, 'label' => $category->name];
        }

        return $tabs;
    }

    public static function normalize(mixed $category): string
    {
        $category = strtolower(trim((string) $category));

        if ($category === '' || $category === 'all') {
            return 'all';
        }

        return static::query()->where('slug', $category)->where('is_active', true)->exists()
            ? $category
            : 'all';
    }

    public static function label(string $slug): string
    {
        return static::query()->where('slug', $slug)->value('name')
            ?: ucfirst(str_replace('-', ' ', $slug));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
