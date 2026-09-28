<?php

namespace App\Services;

use App\Models\ShippingMethod;
use App\Repositories\ShippingMethodRepository;
use Illuminate\Support\Str;

class ShippingMethodService extends BaseService
{
    public function __construct(ShippingMethodRepository $repository)
    {
        parent::__construct($repository);
    }

    public function ensureDefaults(): void
    {
        if (ShippingMethod::query()->exists()) {
            return;
        }

        foreach ($this->defaultRules() as $rule) {
            ShippingMethod::query()->create($rule);
        }
    }

    /**
     * @return array<string, array{label: string, charge: float, eta: string, days: int, free_above: float|null}>
     */
    public function checkoutOptions(?float $orderAmount = null): array
    {
        $this->ensureDefaults();

        $options = [];
        $methods = ShippingMethod::query()
            ->where('is_active', true)
            ->orderBy('rate')
            ->orderBy('name')
            ->get();

        foreach ($methods as $method) {
            if ($method->min_order_amount !== null && $orderAmount !== null
                && $orderAmount < (float) $method->min_order_amount) {
                continue;
            }

            $charge = (float) $method->rate;
            if ($method->free_shipping_threshold !== null && $orderAmount !== null
                && $orderAmount >= (float) $method->free_shipping_threshold) {
                $charge = 0.0;
            }

            $options[$method->code] = [
                'label' => $method->name,
                'charge' => $charge,
                'eta' => $method->estimated_delivery ?: '3–5 business days',
                'days' => $this->deliveryDays($method),
                'free_above' => $method->free_shipping_threshold !== null
                    ? (float) $method->free_shipping_threshold
                    : null,
            ];
        }

        return $options;
    }

    /**
     * @return array{label: string, eta: string, days: int}
     */
    public function metaForCode(string $code): array
    {
        $method = ShippingMethod::query()->where('code', $code)->first();

        if ($method) {
            return [
                'label' => $method->name,
                'eta' => $method->estimated_delivery ?: '3–5 business days',
                'days' => $this->deliveryDays($method),
            ];
        }

        foreach ($this->defaultRules() as $rule) {
            if ($rule['code'] === $code) {
                return [
                    'label' => $rule['name'],
                    'eta' => $rule['estimated_delivery'],
                    'days' => $this->daysFromEta($rule['estimated_delivery']),
                ];
            }
        }

        return [
            'label' => 'Standard Delivery',
            'eta' => '3–5 business days',
            'days' => 5,
        ];
    }

    public function uniqueCode(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'shipping';
        $code = $base;
        $i = 1;

        while (
            ShippingMethod::query()
                ->where('code', $code)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $code = $base.'-'.$i++;
        }

        return $code;
    }

    public function deliveryDays(ShippingMethod $method): int
    {
        return $this->daysFromEta((string) $method->estimated_delivery);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultRules(): array
    {
        return [
            [
                'name' => 'Standard Delivery',
                'code' => 'standard',
                'type' => 'flat',
                'rate' => 0,
                'min_order_amount' => null,
                'free_shipping_threshold' => null,
                'estimated_delivery' => '3–5 business days',
                'cod_available' => true,
                'cod_charges' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Express Delivery',
                'code' => 'express',
                'type' => 'express',
                'rate' => 199,
                'min_order_amount' => null,
                'free_shipping_threshold' => null,
                'estimated_delivery' => '1–2 business days',
                'cod_available' => true,
                'cod_charges' => 0,
                'is_active' => true,
            ],
        ];
    }

    private function daysFromEta(string $eta): int
    {
        preg_match_all('/\d+/', $eta, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        return $numbers === [] ? 5 : max($numbers);
    }
}
