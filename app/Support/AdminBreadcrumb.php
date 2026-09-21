<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class AdminBreadcrumb
{
    public static function items(): array
    {
        $dashboardUrl = Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin');
        $dashboard = ['label' => 'Dashboard', 'url' => $dashboardUrl];

        $route = request()->route();
        $name = $route?->getName();

        if (! $name || ! str_starts_with($name, 'admin.')) {
            return [['label' => 'Dashboard']];
        }

        if (in_array($name, ['admin.dashboard', 'admin.dashboard.index'], true)) {
            return [['label' => 'Dashboard']];
        }

        if (in_array($name, ['admin.profile', 'admin.profile.edit', 'admin.profile.update'], true)) {
            return [$dashboard, ['label' => 'Profile']];
        }

        if (in_array($name, ['admin.policies.edit', 'admin.policies.update'], true)) {
            $key = (string) request()->route('policy');
            $labels = [
                'shipping' => 'Shipping Policy',
                'terms' => 'Terms & Conditions',
                'privacy' => 'Privacy Policy',
            ];

            return [$dashboard, ['label' => $labels[$key] ?? 'Policies']];
        }

        $parts = explode('.', $name);
        array_shift($parts);

        $resource = $parts[0] ?? '';
        $action = $parts[1] ?? 'index';
        $extra = $parts[2] ?? null;

        $resourceLabel = self::resourceLabel($resource);
        $indexName = 'admin.'.$resource.'.index';
        $indexUrl = Route::has($indexName) ? route($indexName) : null;

        $items = [$dashboard];
        $model = self::routeModel($route?->parameters() ?? []);
        $modelTitle = self::modelTitle($model);
        $showUrl = self::showUrl($resource, $model);

        if ($action === 'index' && $extra === null) {
            $items[] = ['label' => $resourceLabel];

            return $items;
        }

        $items[] = ['label' => $resourceLabel, 'url' => $indexUrl];

        if ($action === 'show' && $extra === null) {
            $items[] = ['label' => $modelTitle ?? 'View'];

            return $items;
        }

        if ($action === 'edit' && $extra === null) {
            if ($modelTitle) {
                $items[] = ['label' => $modelTitle, 'url' => $showUrl];
            }
            $items[] = ['label' => 'Edit'];

            return $items;
        }

        if ($action === 'create' && $extra === null) {
            $items[] = ['label' => 'Add'];

            return $items;
        }

        if ($modelTitle) {
            $items[] = ['label' => $modelTitle, 'url' => $showUrl];
        }

        $items[] = ['label' => self::actionLabel($action, $extra)];

        return $items;
    }

    public static function shown(?bool $set = null): bool
    {
        static $shown = false;

        if ($set === true) {
            $shown = true;
        }

        return $shown;
    }

    private static function resourceLabel(string $resource): string
    {
        $labels = [
            'orders' => 'Orders',
            'products' => 'Products',
            'categories' => 'Categories',
            'collections' => 'Collections',
            'inventory' => 'Inventory',
            'customers' => 'Customers',
            'coupons' => 'Offers & Coupons',
            'flash-sales' => 'Flash Sales',
            'reviews' => 'Reviews',
            'returns' => 'Returns & Refunds',
            'payments' => 'Payments',
            'settings' => 'Settings',
            'reports' => 'Reports',
            'enquiries' => 'Enquiries',
            'profile' => 'Profile',
            'banners' => 'Banners',
            'blogs' => 'Blogs',
            'pages' => 'Pages',
            'faqs' => 'FAQs',
            'staff' => 'Staff',
            'roles' => 'Roles',
            'warehouses' => 'Warehouses',
            'suppliers' => 'Suppliers',
            'brands' => 'Brands',
            'tags' => 'Tags',
            'attributes' => 'Attributes',
            'menus' => 'Menus',
            'couriers' => 'Couriers',
            'shipments' => 'Shipments',
            'campaigns' => 'Campaigns',
            'testimonials' => 'Testimonials',
            'notifications' => 'Notifications',
            'backups' => 'Backups',
            'integrations' => 'Integrations',
            'gift-cards' => 'Gift Cards',
            'tax-rates' => 'Tax Rates',
            'shipping-classes' => 'Shipping Classes',
            'shipping-methods' => 'Shipping Methods',
            'shipping-zones' => 'Shipping Zones',
            'store-locations' => 'Store Locations',
            'customer-groups' => 'Customer Groups',
            'seo-redirects' => 'SEO Redirects',
            'homepage-sections' => 'Homepage',
            'purchase-orders' => 'Purchase Orders',
            'return-reasons' => 'Return Reasons',
            'support-tickets' => 'Support Tickets',
            'support-ticket-categories' => 'Ticket Categories',
            'product-questions' => 'Product Questions',
            'audit-logs' => 'Audit Logs',
            'expenses' => 'Expenses',
            'affiliates' => 'Affiliates',
            'abandoned-carts' => 'Abandoned Carts',
            'policies' => 'Policies',
            'offers' => 'Offers',
        ];

        return $labels[$resource] ?? Str::headline(str_replace('-', ' ', $resource));
    }

    private static function actionLabel(string $action, ?string $extra = null): string
    {
        $labels = [
            'create' => 'Add',
            'edit' => 'Edit',
            'show' => 'View',
            'adjust' => 'Update Stock',
            'low-stock' => 'Low Stock',
            'out-of-stock' => 'Out of Stock',
            'movements' => 'Stock Movements',
            'transfer' => 'Transfer Stock',
            'valuation' => 'Stock Valuation',
            'ageing' => 'Stock Ageing',
            'sales' => 'Sales',
            'products' => 'Products',
            'customers' => 'Customers',
            'profitability' => 'Profitability',
        ];

        $key = $extra ?: $action;

        return $labels[$key] ?? Str::headline(str_replace('-', ' ', $key));
    }

    private static function routeModel(array $parameters): mixed
    {
        foreach ($parameters as $value) {
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    private static function modelTitle(mixed $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        foreach (['name', 'order_number', 'return_number', 'ticket_number', 'title', 'code', 'sku', 'email', 'subject'] as $field) {
            $value = $model->getAttribute($field);
            if (filled($value)) {
                return (string) $value;
            }
        }

        $key = $model->getKey();

        return $key !== null ? '#'.$key : null;
    }

    private static function showUrl(string $resource, mixed $model): ?string
    {
        $showName = 'admin.'.$resource.'.show';

        if (! $model instanceof Model || ! Route::has($showName)) {
            return null;
        }

        try {
            return route($showName, $model);
        } catch (Throwable) {
            return null;
        }
    }
}
