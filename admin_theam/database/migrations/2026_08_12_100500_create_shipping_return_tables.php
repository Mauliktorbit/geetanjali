<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('countries')->nullable();
            $table->json('states')->nullable();
            $table->json('cities')->nullable();
            $table->json('pincodes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->index(); // flat, free, weight, price, distance, express, same_day, local, pickup
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('free_shipping_threshold', 12, 2)->nullable();
            $table->decimal('min_weight', 10, 3)->nullable();
            $table->decimal('max_weight', 10, 3)->nullable();
            $table->boolean('cod_available')->default(true);
            $table->decimal('cod_charges', 12, 2)->default(0);
            $table->string('estimated_delivery')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('non_serviceable_locations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('pincode'); // pincode, city, state
            $table->string('value');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['type', 'value']);
        });

        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('provider')->nullable();
            $table->json('credentials')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('awb_number')->nullable()->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('status')->default('created')->index();
            $table->string('label_path')->nullable();
            $table->timestamp('pickup_requested_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('is_reverse')->default(false);
            $table->string('ndr_status')->nullable();
            $table->text('ndr_reason')->nullable();
            $table->string('delivery_proof')->nullable();
            $table->decimal('cod_amount', 14, 2)->default(0);
            $table->string('cod_reconciliation_status')->nullable();
            $table->json('tracking_data')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('return_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('return'); // return, replacement
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('return_reason_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('return'); // return, replacement
            $table->string('status')->default('requested')->index();
            $table->text('customer_reason')->nullable();
            $table->json('evidence_files')->nullable();
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->decimal('return_shipping_deduction', 12, 2)->default(0);
            $table->string('refund_method')->nullable(); // original, wallet
            $table->boolean('restock')->default(true);
            $table->text('inspection_notes')->nullable();
            $table->string('inspection_status')->nullable();
            $table->foreignId('replacement_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('return_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('original');
            $table->string('status')->default('pending')->index();
            $table->boolean('is_partial')->default(false);
            $table->text('reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('return_reasons');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('non_serviceable_locations');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
