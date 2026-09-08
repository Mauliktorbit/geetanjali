<?php

namespace App\Services;

use App\Models\Courier;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Integrations\Contracts\CourierInterface;
use App\Services\Integrations\NullCourierAdapter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentService
{
    public function __construct(protected ?CourierInterface $courierAdapter = null)
    {
        $this->courierAdapter ??= new NullCourierAdapter();
    }

    public function createShipment(Order $order, array $data = []): Shipment
    {
        return DB::transaction(function () use ($order, $data) {
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'courier_id' => $data['courier_id'] ?? null,
                'status' => 'created',
                'cod_amount' => $data['cod_amount']
                    ?? (strtolower((string) $order->payment_method) === 'cod' ? $order->grand_total : 0),
                'is_reverse' => false,
                'meta' => $data['meta'] ?? null,
            ]);

            if (! empty($data['courier_id'])) {
                $this->assignCourier($shipment, (int) $data['courier_id']);
            }

            return $shipment->fresh(['courier', 'order']);
        });
    }

    public function assignCourier(Shipment $shipment, int $courierId): Shipment
    {
        return DB::transaction(function () use ($shipment, $courierId) {
            $courier = Courier::findOrFail($courierId);

            if (! $courier->is_active) {
                throw new InvalidArgumentException('Courier is inactive.');
            }

            $adapter = $this->resolveAdapter($courier);
            $response = $adapter->createShipment($shipment->order, [
                'courier_code' => $courier->code,
                'shipment_id' => $shipment->id,
            ]);

            $shipment->update([
                'courier_id' => $courier->id,
                'awb_number' => $response['awb'] ?? $shipment->awb_number,
                'tracking_number' => $response['tracking_number'] ?? $shipment->tracking_number,
                'label_path' => $response['label_path'] ?? $shipment->label_path,
                'status' => $response['status'] ?? 'assigned',
                'meta' => array_merge($shipment->meta ?? [], ['courier_response' => $response]),
            ]);

            $shipment->order?->update([
                'shipping_partner' => $courier->name,
                'awb_number' => $shipment->awb_number,
                'tracking_number' => $shipment->tracking_number,
            ]);

            return $shipment->fresh(['courier']);
        });
    }

    public function addTracking(Shipment $shipment, string $trackingNumber, array $trackingData = []): Shipment
    {
        $shipment->update([
            'tracking_number' => $trackingNumber,
            'tracking_data' => array_merge($shipment->tracking_data ?? [], $trackingData, [
                'updated_at' => now()->toIso8601String(),
            ]),
            'status' => $trackingData['status'] ?? $shipment->status,
        ]);

        $shipment->order?->update(['tracking_number' => $trackingNumber]);

        return $shipment->fresh();
    }

    public function cancel(Shipment $shipment, ?string $reason = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $reason) {
            if (in_array($shipment->status, ['delivered', 'cancelled'], true)) {
                throw new InvalidArgumentException('Shipment cannot be cancelled.');
            }

            $courier = $shipment->courier;
            if ($courier) {
                $this->resolveAdapter($courier)->cancelShipment($shipment);
            }

            $shipment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'meta' => array_merge($shipment->meta ?? [], ['cancel_reason' => $reason]),
            ]);

            return $shipment->fresh();
        });
    }

    public function reversePickup(Order $order, array $data = []): Shipment
    {
        return DB::transaction(function () use ($order, $data) {
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'courier_id' => $data['courier_id'] ?? null,
                'status' => 'reverse_created',
                'is_reverse' => true,
                'meta' => $data['meta'] ?? null,
            ]);

            if ($shipment->courier_id) {
                $courier = Courier::find($shipment->courier_id);
                if ($courier) {
                    $response = $this->resolveAdapter($courier)->createReversePickup($order, [
                        'shipment_id' => $shipment->id,
                    ]);
                    $shipment->update([
                        'awb_number' => $response['awb'] ?? null,
                        'tracking_number' => $response['tracking_number'] ?? null,
                        'pickup_requested_at' => now(),
                        'status' => 'reverse_pickup_requested',
                        'meta' => array_merge($shipment->meta ?? [], ['courier_response' => $response]),
                    ]);
                }
            }

            return $shipment->fresh();
        });
    }

    protected function resolveAdapter(Courier $courier): CourierInterface
    {
        return $this->courierAdapter ?? new NullCourierAdapter();
    }
}
