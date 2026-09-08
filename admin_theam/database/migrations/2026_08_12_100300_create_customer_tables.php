<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->json('pricing_rules')->nullable();
            $table->integer('payment_terms_days')->default(0);
            $table->unsignedInteger('moq')->default(1);
            $table->json('shipping_rates')->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->date('dob')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('company_name')->nullable();
            $table->decimal('wallet_balance', 14, 2)->default(0);
            $table->integer('reward_points')->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->decimal('average_order_value', 14, 2)->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->unsignedInteger('cancelled_orders')->default(0);
            $table->string('acquisition_source')->nullable();
            $table->boolean('is_blocked')->default(false)->index();
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('shipping'); // billing, shipping
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('India');
            $table->string('pincode', 20);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // credit, debit
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reward_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // credit, debit
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['customer_id', 'product_id']);
        });

        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->json('cart_items');
            $table->decimal('cart_value', 14, 2)->default(0);
            $table->timestamp('cart_date');
            $table->timestamp('last_activity_at')->nullable();
            $table->string('recovery_status')->default('pending')->index();
            $table->boolean('coupon_sent')->default(false);
            $table->string('reminder_status')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel'); // email, sms, whatsapp, push, call
            $table->string('type')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('sent');
            $table->json('meta')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('abandoned_carts');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reward_point_transactions');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }
};
