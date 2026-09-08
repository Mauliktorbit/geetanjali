<?php

use App\Http\Controllers\Admin\AbandonedCartController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\GiftCardController;
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductQuestionController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ReturnReasonController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoRedirectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\ShippingClassController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StoreLocationController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupportTicketCategoryController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Products
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::post('products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulk');
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
        Route::post('products/{product}/activate', [ProductController::class, 'activate'])->name('products.activate');
        Route::post('products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');
        Route::resource('products', ProductController::class);

        // Customers
        Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::post('customers/{customer}/block', [CustomerController::class, 'block'])->name('customers.block');
        Route::post('customers/{customer}/unblock', [CustomerController::class, 'unblock'])->name('customers.unblock');
        Route::post('customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset-password');
        Route::post('customers/{customer}/assign-group', [CustomerController::class, 'assignGroup'])->name('customers.assign-group');
        Route::post('customers/{customer}/store-credit', [CustomerController::class, 'addStoreCredit'])->name('customers.store-credit');
        Route::post('customers/{customer}/reward-points', [CustomerController::class, 'addRewardPoints'])->name('customers.reward-points');
        Route::post('customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.notes');
        Route::post('customers/{customer}/merge', [CustomerController::class, 'merge'])->name('customers.merge');
        Route::resource('customers', CustomerController::class);

        // Orders
        Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.add');
        Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.remove');
        Route::post('orders/{order}/items/{item}/qty', [OrderController::class, 'changeQty'])->name('orders.items.qty');
        Route::post('orders/{order}/discount', [OrderController::class, 'applyDiscount'])->name('orders.discount');
        Route::post('orders/{order}/address', [OrderController::class, 'changeAddress'])->name('orders.address');
        Route::post('orders/{order}/invoice', [OrderController::class, 'generateInvoice'])->name('orders.invoice');
        Route::get('orders/{order}/packing-slip', [OrderController::class, 'packingSlip'])->name('orders.packing-slip');
        Route::post('orders/{order}/assign-courier', [OrderController::class, 'assignCourier'])->name('orders.assign-courier');
        Route::post('orders/{order}/tracking', [OrderController::class, 'addTracking'])->name('orders.tracking');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{order}/approve-return', [OrderController::class, 'approveReturn'])->name('orders.approve-return');
        Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
        Route::post('orders/{order}/resend-confirmation', [OrderController::class, 'resendConfirmation'])->name('orders.resend-confirmation');
        Route::post('orders/{order}/contact', [OrderController::class, 'contactCustomer'])->name('orders.contact');
        Route::post('orders/{order}/notes', [OrderController::class, 'addNote'])->name('orders.notes');
        Route::resource('orders', OrderController::class)->except(['destroy']);

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjust');
        Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust.store');
        Route::get('inventory/transfer', [InventoryController::class, 'transferForm'])->name('inventory.transfer');
        Route::post('inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer.store');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('inventory/valuation', [InventoryController::class, 'valuation'])->name('inventory.valuation');
        Route::get('inventory/ageing', [InventoryController::class, 'ageing'])->name('inventory.ageing');
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('inventory/out-of-stock', [InventoryController::class, 'outOfStock'])->name('inventory.out-of-stock');

        // Payments
        Route::post('payments/record', [PaymentController::class, 'record'])->name('payments.record');
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        Route::post('payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.status');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

        // Returns
        Route::post('returns/{returnRequest}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
        Route::post('returns/{returnRequest}/reject', [ReturnController::class, 'reject'])->name('returns.reject');
        Route::post('returns/{returnRequest}/inspect', [ReturnController::class, 'inspect'])->name('returns.inspect');
        Route::post('returns/{returnRequest}/refund', [ReturnController::class, 'refund'])->name('returns.refund');
        Route::post('returns/{returnRequest}/replacement', [ReturnController::class, 'replacement'])->name('returns.replacement');
        Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{returnRequest}', [ReturnController::class, 'show'])->name('returns.show');

        // Shipments
        Route::post('shipments/reverse', [ShipmentController::class, 'reverse'])->name('shipments.reverse');
        Route::post('shipments/{shipment}/assign', [ShipmentController::class, 'assign'])->name('shipments.assign');
        Route::post('shipments/{shipment}/track', [ShipmentController::class, 'track'])->name('shipments.track');
        Route::post('shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
        Route::get('shipments/{shipment}/label', [ShipmentController::class, 'label'])->name('shipments.label');
        Route::resource('shipments', ShipmentController::class)->only(['index', 'create', 'store', 'show']);

        // Reviews & Q&A
        Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        Route::post('reviews/{review}/feature', [ReviewController::class, 'feature'])->name('reviews.feature');
        Route::post('reviews/{review}/hide', [ReviewController::class, 'hide'])->name('reviews.hide');
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');

        Route::post('product-questions/{productQuestion}/answer', [ProductQuestionController::class, 'answer'])->name('product-questions.answer');
        Route::post('product-questions/{productQuestion}/publish', [ProductQuestionController::class, 'publish'])->name('product-questions.publish');
        Route::post('product-questions/{productQuestion}/hide', [ProductQuestionController::class, 'hide'])->name('product-questions.hide');
        Route::post('product-questions/{productQuestion}/assign', [ProductQuestionController::class, 'assign'])->name('product-questions.assign');
        Route::get('product-questions', [ProductQuestionController::class, 'index'])->name('product-questions.index');

        // Abandoned carts
        Route::post('abandoned-carts/{abandonedCart}/remind', [AbandonedCartController::class, 'sendReminder'])->name('abandoned-carts.remind');
        Route::get('abandoned-carts', [AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
        Route::get('abandoned-carts/{abandonedCart}', [AbandonedCartController::class, 'show'])->name('abandoned-carts.show');

        // Purchase orders
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::resource('purchase-orders', PurchaseOrderController::class);

        // Support tickets
        Route::post('support-tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('support-tickets.reply');
        Route::post('support-tickets/{supportTicket}/assign', [SupportTicketController::class, 'assign'])->name('support-tickets.assign');
        Route::post('support-tickets/{supportTicket}/escalate', [SupportTicketController::class, 'escalate'])->name('support-tickets.escalate');
        Route::post('support-tickets/{supportTicket}/resolve', [SupportTicketController::class, 'resolve'])->name('support-tickets.resolve');
        Route::resource('support-tickets', SupportTicketController::class)->except(['edit']);

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general');
        Route::post('settings/order', [SettingController::class, 'updateOrder'])->name('settings.order');
        Route::post('settings/product', [SettingController::class, 'updateProduct'])->name('settings.product');
        Route::post('settings/customer', [SettingController::class, 'updateCustomer'])->name('settings.customer');
        Route::post('settings/invoice', [SettingController::class, 'updateInvoice'])->name('settings.invoice');

        // Staff & roles
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);

        // Notifications & audit
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Backups
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // Nested attribute values & menu items
        Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'index'])->name('attributes.values.index');
        Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attributes.values.store');
        Route::put('attributes/{attribute}/values/{attributeValue}', [AttributeValueController::class, 'update'])->name('attributes.values.update');
        Route::delete('attributes/{attribute}/values/{attributeValue}', [AttributeValueController::class, 'destroy'])->name('attributes.values.destroy');

        Route::get('menus/{menu}/items', [MenuItemController::class, 'index'])->name('menus.items.index');
        Route::post('menus/{menu}/items', [MenuItemController::class, 'store'])->name('menus.items.store');
        Route::put('menus/{menu}/items/{menuItem}', [MenuItemController::class, 'update'])->name('menus.items.update');
        Route::delete('menus/{menu}/items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menus.items.destroy');
        Route::post('menus/{menu}/items/reorder', [MenuItemController::class, 'reorder'])->name('menus.items.reorder');

        // Existing catalog / CMS resources
        foreach ([
            'brands' => BrandController::class,
            'categories' => CategoryController::class,
            'attributes' => AttributeController::class,
            'tags' => TagController::class,
            'tax-rates' => TaxRateController::class,
            'shipping-classes' => ShippingClassController::class,
            'warehouses' => WarehouseController::class,
            'suppliers' => SupplierController::class,
            'customer-groups' => CustomerGroupController::class,
            'coupons' => CouponController::class,
            'banners' => BannerController::class,
            'campaigns' => CampaignController::class,
            'blogs' => BlogController::class,
            'pages' => PageController::class,
            'faqs' => FaqController::class,
            'testimonials' => TestimonialController::class,
            'store-locations' => StoreLocationController::class,
            'menus' => MenuController::class,
            'seo-redirects' => SeoRedirectController::class,
            'gift-cards' => GiftCardController::class,
            'affiliates' => AffiliateController::class,
            'flash-sales' => FlashSaleController::class,
            'expenses' => ExpenseController::class,
            'return-reasons' => ReturnReasonController::class,
            'shipping-zones' => ShippingZoneController::class,
            'shipping-methods' => ShippingMethodController::class,
            'couriers' => CourierController::class,
            'integrations' => IntegrationController::class,
            'support-ticket-categories' => SupportTicketCategoryController::class,
            'homepage-sections' => HomepageSectionController::class,
        ] as $resource => $controller) {
            Route::post("{$resource}/bulk", [$controller, 'bulk'])->name("{$resource}.bulk");
            Route::resource($resource, $controller);
        }
    });
});
