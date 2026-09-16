<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class WishlistService
{
    private const SESSION_KEY = 'wishlist';

    public function __construct(private readonly CatalogService $catalog) {}

    /**
     * @return array{items: list<array<string, mixed>>, count: int}
     */
    public function summary(): array
    {
        $items = $this->items();

        return [
            'items' => $items,
            'count' => count($items),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(): array
    {
        $customer = $this->customer();
        $raw = $customer ? $this->databaseRows($customer) : $this->sessionMap();

        $items = [];
        foreach ($raw as $row) {
            $id = (int) (is_array($row) ? ($row['id'] ?? $row['product_id'] ?? 0) : 0);
            $snapshot = is_array($row) ? ($row['payload'] ?? $row) : null;
            $product = $this->catalog->present($id, is_array($snapshot) ? $snapshot : null);
            if ($product) {
                $items[] = $product;
            }
        }

        return array_values($items);
    }

    public function count(): int
    {
        return count($this->productIds());
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        $customer = $this->customer();
        if ($customer) {
            return Wishlist::query()
                ->where('customer_id', $customer->id)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return array_values(array_map('intval', array_keys($this->sessionMap())));
    }

    public function add(int $productId, array $snapshot = []): bool
    {
        $product = $this->catalog->resolve($productId, $snapshot);
        if ($product === null) {
            return false;
        }

        $customer = $this->customer(true);
        if ($customer) {
            Wishlist::query()->updateOrCreate(
                ['customer_id' => $customer->id, 'product_id' => $productId],
                ['payload' => $product]
            );
            $this->forgetSession();

            return true;
        }

        $list = $this->sessionMap();
        $list[(string) $productId] = $product;
        $this->putSession($list);

        return true;
    }

    public function remove(int $productId): void
    {
        $customer = $this->customer();
        if ($customer) {
            Wishlist::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->delete();

            return;
        }

        $list = $this->sessionMap();
        unset($list[(string) $productId]);
        $this->putSession($list);
    }

    /**
     * @return array{added: bool, in_wishlist: bool}
     */
    public function toggle(int $productId, array $snapshot = []): array
    {
        if ($this->contains($productId)) {
            $this->remove($productId);

            return ['added' => false, 'in_wishlist' => false];
        }

        $ok = $this->add($productId, $snapshot);

        return ['added' => $ok, 'in_wishlist' => $ok];
    }

    public function contains(int $productId): bool
    {
        $customer = $this->customer();
        if ($customer) {
            return Wishlist::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->exists();
        }

        return isset($this->sessionMap()[(string) $productId]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadFor(int $productId): array
    {
        $customer = $this->customer();
        if ($customer) {
            $row = Wishlist::query()
                ->where('customer_id', $customer->id)
                ->where('product_id', $productId)
                ->first();

            return is_array($row?->payload) ? $row->payload : [];
        }

        $item = $this->sessionMap()[(string) $productId] ?? [];

        return is_array($item) ? $item : [];
    }

    public function clear(): void
    {
        $this->putSession([]);
        $customer = $this->customer();
        if ($customer) {
            Wishlist::query()->where('customer_id', $customer->id)->delete();
        }
    }

    public function moveToBag(int $productId, CartService $cart): bool
    {
        if (! $this->contains($productId)) {
            return false;
        }

        $ok = $cart->add($productId, 1, $this->payloadFor($productId));
        if ($ok) {
        $this->remove($productId);
        }

        return $ok;
    }

    public function moveAllToBag(CartService $cart): int
    {
        $moved = 0;
        foreach ($this->items() as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            if ($cart->add($id, 1, $item)) {
                $this->remove($id);
            $moved++;
            }
        }

        return $moved;
    }

    /**
     * @param  mixed  $guestWishlist
     */
    public function importGuest(mixed $guestWishlist): void
    {
        if (! is_array($guestWishlist)) {
            return;
        }

        foreach ($guestWishlist as $row) {
            $id = (int) (is_array($row) ? ($row['id'] ?? 0) : $row);
            if ($id > 0) {
                $this->add($id, is_array($row) ? $row : []);
            }
        }

        $this->forgetSession();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function catalog(): Collection
    {
        return $this->catalog->demo();
    }

    private function customer(bool $create = false): ?Customer
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user || $user->is_staff) {
            return null;
        }

        return Customer::forUser($user, $create);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function databaseRows(Customer $customer): array
    {
        return Wishlist::query()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->get()
            ->map(fn (Wishlist $row) => [
                'id' => (int) $row->product_id,
                'payload' => $row->payload,
            ])
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function sessionMap(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);

        return is_array($raw) ? $raw : [];
    }

    /**
     * @param  array<string, array<string, mixed>>  $list
     */
    private function putSession(array $list): void
    {
        Session::put(self::SESSION_KEY, $list);
    }

    private function forgetSession(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
