<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferCategory;
use App\Models\Coupon;
use App\Repositories\OfferRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class OfferService extends BaseService
{
    public function __construct(OfferRepository $repository)
    {
        parent::__construct($repository);
    }

    public function forStorefront(?string $category = 'all'): Collection
    {
        return Coupon::query()
            ->available()
            ->latest('id')
            ->get()
            ->values()
            ->map(fn (Coupon $coupon, int $index) => $coupon->toStorefrontCard($index));
    }

    public function save(array $data, ?Offer $offer = null): Offer
    {
        $title = trim((string) $data['title']);
        $payload = [
            'category' => $this->resolveCategorySlug($data),
            'title' => $title,
            'discount_display' => trim((string) $data['discount_display']),
            'image_alt' => $title,
            'ends_at' => parse_dmy($data['ends_at'] ?? null, true),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (array_key_exists('image', $data)) {
            if ($offer) {
                $this->deleteStoredImage($offer->image);
            }
            $payload['image'] = $data['image'];
        }

        if ($offer) {
            return $this->update($offer, $payload);
        }

        $payload['label'] = 'Flat';
        $payload['discount_suffix'] = 'Off';
        $payload['theme'] = $this->nextTheme();
        $payload['sort_order'] = (int) Offer::query()->max('sort_order') + 1;
        $payload['starts_at'] = now()->startOfDay();

        return $this->create($payload);
    }

    public function delete(Model $model): bool
    {
        if ($model instanceof Offer) {
            $this->deleteStoredImage($model->image);
        }

        return parent::delete($model);
    }

    protected function deleteStoredImage(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || str_starts_with($path, 'public/') || str_starts_with($path, 'http')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveCategorySlug(array $data): string
    {
        $selected = trim((string) ($data['category'] ?? ''));
        $newName = trim((string) ($data['new_category'] ?? ''));

        if ($selected === '__new__' || ($selected === '' && $newName !== '')) {
            return OfferCategory::firstOrCreateNamed($newName)->slug;
        }

        $existing = OfferCategory::query()->where('slug', $selected)->first();
        if ($existing) {
            return $existing->slug;
        }

        return OfferCategory::firstOrCreateNamed($newName !== '' ? $newName : $selected)->slug;
    }

    protected function nextTheme(): string
    {
        return Offer::query()->orderByDesc('id')->value('theme') === 'dark' ? 'light' : 'dark';
    }
}
