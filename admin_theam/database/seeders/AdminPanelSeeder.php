<?php

namespace Database\Seeders;

use App\Models\AbandonedCart;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Expense;
use App\Models\Faq;
use App\Models\Integration;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnReason;
use App\Models\Setting;
use App\Models\ShippingClass;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\Supplier;
use App\Models\SupportTicketCategory;
use App\Models\Tag;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPanelSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view', 'create', 'edit', 'delete', 'approve', 'export', 'refund',
            'change_price', 'change_stock', 'view_profit', 'view_customer_data', 'manage_settings',
        ];

        $modules = [
            'dashboard', 'products', 'categories', 'brands', 'inventory', 'orders', 'customers',
            'payments', 'shipping', 'returns', 'coupons', 'marketing', 'reviews', 'suppliers',
            'warehouses', 'cms', 'reports', 'expenses', 'support', 'notifications', 'staff',
            'integrations', 'settings', 'audit',
        ];

        foreach ($modules as $module) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => "{$module}.{$permission}", 'guard_name' => 'web']);
            }
        }

        $roles = [
            'Super Admin', 'Owner', 'Store Manager', 'Product Manager', 'Inventory Manager',
            'Order Staff', 'Packing Staff', 'Customer Support', 'Accountant', 'Marketing Manager', 'Report Viewer',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if (in_array($roleName, ['Super Admin', 'Owner'], true)) {
                $role->syncPermissions(Permission::all());
            } elseif ($roleName === 'Report Viewer') {
                $role->syncPermissions(Permission::where('name', 'like', '%.view')->orWhere('name', 'like', 'reports.%')->get());
            } else {
                $role->syncPermissions(Permission::where('name', 'like', '%.view')->orWhere('name', 'like', '%.create')->orWhere('name', 'like', '%.edit')->get());
            }
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@ecommerce.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '9999999999',
                'is_active' => true,
                'is_staff' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Super Admin');

        foreach ([
            ['general.store_name', 'Commerce Hub'],
            ['general.currency', 'INR'],
            ['general.timezone', 'Asia/Kolkata'],
            ['general.date_format', 'd M Y'],
            ['general.language', 'en'],
            ['general.contact_email', 'support@ecommerce.test'],
            ['general.contact_phone', '1800-000-000'],
            ['general.business_address', '12 Market Street, Mumbai, MH 400001'],
            ['general.gstin', '27AAAAA0000A1Z5'],
            ['general.pan', 'AAAAA0000A'],
            ['invoice.prefix', 'INV'],
            ['invoice.terms', 'Goods once sold will be replaced as per return policy.'],
            ['order.prefix', 'ORD'],
            ['order.minimum_order', '0'],
            ['order.auto_confirmation', '0'],
            ['product.low_stock_threshold', '5'],
            ['product.default_tax_rate', '18'],
            ['customer.guest_checkout', '1'],
            ['tax.pricing_mode', 'exclusive'],
        ] as [$key, $value]) {
            Setting::updateOrCreate(['key' => $key], ['group' => explode('.', $key)[0], 'value' => $value, 'type' => 'string']);
        }

        $groups = [
            'New', 'Regular', 'VIP', 'Wholesale', 'Corporate', 'Dealer', 'Distributor', 'Influencer', 'High-return', 'Blocked',
        ];
        foreach ($groups as $g) {
            CustomerGroup::firstOrCreate(
                ['slug' => Str::slug($g)],
                ['name' => $g, 'discount_percent' => $g === 'VIP' ? 10 : ($g === 'Wholesale' ? 15 : 0), 'is_active' => true]
            );
        }

        foreach ([
            'Damaged', 'Wrong product', 'Wrong size', 'Quality issue', 'Missing item', 'Not as described', 'Changed mind', 'Delivery delay',
        ] as $reason) {
            ReturnReason::firstOrCreate(['name' => $reason, 'type' => 'return'], ['is_active' => true]);
            ReturnReason::firstOrCreate(['name' => $reason, 'type' => 'replacement'], ['is_active' => true]);
        }

        foreach (['Order Issue', 'Payment', 'Shipping', 'Product', 'Return', 'General'] as $cat) {
            SupportTicketCategory::firstOrCreate(['name' => $cat], ['is_active' => true]);
        }

        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            ['name' => 'Main Warehouse', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'country' => 'India', 'pincode' => '400001', 'is_default' => true, 'is_active' => true, 'priority' => 1]
        );

        $tax18 = TaxRate::firstOrCreate(['name' => 'GST 18%'], ['hsn_sac' => '9987', 'cgst' => 9, 'sgst' => 9, 'igst' => 18, 'is_active' => true]);
        $tax12 = TaxRate::firstOrCreate(['name' => 'GST 12%'], ['hsn_sac' => '6109', 'cgst' => 6, 'sgst' => 6, 'igst' => 12, 'is_active' => true]);

        $shippingClass = ShippingClass::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard', 'cost' => 49, 'is_active' => true]);

        $brands = collect(['Nike', 'Adidas', 'Samsung', 'Apple', 'Sony'])->map(function ($name) {
            return Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => "{$name} official products", 'is_active' => true]);
        });

        $electronics = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'display_order' => 1, 'is_active' => true]);
        $fashion = Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion', 'display_order' => 2, 'is_active' => true]);
        $phones = Category::firstOrCreate(['slug' => 'phones', 'parent_id' => $electronics->id], ['name' => 'Phones', 'display_order' => 1, 'is_active' => true]);
        $apparel = Category::firstOrCreate(['slug' => 'apparel', 'parent_id' => $fashion->id], ['name' => 'Apparel', 'display_order' => 1, 'is_active' => true]);

        $size = Attribute::firstOrCreate(['slug' => 'size'], ['name' => 'Size', 'type' => 'select', 'is_active' => true]);
        $color = Attribute::firstOrCreate(['slug' => 'colour'], ['name' => 'Colour', 'type' => 'color', 'is_active' => true]);
        foreach (['S', 'M', 'L', 'XL'] as $i => $v) {
            AttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => $v], ['sort_order' => $i]);
        }
        foreach ([['Red', '#ff0000'], ['Blue', '#0000ff'], ['Black', '#111111']] as $i => [$v, $c]) {
            AttributeValue::firstOrCreate(['attribute_id' => $color->id, 'value' => $v], ['color_code' => $c, 'sort_order' => $i]);
        }

        foreach (['new', 'sale', 'trending', 'eco'] as $tag) {
            Tag::firstOrCreate(['slug' => $tag], ['name' => ucfirst($tag)]);
        }

        $supplier = Supplier::firstOrCreate(
            ['email' => 'supplier@example.com'],
            ['name' => 'Global Supplies', 'company_name' => 'Global Supplies Pvt Ltd', 'phone' => '9876543210', 'gstin' => '27BBBBB0000B1Z5', 'city' => 'Pune', 'state' => 'Maharashtra', 'lead_time_days' => 5, 'is_active' => true]
        );

        $zone = ShippingZone::firstOrCreate(['name' => 'India Metro'], [
            'countries' => ['India'],
            'states' => ['Maharashtra', 'Delhi', 'Karnataka'],
            'is_active' => true,
        ]);

        ShippingMethod::firstOrCreate(['code' => 'flat'], [
            'shipping_zone_id' => $zone->id, 'name' => 'Flat Rate', 'type' => 'flat', 'rate' => 49, 'cod_available' => true, 'cod_charges' => 30, 'estimated_delivery' => '3-5 days', 'is_active' => true,
        ]);
        ShippingMethod::firstOrCreate(['code' => 'free'], [
            'shipping_zone_id' => $zone->id, 'name' => 'Free Shipping', 'type' => 'free', 'rate' => 0, 'free_shipping_threshold' => 999, 'cod_available' => true, 'estimated_delivery' => '4-6 days', 'is_active' => true,
        ]);

        Courier::firstOrCreate(['code' => 'manual'], ['name' => 'Manual Courier', 'provider' => 'manual', 'is_active' => true]);

        Coupon::firstOrCreate(['code' => 'WELCOME10'], [
            'name' => 'Welcome 10%', 'discount_type' => 'percentage', 'discount_value' => 10,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addMonths(3),
            'usage_limit' => 1000, 'per_customer_limit' => 1, 'minimum_cart' => 499, 'is_active' => true,
        ]);

        $products = [];
        $catalog = [
            ['Wireless Earbuds Pro', 'simple', 2999, 2499, $electronics->id, $phones->id, $brands[2]->id, 40],
            ['Smart Watch X', 'simple', 7999, 6999, $electronics->id, $phones->id, $brands[3]->id, 25],
            ['Running Shoes', 'variable', 4999, 3999, $fashion->id, $apparel->id, $brands[0]->id, 60],
            ['Sports Tee', 'variable', 1299, 999, $fashion->id, $apparel->id, $brands[1]->id, 80],
            ['Bluetooth Speaker', 'simple', 3499, null, $electronics->id, null, $brands[4]->id, 15],
            ['Gift Card 1000', 'gift_card', 1000, null, null, null, null, 999],
            ['Setup Service', 'service', 499, null, null, null, null, 0],
            ['E-Book Guide', 'digital', 199, 149, null, null, null, 0],
        ];

        foreach ($catalog as $i => [$name, $type, $price, $sale, $cat, $sub, $brand, $stock]) {
            $product = Product::updateOrCreate(
                ['sku' => 'SKU-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'barcode' => '890' . str_pad((string) ($i + 1), 10, '0', STR_PAD_LEFT),
                    'short_description' => "Premium {$name}",
                    'description' => "Detailed description for {$name}. Quality assured and ready to ship.",
                    'category_id' => $cat,
                    'subcategory_id' => $sub,
                    'brand_id' => $brand,
                    'product_type' => $type,
                    'regular_price' => $price,
                    'sale_price' => $sale,
                    'cost_price' => round(($sale ?? $price) * 0.6, 2),
                    'tax_rate_id' => $i % 2 ? $tax12->id : $tax18->id,
                    'hsn_sac' => $i % 2 ? '6109' : '8518',
                    'min_order_qty' => 1,
                    'weight' => 0.5,
                    'shipping_class_id' => $shippingClass->id,
                    'estimated_delivery' => '3-5 business days',
                    'cod_available' => true,
                    'return_eligible' => true,
                    'is_featured' => $i < 3,
                    'is_new_arrival' => $i < 4,
                    'is_bestseller' => $i < 2,
                    'is_active' => true,
                    'published_at' => now(),
                ]
            );
            $products[] = $product;

            if (! in_array($type, ['digital', 'service', 'gift_card'], true)) {
                Inventory::updateOrCreate(
                    ['product_id' => $product->id, 'product_variant_id' => null, 'warehouse_id' => $warehouse->id, 'batch_lot_number' => null],
                    [
                        'supplier_id' => $supplier->id,
                        'current_stock' => $stock,
                        'available_stock' => $stock,
                        'reserved_stock' => 0,
                        'reorder_level' => 5,
                        'unit_cost' => $product->cost_price,
                    ]
                );
            }
        }

        $vipGroup = CustomerGroup::where('slug', 'vip')->first();
        $regularGroup = CustomerGroup::where('slug', 'regular')->first();

        $customers = [];
        foreach ([
            ['Riya Sharma', 'riya@example.com', '9811111111', 'female'],
            ['Aman Verma', 'aman@example.com', '9822222222', 'male'],
            ['Neha Patel', 'neha@example.com', '9833333333', 'female'],
        ] as $i => [$name, $email, $phone, $gender]) {
            $customer = Customer::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'gender' => $gender,
                    'customer_group_id' => $i === 0 ? $vipGroup?->id : $regularGroup?->id,
                    'wallet_balance' => 200,
                    'reward_points' => 150,
                    'acquisition_source' => 'website',
                    'is_verified' => true,
                ]
            );
            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'type' => 'shipping', 'is_default' => true],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'address_line1' => ($i + 1) . ' Palm Residency',
                    'city' => $i === 1 ? 'Pune' : 'Mumbai',
                    'state' => 'Maharashtra',
                    'country' => 'India',
                    'pincode' => $i === 1 ? '411001' : '400001',
                ]
            );
            $customers[] = $customer;
        }

        $statuses = ['new', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested'];
        foreach ($customers as $ci => $customer) {
            for ($o = 0; $o < 2; $o++) {
                $product = $products[($ci + $o) % count($products)];
                $qty = 1 + $o;
                $unit = $product->sale_price ?? $product->regular_price;
                $subtotal = $unit * $qty;
                $shipping = 49;
                $tax = round($subtotal * 0.18, 2);
                $total = $subtotal + $shipping + $tax;
                $status = $statuses[($ci + $o) % count($statuses)];

                $order = Order::create([
                    'order_number' => 'ORD-' . now()->format('ymd') . '-' . str_pad((string) (($ci * 2) + $o + 1), 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'source' => $o === 0 ? 'website' : 'phone',
                    'sales_channel' => $o === 0 ? 'Website' : 'Phone',
                    'status' => $status,
                    'payment_status' => in_array($status, ['cancelled'], true) ? 'failed' : 'paid',
                    'payment_method' => $o === 0 ? 'upi' : 'cod',
                    'shipping_method' => 'flat',
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'tax_amount' => $tax,
                    'shipping_charge' => $shipping,
                    'cod_charge' => $o === 1 ? 30 : 0,
                    'grand_total' => $total + ($o === 1 ? 30 : 0),
                    'paid_amount' => in_array($status, ['cancelled'], true) ? 0 : $total,
                    'cost_total' => ($product->cost_price ?? 0) * $qty,
                    'billing_address' => [
                        'name' => $customer->name, 'phone' => $customer->phone,
                        'address_line1' => 'Billing Address', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'country' => 'India', 'pincode' => '400001',
                    ],
                    'shipping_address' => [
                        'name' => $customer->name, 'phone' => $customer->phone,
                        'address_line1' => 'Shipping Address', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'country' => 'India', 'pincode' => '400001',
                    ],
                    'shipping_city' => 'Mumbai',
                    'shipping_state' => 'Maharashtra',
                    'shipping_country' => 'India',
                    'shipping_pincode' => '400001',
                    'warehouse_id' => $warehouse->id,
                    'created_by' => $admin->id,
                    'confirmed_at' => now()->subDays(2),
                    'shipped_at' => in_array($status, ['shipped', 'delivered'], true) ? now()->subDay() : null,
                    'delivered_at' => $status === 'delivered' ? now() : null,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'cost_price' => $product->cost_price ?? 0,
                    'tax' => $tax,
                    'tax_rate' => 18,
                    'total' => $subtotal + $tax,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => $status,
                    'note' => 'Seeded order',
                    'user_id' => $admin->id,
                ]);

                Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'transaction_id' => 'TXN' . $order->id . rand(1000, 9999),
                    'payment_method' => $order->payment_method,
                    'gateway' => $order->payment_method === 'upi' ? 'razorpay' : 'cod',
                    'amount' => $order->grand_total,
                    'status' => $order->payment_status === 'paid' ? 'paid' : 'failed',
                    'gateway_charges' => $order->payment_method === 'upi' ? 10 : 0,
                    'net_settlement' => $order->grand_total - ($order->payment_method === 'upi' ? 10 : 0),
                    'paid_at' => $order->payment_status === 'paid' ? now() : null,
                ]);

                $customer->increment('total_orders');
                $customer->increment('total_spent', $order->grand_total);
                $customer->update([
                    'average_order_value' => $customer->total_spent / max(1, $customer->total_orders),
                    'last_order_at' => now(),
                ]);
            }
        }

        AbandonedCart::create([
            'customer_id' => $customers[0]->id,
            'email' => $customers[0]->email,
            'phone' => $customers[0]->phone,
            'cart_items' => [
                ['product_id' => $products[0]->id, 'name' => $products[0]->name, 'qty' => 1, 'price' => $products[0]->sale_price],
            ],
            'cart_value' => $products[0]->sale_price ?? $products[0]->regular_price,
            'cart_date' => now()->subHours(6),
            'last_activity_at' => now()->subHours(5),
            'recovery_status' => 'pending',
        ]);

        Banner::firstOrCreate(['title' => 'Summer Sale Hero'], [
            'type' => 'hero', 'link' => '/sale', 'content' => 'Up to 40% off', 'sort_order' => 1, 'is_active' => true, 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(),
        ]);

        Page::firstOrCreate(['slug' => 'about'], ['title' => 'About Us', 'type' => 'about', 'content' => 'We are a single-vendor ecommerce store.', 'is_active' => true]);
        Page::firstOrCreate(['slug' => 'privacy-policy'], ['title' => 'Privacy Policy', 'type' => 'privacy', 'content' => 'Privacy policy content.', 'is_active' => true]);
        Page::firstOrCreate(['slug' => 'terms'], ['title' => 'Terms & Conditions', 'type' => 'terms', 'content' => 'Terms content.', 'is_active' => true]);
        Page::firstOrCreate(['slug' => 'shipping-policy'], ['title' => 'Shipping Policy', 'type' => 'shipping', 'content' => 'Shipping policy content.', 'is_active' => true]);
        Page::firstOrCreate(['slug' => 'return-refund-policy'], ['title' => 'Return & Refund Policy', 'type' => 'return', 'content' => 'Return policy content.', 'is_active' => true]);

        Faq::firstOrCreate(['question' => 'How long does delivery take?'], ['answer' => 'Usually 3-5 business days.', 'category' => 'Shipping', 'is_active' => true]);

        foreach ([
            ['Packaging', 'Mailers & Tape', 2500],
            ['Courier', 'Monthly courier billing', 12000],
            ['Marketing', 'Meta ads', 8000],
            ['Software', 'SaaS tools', 3000],
        ] as [$cat, $title, $amount]) {
            Expense::firstOrCreate(
                ['title' => $title, 'expense_date' => now()->toDateString()],
                ['category' => $cat, 'amount' => $amount, 'payment_method' => 'bank_transfer', 'created_by' => $admin->id]
            );
        }

        foreach ([
            ['Razorpay', 'razorpay', 'payment'],
            ['Shiprocket', 'shiprocket', 'courier'],
            ['MSG91', 'msg91', 'sms'],
            ['Twilio WhatsApp', 'twilio', 'whatsapp'],
            ['SMTP', 'smtp', 'email'],
            ['Google Analytics', 'ga4', 'analytics'],
            ['Meta Pixel', 'meta_pixel', 'analytics'],
        ] as [$name, $provider, $category]) {
            Integration::firstOrCreate(
                ['provider' => $provider, 'category' => $category],
                ['name' => $name, 'is_active' => false, 'is_sandbox' => true, 'settings' => []]
            );
        }
    }
}
