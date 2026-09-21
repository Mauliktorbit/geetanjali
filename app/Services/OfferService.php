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
        $offers = Offer::query()
            ->with('coupon')
            ->storefront()
            ->when($category && $category !== 'all', fn ($query) => $query->where('category', $category))
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->get();

        $linkedCouponIds = $offers->pluck('coupon_id')->filter()->map(fn ($id) => (int) $id)->all();
        $linkedCodes = $offers
            ->map(fn (Offer $offer) => $offer->displayCode())
            ->filter()
            ->map(fn (string $code) => strtoupper($code))
            ->all();

        $cards = $offers->map(fn (Offer $offer) => $offer->toCardArray())->values();

        if ($category && $category !== 'all') {
            return $cards;
        }

        $coupons = Coupon::query()
            ->available()
            ->latest('id')
            ->get()
            ->filter(function (Coupon $coupon) use ($linkedCouponIds, $linkedCodes) {
                if (in_array((int) $coupon->id, $linkedCouponIds, true)) {
                    return false;
                }

                $code = strtoupper(trim((string) $coupon->code));

                return $code === '' || ! in_array($code, $linkedCodes, true);
            })
            ->values();

        foreach ($coupons as $index => $coupon) {
            $cards->push($coupon->toStorefrontCard($cards->count() + $index));
        }

        return $cards->values();
    }

    public function save(array $data, ?Offer $offer = null): Offer
    {
        $title = trim((string) $data['title']);
        $payload = [
            'category' => $this->resolveCategorySlug($data),
            'label' => (string) ($data['label'] ?? 'Flat'),
            'title' => $title,
            'discount_display' => trim((string) $data['discount_display']),
            'discount_suffix' => trim((string) ($data['discount_suffix'] ?? 'Off')) ?: 'Off',
            'theme' => in_array(($data['theme'] ?? ''), ['dark', 'light'], true) ? $data['theme'] : 'dark',
            'minimum_order' => isset($data['minimum_order']) && $data['minimum_order'] !== ''
                ? (float) $data['minimum_order']
                : null,
            'image_alt' => $title,
            'starts_at' => parse_dmy($data['starts_at'] ?? null) ?: now()->startOfDay(),
            'ends_at' => parse_dmy($data['ends_at'] ?? null, true),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (array_key_exists('image', $data)) {
            if ($offer) {
                $this->deleteStoredImage($offer->image);
            }
            $payload['image'] = $data['image'] ?: null;
        }

        if ($offer) {
            return $this->update($offer, $payload);
        }

        $payload['sort_order'] = (int) Offer::query()->max('sort_order') + 1;

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
}
