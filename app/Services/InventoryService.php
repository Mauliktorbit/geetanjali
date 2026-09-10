<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(protected InventoryRepository $inventoryRepository) {}

    public function availableStockForProduct(int $productId): int
    {
        return (int) Inventory::query()->where('product_id', $productId)->sum('available_stock');
    }

    public function defaultWarehouseId(): int
    {
        $id = Warehouse::query()->where('is_default', true)->where('is_active', true)->value('id')
            ?? Warehouse::query()->where('is_active', true)->orderBy('id')->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) Warehouse::query()->create([
            'name' => 'Main Warehouse',
            'code' => 'MAIN',
            'is_default' => true,
            'is_active' => true,
        ])->id;
    }

    public function adjustStock(
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $quantityChange,
        string $reason,
        string $type = 'adjustment',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $meta = []
    ): Inventory {
        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $quantityChange,
            $reason, $type, $referenceType, $referenceId, $meta
        ) {
            $inventory = $this->getOrCreateInventory($productId, $variantId, $warehouseId);
            $previous = (int) $inventory->current_stock;
            $newQty = $previous + $quantityChange;

            if ($newQty < 0) {
                throw new InvalidArgumentException('Insufficient stock for adjustment.');
            }

            $availableDelta = $quantityChange;
            $newAvailable = max(0, (int) $inventory->available_stock + $availableDelta);

            $inventory->update([
                'current_stock' => $newQty,
                'available_stock' => $newAvailable,
            ]);

            $this->writeMovement(
                $productId,
                $variantId,
                $warehouseId,
                null,
                null,
                $type,
                $previous,
                $newQty,
                $quantityChange,
                $reason,
                $referenceType,
                $referenceId,
                $meta
            );

            return $inventory->fresh();
        });
    }

    public function transferStock(
        int $productId,
        ?int $variantId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $quantity,
        string $reason = 'Stock transfer'
    ): array {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Transfer quantity must be positive.');
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException('Source and destination warehouses must differ.');
        }

        return DB::transaction(function () use (
            $productId, $variantId, $fromWarehouseId, $toWarehouseId, $quantity, $reason
        ) {
            $from = $this->getOrCreateInventory($productId, $variantId, $fromWarehouseId);
            $to = $this->getOrCreateInventory($productId, $variantId, $toWarehouseId);

            if ((int) $from->available_stock < $quantity) {
                throw new InvalidArgumentException('Insufficient available stock to transfer.');
            }

            $fromPrevious = (int) $from->current_stock;
            $toPrevious = (int) $to->current_stock;

            $from->update([
                'current_stock' => $fromPrevious - $quantity,
                'available_stock' => (int) $from->available_stock - $quantity,
            ]);

            $to->update([
                'current_stock' => $toPrevious + $quantity,
                'available_stock' => (int) $to->available_stock + $quantity,
            ]);

            $this->writeMovement(
                $productId,
                $variantId,
                $fromWarehouseId,
                $fromWarehouseId,
                $toWarehouseId,
                'transfer',
                $fromPrevious,
                $fromPrevious - $quantity,
                -$quantity,
                $reason . ' (out)',
                null,
                null,
                ['direction' => 'out']
            );

            $this->writeMovement(
                $productId,
                $variantId,
                $toWarehouseId,
                $fromWarehouseId,
                $toWarehouseId,
                'transfer',
                $toPrevious,
                $toPrevious + $quantity,
                $quantity,
                $reason . ' (in)',
                null,
                null,
                ['direction' => 'in']
            );

            return [
                'from' => $from->fresh(),
                'to' => $to->fresh(),
            ];
        });
    }

    public function reserveStock(
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $quantity,
        string $reason = 'Order reservation',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Reserve quantity must be positive.');
        }

        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $quantity, $reason, $referenceType, $referenceId
        ) {
            $inventory = $this->getOrCreateInventory($productId, $variantId, $warehouseId);

            if ((int) $inventory->available_stock < $quantity) {
                throw new InvalidArgumentException('Insufficient available stock to reserve.');
            }

            $previous = (int) $inventory->current_stock;

            $inventory->update([
                'available_stock' => (int) $inventory->available_stock - $quantity,
                'reserved_stock' => (int) $inventory->reserved_stock + $quantity,
            ]);

            $this->writeMovement(
                $productId,
                $variantId,
                $warehouseId,
                null,
                null,
                'reservation',
                $previous,
                $previous,
                0,
                $reason,
                $referenceType,
                $referenceId,
                [
                    'reserved_delta' => $quantity,
                    'available_after' => $inventory->available_stock,
                    'reserved_after' => $inventory->reserved_stock,
                ]
            );

            return $inventory->fresh();
        });
    }

    public function releaseStock(
        int $productId,
        ?int $variantId,
        int $warehouseId,
        int $quantity,
        bool $returnToAvailable = true,
        string $reason = 'Release reservation',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Release quantity must be positive.');
        }

        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $quantity,
            $returnToAvailable, $reason, $referenceType, $referenceId
        ) {
            $inventory = $this->getOrCreateInventory($productId, $variantId, $warehouseId);
            $releaseQty = min($quantity, (int) $inventory->reserved_stock);
            $previous = (int) $inventory->current_stock;

            $data = [
                'reserved_stock' => (int) $inventory->reserved_stock - $releaseQty,
            ];

            if ($returnToAvailable) {
                $data['available_stock'] = (int) $inventory->available_stock + $releaseQty;
            } else {
                // Fulfilled sale: deduct from current stock as well
                $data['current_stock'] = max(0, $previous - $releaseQty);
            }

            $inventory->update($data);

            $this->writeMovement(
                $productId,
                $variantId,
                $warehouseId,
                null,
                null,
                $returnToAvailable ? 'release' : 'sale',
                $previous,
                (int) $inventory->current_stock,
                $returnToAvailable ? 0 : -$releaseQty,
                $reason,
                $referenceType,
                $referenceId,
                [
                    'released' => $releaseQty,
                    'returned_to_available' => $returnToAvailable,
                ]
            );

            return $inventory->fresh();
        });
    }

    protected function getOrCreateInventory(int $productId, ?int $variantId, int $warehouseId): Inventory
    {
        $inventory = $this->inventoryRepository->findStock($productId, $variantId, $warehouseId);

        if ($inventory) {
            return $inventory;
        }

        return Inventory::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'current_stock' => 0,
            'available_stock' => 0,
            'reserved_stock' => 0,
        ])->fresh();
    }

    protected function writeMovement(
        int $productId,
        ?int $variantId,
        ?int $warehouseId,
        ?int $fromWarehouseId,
        ?int $toWarehouseId,
        string $type,
        int $previous,
        int $newQty,
        int $change,
        string $reason,
        ?string $referenceType,
        ?int $referenceId,
        array $meta = []
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'from_warehouse_id' => $fromWarehouseId,
            'to_warehouse_id' => $toWarehouseId,
            'type' => $type,
            'previous_quantity' => $previous,
            'new_quantity' => $newQty,
            'quantity_change' => $change,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => Auth::id(),
            'meta' => $meta ?: null,
        ]);
    }
}
