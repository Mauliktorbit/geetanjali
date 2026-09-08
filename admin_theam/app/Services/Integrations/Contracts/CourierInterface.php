<?php

namespace App\Services\Integrations\Contracts;

use App\Models\Order;
use App\Models\Shipment;

interface CourierInterface
{
    public function createShipment(Order $order, array $options = []): array;

    public function cancelShipment(Shipment $shipment): array;

    public function track(string $trackingNumber): array;

    public function createReversePickup(Order $order, array $options = []): array;
}
