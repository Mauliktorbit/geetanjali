<?php

namespace App\Services\Integrations;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Integrations\Contracts\CourierInterface;
use Illuminate\Support\Str;

class NullCourierAdapter implements CourierInterface
{
    public function createShipment(Order $order, array $options = []): array
    {
        $awb = 'NULLAWB' . strtoupper(Str::random(10));

        return [
            'success' => true,
            'awb' => $awb,
            'tracking_number' => $awb,
            'label_path' => null,
            'status' => 'assigned',
            'gateway' => 'null',
            'options' => $options,
        ];
    }

    public function cancelShipment(Shipment $shipment): array
    {
        return [
            'success' => true,
            'shipment_id' => $shipment->id,
            'status' => 'cancelled',
            'gateway' => 'null',
        ];
    }

    public function track(string $trackingNumber): array
    {
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'events' => [],
            'gateway' => 'null',
        ];
    }

    public function createReversePickup(Order $order, array $options = []): array
    {
        $awb = 'NULLRVP' . strtoupper(Str::random(10));

        return [
            'success' => true,
            'awb' => $awb,
            'tracking_number' => $awb,
            'status' => 'reverse_pickup_requested',
            'gateway' => 'null',
            'options' => $options,
        ];
    }
}
