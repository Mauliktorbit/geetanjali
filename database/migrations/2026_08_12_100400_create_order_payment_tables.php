<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discount_type')->index();
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('per_customer_limit')->default(1);
            $table->decimal('minimum_cart', 12, 2)->nullable();
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->json('included_products')->nullable();
            $table->json('excluded_products')->nullable();
            $table->json('included_categories')->nullable();
            $table->json('customer_groups')->nullable();
            $table->boolean('new_customers_only')->default(false);
            $table->json('payment_methods')->nullable();
            $table->json('locations')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone', 20)->nullable()->index();
            $table->string('source')->default('website')->index();
            $table->string('sales_channel')->nullable()->index();
            $table->string('status')->default('new')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->string('payment_method')->nullable()->index();
            $table->string('shipping_method')->nullable()->index();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('shipping_charge', 14, 2)->default(0);
            $table->decimal('cod_charge', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->decimal('cost_total', 14, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('shipping_city')->nullable()->index();
            $table->string('shipping_state')->nullable()->index();
            $table->string('shipping_country')->nullable()->index();
            $table->string('shipping_pincode', 20)->nullable()->index();
            $table->string('shipping_partner')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('awb_number')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['created_at', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('variant_label')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('hsn_sac')->nullable();
            $table->decimal('total', 12, 2);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_internal')->default(true);
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->nullable()->index();
            $table->string('payment_method')->index();
            $table->string('gateway')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('status')->default('pending')->index();
            $table->string('failure_reason')->nullable();
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->string('refund_status')->nullable();
            $table->string('settlement_status')->nullable()->index();
            $table->decimal('gateway_charges', 12, 2)->default(0);
            $table->decimal('net_settlement', 14, 2)->default(0);
            $table->json('gateway_response')->nullable();
            $table->string('payment_link')->nullable();
            $table->boolean('is_partial')->default(false);
            $table->boolean('is_advance')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('type')->default('tax_invoice')->index();
            $table->date('invoice_date');
            $table->string('financial_year', 20)->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('cgst', 14, 2)->default(0);
            $table->decimal('sgst', 14, 2)->default(0);
            $table->decimal('igst', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('place_of_supply')->nullable();
            $table->boolean('is_b2b')->default(false);
            $table->string('customer_gstin')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('tax_breakup')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('credit_note_number')->unique();
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('debit_note_number')->unique();
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
    }
};
